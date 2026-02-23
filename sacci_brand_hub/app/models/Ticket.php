<?php

namespace App\Models;

use Core\Database;

class Ticket extends BaseModel
{
    protected static string $table = 'tickets';

    public static function findByAssignee(int $userId): array
    {
        $stmt = self::db()->prepare('SELECT * FROM tickets WHERE assigned_to = :uid ORDER BY due_date');
        $stmt->execute(['uid' => $userId]);
        return $stmt->fetchAll();
    }
}