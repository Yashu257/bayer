<?php

declare(strict_types=1);

namespace Core\Database;

/**
 * DB — thin static alias for Database.
 * Lets service/mail classes import Core\Database\DB without knowing the concrete name.
 */
final class DB
{
    public static function init(array $config): void
    {
        Database::init($config);
    }

    public static function conn(): \PDO
    {
        return Database::conn();
    }

    /** @return array<int,array<string,mixed>> */
    public static function query(string $sql, array $bindings = []): array
    {
        return Database::query($sql, $bindings);
    }

    /** @return array<string,mixed>|null */
    public static function queryOne(string $sql, array $bindings = []): ?array
    {
        return Database::queryOne($sql, $bindings);
    }

    /** Returns affected row count. */
    public static function execute(string $sql, array $bindings = []): int
    {
        return Database::execute($sql, $bindings);
    }

    /** Runs INSERT; returns the new auto-increment ID. */
    public static function insert(string $sql, array $bindings = []): int
    {
        return Database::insert($sql, $bindings);
    }
}
