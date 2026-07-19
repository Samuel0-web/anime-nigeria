<?php
namespace App\Mail;
use App\Mail\Templates\VerifyEmail;

class Mail {
    public function __construct(private readonly Mailer $mailer, private readonly string $appUrl,) {}

    public function sendVerificationEmail(string $email, string $name, string $token ): bool {
        $url = rtrim($this->appUrl, '/') . '/auth/verify?token=' . urlencode($token);

        return $this->mailer->send($email, 'Confirm your email to activate your account',
            VerifyEmail::render($name, $url)
        );
    }
}