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

    public static function findRecentPublished(int $limit = 5): array
    {
        $limit = max(1, $limit);
        $stmt = self::db()->query(
            'SELECT id, title
             FROM reports
             WHERE status = "published"
             ORDER BY COALESCE(reporting_period_end, reporting_period_start, DATE(updated_at)) DESC
             LIMIT ' . $limit
        );

        return $stmt->fetchAll();
    }

    public static function searchByTerm(string $term, int $limit = 8): array
    {
        $limit = max(1, $limit);
        $stmt = self::db()->prepare(
            'SELECT id, title, summary
             FROM reports
             WHERE title LIKE :term OR summary LIKE :term
             ORDER BY COALESCE(reporting_period_end, reporting_period_start, DATE(updated_at)) DESC
             LIMIT ' . $limit
        );
        $stmt->execute(['term' => '%' . $term . '%']);

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

    public static function createEntry(array $data): int
    {
        $columns = array_keys($data);
        $placeholders = array_map(fn($column) => ':' . $column, $columns);
        $sql = 'INSERT INTO report_entries (' . implode(', ', $columns) . ')
                VALUES (' . implode(', ', $placeholders) . ')';
        $stmt = self::db()->prepare($sql);
        $stmt->execute($data);

        return (int) self::db()->lastInsertId();
    }

    public static function findEntry(int $entryId): ?array
    {
        $stmt = self::db()->prepare(
            'SELECT *
             FROM report_entries
             WHERE id = :id
             LIMIT 1'
        );
        $stmt->execute(['id' => $entryId]);
        $entry = $stmt->fetch();

        return $entry ?: null;
    }

    public static function updateEntry(int $entryId, array $data): void
    {
        $assignments = [];
        foreach ($data as $key => $value) {
            $assignments[] = $key . ' = :' . $key;
        }

        $data['id'] = $entryId;
        $sql = 'UPDATE report_entries
                SET ' . implode(', ', $assignments) . '
                WHERE id = :id';
        $stmt = self::db()->prepare($sql);
        $stmt->execute($data);
    }
}
