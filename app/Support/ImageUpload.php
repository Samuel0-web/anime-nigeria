<?php
namespace App\Support;

/**
 * Stores validated images under storage/uploads/{directory}/ and exposes them
 * at /storage/uploads/{directory}/..., the same scheme avatars use.
 */
final class ImageUpload {
    private const TYPES = [
        'image/png'  => 'png',
        'image/jpeg' => 'jpg',
        'image/webp' => 'webp',
    ];

    public function __construct(private string $directory, private int $maxBytes) {}

    public function hasUpload(array $files, string $key): bool {
        return isset($files[$key])
            && ($files[$key]['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;
    }

    /** Returns a user-facing error, or null when the file is acceptable. Moves nothing. */
    public function inspect(array $file): ?string {
        $error = $file['error'] ?? UPLOAD_ERR_NO_FILE;

        if ($error === UPLOAD_ERR_INI_SIZE || $error === UPLOAD_ERR_FORM_SIZE) {
            return $this->tooLarge();
        }

        if ($error !== UPLOAD_ERR_OK) {
            return 'Image upload failed. Please try again.';
        }

        if (($file['size'] ?? 0) > $this->maxBytes) {
            return $this->tooLarge();
        }

        $info = @getimagesize($file['tmp_name']);

        if ($info === false) {
            return 'That file is not a valid image.';
        }

        if (!isset(self::TYPES[$info['mime']])) {
            return 'Only PNG, JPG and WebP images are allowed.';
        }

        return null;
    }

    /** @return array{publicPath: string, absolutePath: string}|null */
    public function store(array $file): ?array {
        $info = @getimagesize($file['tmp_name']);

        if ($info === false || !isset(self::TYPES[$info['mime']])) {
            return null;
        }

        $directory = STORAGE_PATH . '/uploads/' . $this->directory;

        if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
            return null;
        }

        $filename = bin2hex(random_bytes(16)) . '.' . self::TYPES[$info['mime']];
        $absolute = $directory . '/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $absolute)) {
            return null;
        }

        return [
            'publicPath' => '/storage/uploads/' . $this->directory . '/' . $filename,
            'absolutePath' => $absolute,
        ];
    }

    /** True only for files this class created. Seed artwork in /uploads is never "owned". */
    public function owns(?string $publicPath): bool {
        if ($publicPath === null || $publicPath === '') {
            return false;
        }

        $pattern = '#^/storage/uploads/' . preg_quote($this->directory, '#')
            . '/[a-f0-9]{32}\.(png|jpg|webp)$#';

        return preg_match($pattern, $publicPath) === 1;
    }

    public function delete(?string $publicPath): void {
        if (!$this->owns($publicPath)) {
            return;
        }

        $absolute = STORAGE_PATH . '/uploads/' . $this->directory . '/' . basename($publicPath);

        if (is_file($absolute)) {
            @unlink($absolute);
        }
    }

    private function tooLarge(): string {
        return 'Image must not exceed ' . (int) round($this->maxBytes / 1048576) . 'MB.';
    }
}