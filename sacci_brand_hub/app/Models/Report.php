<?php

namespace App\Models;

class Report extends BaseModel
{
    protected static string $table = 'reports';

    public static function countPublished(): int
    {
        $stmt = self::db()->prepare(
            'SELECT COUNT(*)
             FROM reports
             WHERE status = "published"'
        );
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    public static function findAllWithRelations(): array
    {
        $stmt = self::db()->query(
            'SELECT r.*, d.name AS department_name, u.name AS owner_name
             FROM reports r
             LEFT JOIN departments d ON d.id = r.department_id
             LEFT JOIN users u ON u.id = r.owner_user_id
             ORDER BY
                CASE r.status
                    WHEN "published" THEN 1
                    WHEN "draft" THEN 2
                    ELSE 3
                END,
                COALESCE(r.reporting_period_end, r.reporting_period_start, DATE(r.updated_at)) DESC'
        );

        return $stmt->fetchAll();
    }

    public static function findWithRelations(int $id): ?array
    {
        $stmt = self::db()->prepare(
            'SELECT r.*, d.name AS department_name, u.name AS owner_name
             FROM reports r
             LEFT JOIN departments d ON d.id = r.department_id
             LEFT JOIN users u ON u.id = r.owner_user_id
             WHERE r.id = :id
             LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        $report = $stmt->fetch();

        return $report ?: null;
    }

    public static function findEntries(int $reportId): array
    {
        $stmt = self::db()->prepare(
            'SELECT *
             FROM report_entries
             WHERE report_id = :report_id
             ORDER BY sort_order ASC, id ASC'
        );
        $stmt->execute(['report_id' => $reportId]);

        return $stmt->fetchAll();
    }
}
