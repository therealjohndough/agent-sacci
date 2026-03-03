<?php

namespace App\Models;

use Core\Database;

class Role extends BaseModel
{
    protected static string $table = 'roles';

    /**
     * Return roles associated with a user.
     */
    public static function findByUserId(int $userId): array
    {
        $sql = 'SELECT r.* FROM roles r
                JOIN user_roles ur ON ur.role_id = r.id
                WHERE ur.user_id = :uid';
        $stmt = self::db()->prepare($sql);
        $stmt->execute(['uid' => $userId]);
        return $stmt->fetchAll();
    }
}