<?php

namespace App\Models;

use Core\Database;
use PDO;

/**
 * Basic ORM-like model.
 */
abstract class BaseModel
{
    protected static string $table;

    protected static function db(): PDO
    {
        return Database::getConnection();
    }

    public static function find(int $id): ?array
    {
        $stmt = self::db()->prepare('SELECT * FROM ' . static::$table . ' WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public static function findBy(array $conditions): array
    {
        $clauses = [];
        $params = [];
        foreach ($conditions as $key => $value) {
            $clauses[] = "{$key} = :{$key}";
            $params[$key] = $value;
        }
        $sql = 'SELECT * FROM ' . static::$table . ' WHERE ' . implode(' AND ', $clauses);
        $stmt = self::db()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function create(array $data): int
    {
        $columns = array_keys($data);
        $placeholders = array_map(fn($c) => ":{$c}", $columns);
        $sql = 'INSERT INTO ' . static::$table . ' (' . implode(',', $columns) . ') VALUES (' . implode(',', $placeholders) . ')';
        $stmt = self::db()->prepare($sql);
        $stmt->execute($data);
        return (int)self::db()->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        $assignments = [];
        foreach ($data as $key => $value) {
            $assignments[] = "{$key} = :{$key}";
        }
        $data['id'] = $id;
        $sql = 'UPDATE ' . static::$table . ' SET ' . implode(', ', $assignments) . ' WHERE id = :id';
        $stmt = self::db()->prepare($sql);
        $stmt->execute($data);
    }
}