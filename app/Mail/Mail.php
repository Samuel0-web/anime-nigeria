<?php
namespace App\Mail;
use App\Mail\Templates\VerifyEmail;
use App\Mail\Templates\PasswordReset;

class Mail {
    public function __construct(private readonly Mailer $mailer, private readonly string $appUrl,) {}

    public function sendVerificationEmail(string $email, string $name, string $token ): bool {
        $url = rtrim($this->appUrl, '/') . '/auth/verify?token=' . urlencode($token);

        return $this->mailer->send($email, 'Confirm your email to activate your account',
            VerifyEmail::render($name, $url)
        );
    }

    public function sendPasswordResetEmail(string $email, string $name, string $token): bool {
        $url = rtrim($this->appUrl, '/') . '/auth/reset-password?token=' . urlencode($token);

        return $this->mailer->send(
            $email,
            'Reset your Anime Nigeria password',
            PasswordReset::render($name, $url)
        );
    }
}