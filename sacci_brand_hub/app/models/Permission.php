<?php

namespace App\Models;

use Core\Database;

class Permission extends BaseModel
{
    protected static string $table = 'permissions';

    public static function findByRoleId(int $roleId): array
    {
        $sql = 'SELECT p.* FROM permissions p
                JOIN role_permissions rp ON rp.permission_id = p.id
                WHERE rp.role_id = :rid';
        $stmt = self::db()->prepare($sql);
        $stmt->execute(['rid' => $roleId]);
        return $stmt->fetchAll();
    }
}