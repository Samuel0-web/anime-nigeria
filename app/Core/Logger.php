<?php
namespace App\Core;

class Logger{
    public static function error(\Throwable $e): void {
        $logDir = __DIR__ . '/../../storage/logs';

        if (!is_dir($logDir)) {
            mkdir($logDir, 0775, true);
        }

        $file = $logDir . '/app-' . date('Y-m-d') . '.log';

        $message = sprintf(
            "[%s] %s\nFile: %s:%d\n%s\n\n",
            date('Y-m-d H:i:s'),
            $e->getMessage(),
            $e->getFile(),
            $e->getLine(),
            $e->getTraceAsString()
        );

        file_put_contents($file, $message, FILE_APPEND | LOCK_EX);
    }
}