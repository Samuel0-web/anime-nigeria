<?php
namespace App\Mail;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use App\Core\Logger;
use RuntimeException;

class SmtpMailer implements Mailer {
    private PHPMailer $mail;

    public function __construct() {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = $_ENV['MAIL_HOST'];
        $mail->Port = (int) $_ENV['MAIL_PORT'];
        
        if (empty($_ENV['MAIL_HOST']) || empty($_ENV['MAIL_PORT']) ||
            empty($_ENV['MAIL_FROM_ADDRESS'])) {
            throw new \RuntimeException('Mail configuration is incomplete.');
        }

        if (!empty($_ENV['MAIL_USERNAME'])) {
            $mail->SMTPAuth = true;
            $mail->Username = $_ENV['MAIL_USERNAME'];
            $mail->Password = $_ENV['MAIL_PASSWORD'];
        }

        $mail->SMTPSecure = match ($_ENV['MAIL_ENCRYPTION']) {
            'tls' => PHPMailer::ENCRYPTION_STARTTLS,
            'ssl' => PHPMailer::ENCRYPTION_SMTPS,
            default => false,
        };

        $mail->CharSet = 'UTF-8';
        $mail->Encoding = 'base64';

        $mail->setFrom(
            $_ENV['MAIL_FROM_ADDRESS'],
            $_ENV['MAIL_FROM_NAME']
        );

        $mail->isHTML(true);

        if (($_ENV['APP_DEBUG'] ?? 'false') === 'true') {
            $mail->SMTPDebug = 0;
            $mail->Debugoutput = 'html';
        }

        $this->mail = $mail;
    }

    public function send(string $to, string $subject, string $html, ?string $text = null): bool {
        try {
            $this->mail->clearAddresses();
            $this->mail->clearAttachments();
            $this->mail->addAddress($to);
            $this->mail->Subject = $subject;
            $this->mail->Body = $html;
            $this->mail->AltBody = $text ?? strip_tags($html);
            return $this->mail->send();
        } catch (Exception $e) {
            Logger::error($e);

            throw new RuntimeException('Unable to send verification email. Please try again.',
                0,
                $e
            );
        }
    }
}