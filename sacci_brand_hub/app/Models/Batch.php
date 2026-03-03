<?php

namespace App\Models;

class Batch extends BaseModel
{
    protected static string $table = 'batches';

    public static function findAllWithRelations(): array
    {
        $stmt = self::db()->query(
            'SELECT b.*,
                    s.name AS strain_name,
                    COUNT(DISTINCT c.id) AS coa_count,
                    COUNT(DISTINCT p.id) AS product_count
             FROM batches b
             INNER JOIN strains s ON s.id = b.strain_id
             LEFT JOIN coas c ON c.batch_id = b.id
             LEFT JOIN products p ON p.current_batch_id = b.id
             GROUP BY b.id
             ORDER BY
                CASE b.production_status
                    WHEN "active" THEN 1
                    WHEN "approved" THEN 2
                    WHEN "testing" THEN 3
                    WHEN "planned" THEN 4
                    ELSE 5
                END,
                b.updated_at DESC'
        );

        return $stmt->fetchAll();
    }

    public static function findWithRelations(int $id): ?array
    {
        $stmt = self::db()->prepare(
            'SELECT b.*,
                    s.name AS strain_name,
                    COUNT(DISTINCT c.id) AS coa_count,
                    COUNT(DISTINCT p.id) AS product_count
             FROM batches b
             INNER JOIN strains s ON s.id = b.strain_id
             LEFT JOIN coas c ON c.batch_id = b.id
             LEFT JOIN products p ON p.current_batch_id = b.id
             WHERE b.id = :id
             GROUP BY b.id
             LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        $batch = $stmt->fetch();

        return $batch ?: null;
    }
}
