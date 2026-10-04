<?php
namespace App\Support;

use App\Core\Logger;

/**
 * Small file-backed cache over storage/cache.
 *
 * The project has a storage/cache folder but no cache class, so this is the
 * thin layer that uses it: JSON entries (never unserialize), atomic writes,
 * a TTL, and namespace-wide invalidation.
 *
 * Invalidation uses a per-namespace version token that is part of every file
 * name. invalidate() swaps the token, which makes every existing entry
 * unreachable at once without scanning for keys. remember() reads the token
 * BEFORE it runs the compute callback, so a request that began before an
 * invalidation can only write under the old token: it cannot re-seed the
 * cache with data that predates the change.
 *
 * Every filesystem problem degrades to "no cache": the callback runs and its
 * result is returned. The database stays the source of truth.
 */
final class FileCache {
    private const VERSION_FILE = '_version';
    private const GC_MAX_AGE = 86400;
    private const GC_ODDS = 50; // 1 in N writes sweeps long-expired files

    /** @var array<string, ?string> */
    private array $versions = [];
    private bool $reported = false;

    /** @var list<string> "key=hit|miss|bypass|off" for the current request. */
    private array $trace = [];

    public function __construct(private string $baseDir) {}

    public function remember(string $namespace, string $key, int $ttl, callable $compute): mixed {
        $version = $this->version($namespace);

        if ($version === null) {
            $this->note($key, 'off'); // cache unavailable: straight to the database
            return $compute();
        }

        $path = $this->entryPath($namespace, $version, $key);
        $hit = $this->read($path, $key);

        if ($hit !== null) {
            $this->note($key, 'hit');
            return $hit['value'];
        }

        $this->note($key, 'miss');
        $value = $compute();
        $this->write($namespace, $path, $key, $value, $ttl);

        return $value;
    }

    /** Diagnostics only. */
    public function note(string $key, string $result): void {
        $this->trace[] = preg_replace('/[^A-Za-z0-9:_.-]/', '', $key) . '=' . $result;
    }

    /** @return list<string> */
    public function trace(): array {
        return $this->trace;
    }

    /** Make every entry in the namespace unreachable and remove the files. */
    public function invalidate(string $namespace): void {
        unset($this->versions[$namespace]);
        $dir = $this->directory($namespace);

        if ($dir === null) {
            return;
        }

        $token = bin2hex(random_bytes(8));

        if ($this->atomicWrite($dir . '/' . self::VERSION_FILE, $token)) {
            $this->versions[$namespace] = $token;
        } else {
            $this->report('Unable to write the cache version file.');
        }

        // Entries under the previous token are unreachable now; remove them.
        foreach (glob($dir . '/*.json') ?: [] as $file) {
            @unlink($file);
        }
    }

    // -------------------------------------------------------------------------
    private function version(string $namespace): ?string {
        if (array_key_exists($namespace, $this->versions)) {
            return $this->versions[$namespace];
        }

        $token = null;

        try {
            $dir = $this->directory($namespace);

            if ($dir !== null) {
                $stored = @file_get_contents($dir . '/' . self::VERSION_FILE);

                if (is_string($stored) && preg_match('/^[a-f0-9]{16}$/', trim($stored))) {
                    $token = trim($stored);
                } else {
                    $fresh = bin2hex(random_bytes(8));
                    $token = $this->atomicWrite($dir . '/' . self::VERSION_FILE, $fresh) ? $fresh : null;
                }
            }
        } catch (\Throwable $e) {
            $this->report($e->getMessage());
        }

        return $this->versions[$namespace] = $token;
    }

    private function directory(string $namespace): ?string {
        if (!preg_match('/^[a-z0-9_-]+$/', $namespace)) {
            return null;
        }

        $dir = rtrim($this->baseDir, '/\\') . '/' . $namespace;

        if (!is_dir($dir) && !@mkdir($dir, 0775, true) && !is_dir($dir)) {
            $this->report('Cache directory is not writable: ' . $dir);
            return null;
        }

        return $dir;
    }

    private function entryPath(string $namespace, string $version, string $key): string {
        return rtrim($this->baseDir, '/\\') . '/' . $namespace . '/' . $version . '-' . sha1($key) . '.json';
    }

    /** @return array{key: string, expires: int, value: mixed}|null */
    private function read(string $path, string $key): ?array {
        $raw = @file_get_contents($path);

        if ($raw === false) {
            return null;
        }

        $entry = json_decode($raw, true);

        if (!is_array($entry)
            || ($entry['key'] ?? null) !== $key
            || (int) ($entry['expires'] ?? 0) < time()
            || !array_key_exists('value', $entry)
        ) {
            return null; // expired or unreadable: it will be overwritten
        }

        return $entry;
    }

    private function write(string $namespace, string $path, string $key, mixed $value, int $ttl): void {
        try {
            $json = json_encode(
                ['key' => $key, 'expires' => time() + $ttl, 'value' => $value],
                JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR
            );

            if (!$this->atomicWrite($path, $json)) {
                $this->report('Unable to write a cache entry.');
                return;
            }

            if (random_int(1, self::GC_ODDS) === 1 && ($dir = $this->directory($namespace)) !== null) {
                $this->collectGarbage($dir);
            }
        } catch (\Throwable $e) {
            $this->report($e->getMessage());
        }
    }

    private function atomicWrite(string $path, string $contents): bool {
        $tmp = $path . '.' . bin2hex(random_bytes(4)) . '.tmp';

        if (@file_put_contents($tmp, $contents, LOCK_EX) === false) {
            return false;
        }

        if (!@rename($tmp, $path)) {
            @unlink($tmp);
            return false;
        }

        return true;
    }

    private function collectGarbage(string $dir): void {
        $cutoff = time() - self::GC_MAX_AGE;
        $files = array_merge(glob($dir . '/*.json') ?: [], glob($dir . '/*.tmp') ?: []);

        foreach ($files as $file) {
            if ((int) @filemtime($file) < $cutoff) {
                @unlink($file);
            }
        }
    }

    /** Logged once per request so a broken cache folder cannot flood the log. */
    private function report(string $message): void {
        if ($this->reported) {
            return;
        }

        $this->reported = true;
        Logger::error(new \RuntimeException('FileCache: ' . $message));
    }
}