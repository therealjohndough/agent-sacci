<?php

namespace App\Models;

class Strain extends BaseModel
{
    protected static string $table = 'strains';

    public static function findAllOrdered(): array
    {
        $stmt = self::db()->query(
            'SELECT id, name, status
             FROM strains
             ORDER BY
                CASE status
                    WHEN "active" THEN 1
                    WHEN "draft" THEN 2
                    ELSE 3
                END,
                name ASC'
        );

        return $stmt->fetchAll();
    }

    public static function findAllWithCounts(): array
    {
        $stmt = self::db()->query(
            'SELECT s.*,
                    COUNT(DISTINCT b.id) AS batch_count,
                    COUNT(DISTINCT p.id) AS product_count
             FROM strains s
             LEFT JOIN batches b ON b.strain_id = s.id
             LEFT JOIN products p ON p.strain_id = s.id
             GROUP BY s.id
             ORDER BY
                CASE s.status
                    WHEN "active" THEN 1
                    WHEN "archived" THEN 3
                    ELSE 2
                END,
                s.updated_at DESC'
        );

        return $stmt->fetchAll();
    }

    public static function findWithCounts(int $id): ?array
    {
        $stmt = self::db()->prepare(
            'SELECT s.*,
                    COUNT(DISTINCT b.id) AS batch_count,
                    COUNT(DISTINCT p.id) AS product_count
             FROM strains s
             LEFT JOIN batches b ON b.strain_id = s.id
             LEFT JOIN products p ON p.strain_id = s.id
             WHERE s.id = :id
             GROUP BY s.id
             LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        $strain = $stmt->fetch();

        return $strain ?: null;
    }
}
