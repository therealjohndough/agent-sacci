<?php

namespace App\Models;

use Core\Database;
use PDO;

class User extends BaseModel
{
    protected static string $table = 'users';

    /**
     * Find user by email.
     */
    public static function findByEmail(string $email): ?array
    {
        $stmt = self::db()->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    /**
     * Verify user password using password_hash.
     */
    public static function verifyPassword(array $user, string $password): bool
    {
        return password_verify($password, $user['password_hash']);
    }
}