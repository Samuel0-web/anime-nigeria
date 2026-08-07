<?php
namespace App\Core;

class Logger {
    // =========================================================================
    // CONSTANTS
    // =========================================================================
    private const LOG_DIR = '/../../storage/logs';
    private const FILE_PREFIX = 'app-';
    private const DATE_FORMAT = 'Y-m-d';
    private const TIME_FORMAT = 'Y-m-d H:i:s';
    private const DIR_PERMISSIONS = 0775;

    // =========================================================================
    // PUBLIC API
    // =========================================================================
    
    /**
     * Log an error/exception to the daily log file.
     */
    public static function error(\Throwable $e): void {
        self::ensureLogDirectoryExists();
        
        $message = self::formatError($e);
        $file = self::getLogFilePath();
        
        file_put_contents($file, $message, FILE_APPEND | LOCK_EX);
    }

    // =========================================================================
    // PRIVATE - File Management
    // =========================================================================
    
    /**
     * Ensure the log directory exists.
     */
    private static function ensureLogDirectoryExists(): void {
        $logDir = __DIR__ . self::LOG_DIR;
        
        if (!is_dir($logDir)) {
            mkdir($logDir, self::DIR_PERMISSIONS, true);
        }
    }

    /**
     * Get the full path for today's log file.
     */
    private static function getLogFilePath(): string {
        $logDir = __DIR__ . self::LOG_DIR;
        $filename = self::FILE_PREFIX . date(self::DATE_FORMAT) . '.log';
        
        return $logDir . '/' . $filename;
    }

    // =========================================================================
    // PRIVATE - Formatting
    // =========================================================================
    
    /**
     * Format an exception into a log message.
     */
    private static function formatError(\Throwable $e): string {
        return sprintf("[%s] %s\nFile: %s:%d\nStack trace:\n%s\n\n", date(self::TIME_FORMAT),
            $e->getMessage(), $e->getFile(), $e->getLine(), $e->getTraceAsString()
        );
    }
}