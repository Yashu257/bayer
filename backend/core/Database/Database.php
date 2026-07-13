<?php

declare(strict_types=1);

namespace Core\Database;

use PDO;
use PDOException;
use Core\Exceptions\DatabaseException;

/**
 * Database — PDO singleton wrapper.
 * Call Database::init() once in bootstrap, then Database::conn() everywhere.
 */
final class Database
{
    private static ?PDO $connection = null;

    private function __construct() {}

    public static function init(array $config): void
    {
        if (self::$connection !== null) {
            return;
        }

        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            $config['host'],
            $config['port'],
            $config['dbname'],
            $config['charset']
        );

        try {
            self::$connection = new PDO(
                $dsn,
                $config['user'],
                $config['pass'],
                $config['options']
            );
        } catch (PDOException $e) {
            throw new DatabaseException('Database connection failed: ' . $e->getMessage(), (int) $e->getCode(), $e);
        }
    }

    public static function conn(): PDO
    {
        if (self::$connection === null) {
            throw new DatabaseException('Database not initialised. Call Database::init() first.');
        }
        return self::$connection;
    }

    /** Run a SELECT and return all rows. */
    public static function query(string $sql, array $bindings = []): array
    {
        $stmt = self::conn()->prepare($sql);
        $stmt->execute($bindings);
        return $stmt->fetchAll();
    }

    /** Run a SELECT and return a single row or null. */
    public static function queryOne(string $sql, array $bindings = []): ?array
    {
        $stmt = self::conn()->prepare($sql);
        $stmt->execute($bindings);
        $row = $stmt->fetch();
        return $row !== false ? $row : null;
    }

    /** Run an INSERT / UPDATE / DELETE and return affected row count. */
    public static function execute(string $sql, array $bindings = []): int
    {
        $stmt = self::conn()->prepare($sql);
        $stmt->execute($bindings);
        return $stmt->rowCount();
    }

    /** Run INSERT and return the new auto-increment ID. */
    public static function insert(string $sql, array $bindings = []): int
    {
        $stmt = self::conn()->prepare($sql);
        $stmt->execute($bindings);
        return (int) self::conn()->lastInsertId();
    }

    public static function beginTransaction(): void
    {
        self::conn()->beginTransaction();
    }

    public static function commit(): void
    {
        self::conn()->commit();
    }

    public static function rollback(): void
    {
        self::conn()->rollBack();
    }
}
