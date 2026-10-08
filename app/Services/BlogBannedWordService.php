<?php
namespace App\Services;

use App\Core\Logger;
use App\Database\Database;
use App\Models\BlogBannedWord;
use App\Support\FileCache;

/** Banned-word list (admin CRUD) and the server-side redaction used by comments. */
final class BlogBannedWordService {
    public const CACHE_NS = 'blog-banned';
    public const REDACTION = '[redacted]';
    public const MAX_WORDS = 500;

    private const TTL = 3600;
    private const MAX_BULK = 200;
    private const WORD = '[\p{L}\p{N}\p{M}_]';

    public function __construct(private BlogBannedWord $words, private FileCache $cache) {}

    public static function make(): self {
        return new self(new BlogBannedWord(Database::connection()), new FileCache(STORAGE_PATH . '/cache'));
    }

    // =========================================================================
    // REDACTION
    // =========================================================================
    /**
     * Case-insensitive, whole-word (Unicode aware) redaction. Phrases match across
     * any run of whitespace. Returns null if the pattern fails, so the caller can
     * refuse the text instead of publishing it unfiltered.
     */
    public function redact(string $text): ?string {
        $words = $this->activeWords();

        if ($words === [] || $text === '') {
            return $text;
        }

        $result = preg_replace(self::pattern($words), self::REDACTION, mb_scrub($text));

        return is_string($result) ? $result : null;
    }

    /** @return string[] */
    private function activeWords(): array {
        return $this->cache->remember(self::CACHE_NS, 'words', self::TTL,
            fn (): array => $this->words->allWords());
    }

    private static function pattern(array $words): string {
        usort($words, static fn (string $a, string $b): int => mb_strlen($b) <=> mb_strlen($a));
        $parts = [];

        foreach ($words as $word) {
            $tokens = preg_split('/\s+/u', trim($word), -1, PREG_SPLIT_NO_EMPTY);

            if (!$tokens) {
                continue;
            }

            $body = implode('\s+', array_map(static fn (string $t): string => preg_quote($t, '/'), $tokens));
            // Boundaries only where the phrase starts/ends with a word character ("c++" has no trailing one).
            $start = preg_match('/^' . self::WORD . '$/u', mb_substr($tokens[0], 0, 1))
                ? '(?<!' . self::WORD . ')' : '';
            $end = preg_match('/^' . self::WORD . '$/u', mb_substr($tokens[count($tokens) - 1], -1))
                ? '(?!' . self::WORD . ')' : '';
            $parts[] = $start . $body . $end;
        }

        return '/(?:' . implode('|', $parts) . ')/iu';
    }

    // =========================================================================
    // ADMIN CRUD
    // =========================================================================
    public function list(): array {
        return array_map([$this, 'present'], $this->words->all());
    }

    public function create(array $in): array {
        [$word, $errors] = $this->validate($in, null);

        if ($errors) {
            return $this->invalid($errors);
        }

        if ($this->words->count() >= self::MAX_WORDS) {
            return $this->fail('The list is full (' . self::MAX_WORDS . ' entries). Remove one first.');
        }

        try {
            $id = $this->words->create($word);
        } catch (\PDOException $e) {
            if ($this->isDuplicate($e)) {
                return $this->invalid(['word' => 'That word or phrase is already on the list.']);
            }

            throw $e;
        }

        $this->invalidate();

        return ['success' => true, 'message' => 'Added to the list.',
            'item' => $this->present($this->words->findById($id))];
    }

    public function update(int $id, array $in): array {
        if ($this->words->findById($id) === false) {
            return $this->fail('That entry no longer exists.', 404);
        }

        [$word, $errors] = $this->validate($in, $id);

        if ($errors) {
            return $this->invalid($errors);
        }

        try {
            $this->words->update($id, $word);
        } catch (\PDOException $e) {
            if ($this->isDuplicate($e)) {
                return $this->invalid(['word' => 'That word or phrase is already on the list.']);
            }

            throw $e;
        }

        $this->invalidate();

        return ['success' => true, 'message' => 'Entry updated.',
            'item' => $this->present($this->words->findById($id))];
    }

    public function deleteMany(mixed $ids): array {
        $ids = $this->ids($ids);

        if (!$ids) {
            return $this->fail('Nothing was selected.');
        }

        $deleted = $this->words->deleteMany($ids);
        $this->invalidate();

        return ['success' => true, 'deleted' => $deleted, 'message' => 'Removed from the list.'];
    }

    // =========================================================================
    private function validate(array $in, ?int $ignoreId): array {
        $word = trim((string) preg_replace('/\s+/u', ' ', (string) ($in['word'] ?? '')));
        $errors = [];

        if ($word === '') {
            $errors['word'] = 'Enter a word or phrase.';
        } elseif (mb_strlen($word) < 2 || mb_strlen($word) > 60) {
            $errors['word'] = 'Use between 2 and 60 characters.';
        } elseif (!preg_match('/[\p{L}\p{N}]/u', $word) || preg_match('/[\x00-\x1F\x7F]/', $word)) {
            $errors['word'] = 'Use letters or numbers, without control characters.';
        } else {
            $found = $this->words->findByWord($word);

            if ($found !== false && (int) $found['id'] !== $ignoreId) {
                $errors['word'] = 'That word or phrase is already on the list.';
            }
        }

        return [$word, $errors];
    }

    private function present(array $r): array {
        return ['id' => (int) $r['id'], 'word' => $r['word'],
            'created_at' => (new \DateTimeImmutable($r['created_at'], new \DateTimeZone('UTC')))->format('Y-m-d\TH:i:s\Z')];
    }

    private function invalidate(): void {
        try {
            $this->cache->invalidate(self::CACHE_NS);
        } catch (\Throwable $e) {
            Logger::error($e);
        }
    }

    /** @return int[] */
    private function ids(mixed $ids): array {
        if (!is_array($ids)) {
            return [];
        }

        $clean = [];

        foreach ($ids as $id) {
            if ((is_int($id) || (is_string($id) && ctype_digit($id))) && (int) $id > 0) {
                $clean[(int) $id] = (int) $id;
            }
        }

        return array_slice(array_values($clean), 0, self::MAX_BULK);
    }

    private function isDuplicate(\PDOException $e): bool {
        return ($e->errorInfo[0] ?? '') === '23000' && (int) ($e->errorInfo[1] ?? 0) === 1062;
    }

    private function invalid(array $errors): array {
        return ['success' => false, 'status' => 422, 'errors' => $errors];
    }

    private function fail(string $message, int $status = 422): array {
        return ['success' => false, 'status' => $status, 'message' => $message];
    }
}