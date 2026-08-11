<?php
namespace App\Services;
use App\Models\TwoFactorAuth;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\SvgWriter;
use OTPHP\TOTP;

class TwoFactorService {
    private TwoFactorAuth $twoFactorAuth;

    private const ISSUER = 'Anime Nigeria';
    private const TOTP_SECRET_BYTES = 20;

    /**
     * How long an unfinished 2FA setup remains valid.
     */
    private const SETUP_LIFETIME = 10 * 60; // 10 minutes

    /**
     * Number of recovery codes generated after successful setup.
     */
    private const RECOVERY_CODE_COUNT = 10;

    public function __construct(TwoFactorAuth $twoFactorAuth) {
        $this->twoFactorAuth = $twoFactorAuth;
    }

    /**
     * Start a new 2FA setup for a user.
     *
     * Returns the secret and provisioning URI needed by the frontend.
     */
    public function startSetup(int $userId, string $email): array {
        $secret = $this->generateSecret();
        $setupExpiresAt = date('Y-m-d H:i:s', time() + self::SETUP_LIFETIME);
        $created = $this->twoFactorAuth->create($userId, $secret, $setupExpiresAt);

        if (!$created) {
            throw new \RuntimeException('Unable to initialize two-factor authentication setup.');
        }

        $provisioningUri = $this->getProvisioningUri($secret, $email);

        return [
            'secret' => $secret,
            'provisioning_uri' => $provisioningUri,
            'qr_code' => $this->generateQrCode($provisioningUri),
            'expires_at' => $setupExpiresAt,
        ];
    }

    /**
     * Generate a new TOTP secret.
     */
    public function generateSecret(): string {
        $totp = TOTP::generate(null, self::TOTP_SECRET_BYTES);
        return $totp->getSecret();
    }

    /**
     * Build the TOTP provisioning URI.
     */
    public function getProvisioningUri(string $secret, string $email): string {
        $totp = TOTP::createFromSecret($secret);
        $totp->setLabel($email);
        $totp->setIssuer(self::ISSUER);
        return $totp->getProvisioningUri();
    }

    /**
     * Generate an SVG QR code.
     */
    public function generateQrCode(string $provisioningUri): string {
        $builder = new Builder(writer: new SvgWriter(), data: $provisioningUri, size: 300,
            margin: 10
        );

        $result = $builder->build();
        return $result->getString();
    }

    /**
     * Verify a TOTP code.
     */
    public function verifyCode(string $secret, string $code): bool {
        $totp = TOTP::createFromSecret($secret);
        return $totp->verify($code);
    }

    /**
     * Generate recovery codes.
     */
    public function generateRecoveryCodes(int $count = self::RECOVERY_CODE_COUNT): array {
        $codes = [];

        for ($i = 0; $i < $count; $i++) {
            $codes[] = strtoupper(bin2hex(random_bytes(5)));
        }

        return $codes;
    }

    /**
     * Hash recovery codes before storage.
     */
    public function hashRecoveryCodes(array $codes): string {
        $hashedCodes = [];

        foreach ($codes as $code) {
            $hashedCodes[] = password_hash($code, PASSWORD_DEFAULT);
        }

        return json_encode($hashedCodes, JSON_THROW_ON_ERROR);
    }

    /**
     * Complete 2FA setup after successful TOTP verification.
     *
     * Returns the plaintext recovery codes exactly once.
     */
    public function completeSetup(int $userId, string $code): array {
        $setup = $this->twoFactorAuth->findByUserId($userId);

        if ($setup === false) {
            throw new \RuntimeException('Two-factor authentication setup was not found.');
        }

        if ($setup['enabled_at'] !== null) {
            throw new \RuntimeException('Two-factor authentication is already enabled.');
        }

        if ($setup['setup_expires_at'] === null) {
            throw new \RuntimeException('Two-factor authentication setup is invalid.');
        }

        if (strtotime($setup['setup_expires_at']) <= time()) {
            $this->twoFactorAuth->delete($userId);
            throw new \RuntimeException('Two-factor authentication setup has expired.');
        }

        if (!$this->verifyCode($setup['secret'], $code)) {
            throw new \RuntimeException('Invalid authentication code.');
        }

        $recoveryCodes = $this->generateRecoveryCodes();
        $hashedRecoveryCodes = $this->hashRecoveryCodes($recoveryCodes);

        if (!$this->twoFactorAuth->updateRecoveryCodes($userId, $hashedRecoveryCodes)) {
            throw new \RuntimeException('Unable to save recovery codes.');
        }

        if (!$this->twoFactorAuth->enable($userId)) {
            throw new \RuntimeException('Unable to enable two-factor authentication.');
        }

        return $recoveryCodes;
    }

    /**
     * Verify a recovery code and consume it.
     */
    public function verifyRecoveryCode(string $code, string $storedCodes): array|false {
        $hashedCodes = json_decode($storedCodes, true, 512, JSON_THROW_ON_ERROR);

        foreach ($hashedCodes as $index => $hash) {
            if (!password_verify($code, $hash)) {
                continue;
            }

            unset($hashedCodes[$index]);
            return array_values($hashedCodes);
        }

        return false;
    }
}