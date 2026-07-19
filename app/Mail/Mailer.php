<?php
namespace App\Mail;

interface Mailer {
    public function send(string $to, string $subject, string $html, ?string $text = null): bool;
}