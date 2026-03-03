<?php

namespace App\Models;

class Setting extends BaseModel
{
    protected static string $table = 'settings';

    public static function get(string $key): ?string
    {
        $stmt = self::db()->prepare('SELECT `value` FROM settings WHERE `key` = :key_name LIMIT 1');
        $stmt->execute(['key_name' => $key]);
        $value = $stmt->fetchColumn();

        return $value === false ? null : (string) $value;
    }

    public static function getJson(string $key): array
    {
        $value = self::get($key);
        if ($value === null || $value === '') {
            return [];
        }

        $decoded = json_decode($value, true);

        return is_array($decoded) ? $decoded : [];
    }
}
