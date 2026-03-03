<?php

namespace App\Models;

class ActionItem extends BaseModel
{
    protected static string $table = 'action_items';

    public static function findAllWithRelations(): array
    {
        $stmt = self::db()->query(
            'SELECT ai.*, d.name AS department_name, u.name AS owner_name
             FROM action_items ai
             LEFT JOIN departments d ON d.id = ai.department_id
             LEFT JOIN users u ON u.id = ai.owner_user_id
             ORDER BY
                CASE ai.status
                    WHEN "open" THEN 1
                    WHEN "in_progress" THEN 2
                    WHEN "blocked" THEN 3
                    WHEN "done" THEN 4
                    ELSE 5
                END,
                ai.due_date IS NULL,
                ai.due_date ASC,
                ai.created_at DESC'
        );

        return $stmt->fetchAll();
    }

    public static function findBySource(string $sourceType, int $sourceId): array
    {
        $stmt = self::db()->prepare(
            'SELECT ai.*, d.name AS department_name, u.name AS owner_name
             FROM action_items ai
             LEFT JOIN departments d ON d.id = ai.department_id
             LEFT JOIN users u ON u.id = ai.owner_user_id
             WHERE ai.source_type = :source_type AND ai.source_id = :source_id
             ORDER BY ai.due_date IS NULL, ai.due_date ASC, ai.created_at ASC'
        );
        $stmt->execute([
            'source_type' => $sourceType,
            'source_id' => $sourceId,
        ]);

        return $stmt->fetchAll();
    }
}
