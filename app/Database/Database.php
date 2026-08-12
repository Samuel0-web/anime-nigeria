<?php
namespace App\Database;

use PDO;
use PDOException;

class Database {
    // =========================================================================
    // CONSTANTS
    // =========================================================================
    private const CHARSET = 'utf8mb4';
    
    // =========================================================================
    // PROPERTIES
    // =========================================================================
    private static ?PDO $connection = null;

    // =========================================================================
    // PUBLIC API
    // =========================================================================
    
    /**
     * Get the database connection (singleton pattern).
     */
    public static function connection(): PDO {
        if (self::$connection !== null) {
            return self::$connection;
        }

        self::$connection = self::createConnection();
        return self::$connection;
    }

    /**
     * Close the database connection (useful for testing or cleanup).
     */
    public static function disconnect(): void {
        self::$connection = null;
    }

    // =========================================================================
    // PRIVATE - Connection Management
    // =========================================================================
    
    /**
     * Create a new PDO connection with default options.
     */
    // Add connection pooling or retry logic
    private static function createConnection(): PDO {
        $dsn = self::buildDsn();
        $maxRetries = 3;
        $attempt = 0;

        while ($attempt < $maxRetries) {
            try {
                $pdo = new PDO($dsn, $_ENV['DB_USERNAME'], $_ENV['DB_PASSWORD'],
                    self::getDefaultOptions()
                );

                $pdo->exec("SET time_zone = '+00:00'");
                return $pdo;
            } catch (PDOException $e) {
                $attempt++;

                if ($attempt === $maxRetries) {
                    throw new PDOException(
                        "Database connection failed after {$maxRetries} attempts: " .
                        $e->getMessage(), (int) $e->getCode(), $e
                    );
                }

                // Wait before retrying (exponential backoff)
                usleep(100000 * $attempt); // 100ms, 200ms, 300ms
            }
        }

        throw new PDOException('Database connection failed after retries were exhausted.');
    }

    // Add transaction helper
    public static function transaction(callable $callback): mixed {
        $pdo = self::connection();
        
        if ($pdo->inTransaction()) {
            return $callback($pdo);
        }
        
        $pdo->beginTransaction();

        try {
            $result = $callback($pdo);
            $pdo->commit();
            return $result;
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Build the MySQL DSN string.
     */
    private static function buildDsn(): string {
        return sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            $_ENV['DB_HOST'],
            $_ENV['DB_PORT'],
            $_ENV['DB_DATABASE'],
            self::CHARSET
        );
    }

    /**
     * Get the default PDO options for consistency and security.
     */
    private static function getDefaultOptions(): array {
        return [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
    }
}