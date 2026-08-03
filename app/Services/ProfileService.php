<?php
namespace App\Services;
use App\Models\User;
use App\Support\Avatar;

class ProfileService {
    private const ALLOWED_IMAGE_TYPES = [
        'image/png',
        'image/jpeg',
    ];

    private const MAX_AVATAR_SIZE = 2 * 1024 * 1024;
    public function __construct(private User $users) { }

    public function update(array $currentUser, array $data, array $files): array {
        $errors = [];
        $profile = $this->validateProfile($currentUser, $data, $errors);
        $passwordHash = $this->validatePassword($currentUser, $data, $errors);
        $avatar = $this->processAvatar($files, $errors);

        if (!empty($errors)) {
            if ($avatar !== null) {
                if (is_file($avatar['absolutePath'])) {
                    unlink($avatar['absolutePath']);
                }
            }

            return [
                'success' => false,
                'errors' => $errors,
            ];
        }

        $removeAvatar = ($data['removeAvatar'] ?? '0') === '1';

        $updated = $this->users->updateProfile($currentUser['id'], $profile['fullname'],
            $profile['username'], $passwordHash, $avatar['publicPath'] ?? null, $removeAvatar
        );

        if (!$updated) {

            // Rollback uploaded file
            if ($avatar !== null) {
                if (is_file($avatar['absolutePath'])) {
                    unlink($avatar['absolutePath']);
                }
            }

            return [
                'success' => false,
                'message' => 'Profile update failed.',
            ];
        }

        // Database succeeded.
        // Safe to delete the previous avatar now.
        if ($avatar !== null) {
            $this->deleteAvatar($currentUser['avatar'] ?? null);
        }

        if ($removeAvatar) {
            $this->deleteAvatar($currentUser['avatar'] ?? null);
        }

        $_SESSION['user'] = $this->users->findById($_SESSION['user_id']);
        $updatedUser = $_SESSION['user'] ?? $currentUser;

        return [
            'success' => true,
            'message' => 'Profile updated successfully.',
            'user' => [
                'fullname' => $updatedUser['fullname'] ?? $profile['fullname'],
                'username' => $updatedUser['username'] ?? $profile['username'],
                'avatar' => $updatedUser['avatar'] ?? null,
                'avatarColor' => Avatar::color(($updatedUser['username'] ?? '') !== ''
                    ? $updatedUser['username'] : $updatedUser['fullname']
                ),
            ],
        ];
    }

    private function validateProfile(array $currentUser, array $data, array &$errors): array {
        $fullname = trim(preg_replace('/\s+/', ' ', $data['fullname'] ?? ''));

        if ($fullname === '') {
            $errors['fullname'] = 'Full name is required.';
        } elseif (mb_strlen($fullname) > 100) {
            $errors['fullname'] = 'Full name is too long.';
        }

        $username = trim($data['username'] ?? '');

        if ($username === '') {
            $errors['username'] = 'Username is required.';
        } elseif (!preg_match('/^[A-Za-z0-9_]{3,20}$/', $username)) {
            $errors['username'] =
                'Username must be 3-20 characters and only contain letters, numbers and underscores.';
        } else {
            $existing = $this->users->findByUsername($username);

            if ($existing && (int)$existing['id'] !== (int)$currentUser['id']) {
                $errors['username'] = 'That username is already taken.';
            }
        }

        return [
            'fullname' => $fullname,
            'username' => $username,
        ];
    }

    private function validatePassword(array $currentUser, array $data, array &$errors): ?string {
        $newPassword = $data['newPassword'] ?? '';

        if ($newPassword === '') {
            return null;
        }

        $currentPassword = $data['currentPassword'] ?? '';

        if (!$this->users->verifyPassword($currentUser, $currentPassword)) {
            $errors['currentPassword'] = 'Current password is incorrect.';
        }

        if (strlen($newPassword) < 8) {
            $errors['newPassword'] = 'Password must be at least 8 characters.';
        } elseif (!preg_match('/[A-Z]/', $newPassword)) {
            $errors['newPassword'] = 'Password must contain an uppercase letter.';
        } elseif (!preg_match('/[0-9]/', $newPassword)) {
            $errors['newPassword'] = 'Password must contain a number.';
        } elseif (!preg_match('/[!@#$%&*?,]/', $newPassword)) {
            $errors['newPassword'] = 'Password must contain a symbol.';
        }

        if (($data['confirmPassword'] ?? '') !== $newPassword) {
            $errors['confirmPassword'] = 'Passwords do not match.';
        }

        if (isset($errors['newPassword']) || isset($errors['currentPassword']) ||
            isset($errors['confirmPassword'])
        ) {
            return null;
        }

        return password_hash($newPassword, PASSWORD_DEFAULT);
    }

    private function processAvatar(array $files, array &$errors): ?array {
        if (!isset($files['avatar'])) {
            return null;
        }

        $file = $files['avatar'];

        if ($file['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors['avatar'] = 'Avatar upload failed.';
            return null;
        }

        $file = $files['avatar'];
        $imageInfo = getimagesize($file['tmp_name']);

        if ($imageInfo === false) {
            $errors['avatar'] = 'Invalid image.';
            return null;
        }

        $mime = $imageInfo['mime'];

        if (!in_array($mime, self::ALLOWED_IMAGE_TYPES, true)) {
            $errors['avatar'] = 'Only PNG and JPG images are allowed.';
            return null;
        }

        if ($file['size'] > self::MAX_AVATAR_SIZE) {
            $errors['avatar'] = 'Avatar must not exceed 2MB.';
            return null;
        }

        $directory = STORAGE_PATH . '/uploads/avatars';

        if (!is_dir($directory) && !mkdir($directory, 0755, true)) {
            $errors['avatar'] = 'Unable to create upload directory.';
            return null;
        }

        $extension = $mime === 'image/png' ? 'png' : 'jpg';
        $filename = bin2hex(random_bytes(16)) . '.' . $extension;
        $absolute = $directory . '/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $absolute)) {
            $errors['avatar'] = 'Unable to save avatar.';
            return null;
        }

        return [
            'publicPath' => '/storage/uploads/avatars/' . $filename,
            'absolutePath' => $absolute,
        ];
    }

    private function deleteAvatar(?string $avatar): void {
        if (!$avatar || !str_starts_with($avatar, '/storage/uploads/avatars/')) {
            return;
        }

        $file = PUBLIC_PATH . $avatar;

        if (is_file($file)) {
            @unlink($file);
        }
    }
}