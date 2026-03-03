<?php

namespace App\Models;

class Department extends BaseModel
{
    protected static string $table = 'departments';

    public static function findAllOrdered(): array
    {
        $stmt = self::db()->query(
            'SELECT *
             FROM departments
             ORDER BY name ASC'
        );

        return $stmt->fetchAll();
    }
}
