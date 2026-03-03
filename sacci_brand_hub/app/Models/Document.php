<?php

namespace App\Models;

class Document extends BaseModel
{
    protected static string $table = 'documents';

    public static function countActive(): int
    {
        $stmt = self::db()->prepare(
            'SELECT COUNT(*)
             FROM documents
             WHERE status = "active"'
        );
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    public static function findAllWithRelations(): array
    {
        $stmt = self::db()->query(
            'SELECT d.*, dept.name AS department_name, u.name AS owner_name
             FROM documents d
             LEFT JOIN departments dept ON dept.id = d.department_id
             LEFT JOIN users u ON u.id = d.owner_user_id
             ORDER BY
                CASE d.status
                    WHEN "active" THEN 1
                    WHEN "draft" THEN 2
                    ELSE 3
                END,
                d.updated_at DESC'
        );

        return $stmt->fetchAll();
    }

    public static function findWithRelations(int $id): ?array
    {
        $stmt = self::db()->prepare(
            'SELECT d.*, dept.name AS department_name, u.name AS owner_name
             FROM documents d
             LEFT JOIN departments dept ON dept.id = d.department_id
             LEFT JOIN users u ON u.id = d.owner_user_id
             WHERE d.id = :id
             LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        $document = $stmt->fetch();

        return $document ?: null;
    }
}
