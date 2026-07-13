<?php

declare(strict_types=1);

namespace App\Models;

use Core\Database\Database;

/**
 * BaseModel — thin Active-Record-style base.
 * Subclasses define $table and $fillable; never write SQL in views or controllers.
 */
abstract class BaseModel
{
    protected static string $table      = '';
    protected static string $primaryKey = 'id';
    protected array         $attributes = [];

    public function __construct(array $attributes = [])
    {
        $this->attributes = $attributes;
    }

    public function __get(string $key): mixed
    {
        return $this->attributes[$key] ?? null;
    }

    public function __set(string $key, mixed $value): void
    {
        $this->attributes[$key] = $value;
    }

    public function __isset(string $key): bool
    {
        return isset($this->attributes[$key]);
    }

    public function toArray(): array
    {
        return $this->attributes;
    }

    // --- Finders (return model instances) ------------------------------------

    public static function find(int|string $id): ?static
    {
        $table = static::$table;
        $pk    = static::$primaryKey;
        $row   = Database::queryOne("SELECT * FROM `$table` WHERE `$pk` = ? LIMIT 1", [$id]);
        return $row ? new static($row) : null;
    }

    public static function findOrFail(int|string $id): static
    {
        $model = static::find($id);
        if ($model === null) {
            throw new \Core\Exceptions\HttpException(404, static::class . ' not found.');
        }
        return $model;
    }

    public static function where(string $column, mixed $value): ?static
    {
        $table = static::$table;
        $row   = Database::queryOne(
            "SELECT * FROM `$table` WHERE `$column` = ? LIMIT 1",
            [$value]
        );
        return $row ? new static($row) : null;
    }

    public static function all(): array
    {
        $table = static::$table;
        $rows  = Database::query("SELECT * FROM `$table`");
        return array_map(fn($r) => new static($r), $rows);
    }

    // --- Persistence ---------------------------------------------------------

    public function save(): bool
    {
        if (isset($this->attributes[static::$primaryKey])) {
            return $this->update();
        }
        return $this->insert();
    }

    private function insert(): bool
    {
        $table    = static::$table;
        $data     = $this->attributes;
        $cols     = implode('`, `', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));

        $id = Database::insert(
            "INSERT INTO `$table` (`$cols`) VALUES ($placeholders)",
            array_values($data)
        );

        $this->attributes[static::$primaryKey] = $id;
        return $id > 0;
    }

    private function update(): bool
    {
        $table = static::$table;
        $pk    = static::$primaryKey;
        $data  = $this->attributes;
        $id    = $data[$pk];
        unset($data[$pk]);

        $set = implode(', ', array_map(fn($k) => "`$k` = ?", array_keys($data)));

        $affected = Database::execute(
            "UPDATE `$table` SET $set WHERE `$pk` = ?",
            [...array_values($data), $id]
        );
        return $affected > 0;
    }

    public function delete(): bool
    {
        $table = static::$table;
        $pk    = static::$primaryKey;
        return Database::execute(
            "DELETE FROM `$table` WHERE `$pk` = ?",
            [$this->attributes[$pk]]
        ) > 0;
    }

    /** Soft-delete helper — only works if the table has a deleted_at column. */
    public function softDelete(): bool
    {
        $this->attributes['deleted_at'] = date('Y-m-d H:i:s');
        return $this->save();
    }
}
