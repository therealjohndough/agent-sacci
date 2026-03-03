<?php

namespace App\Models;

class Meeting extends BaseModel
{
    protected static string $table = 'meetings';

    public static function countAll(): int
    {
        return (int) self::db()->query('SELECT COUNT(*) FROM meetings')->fetchColumn();
    }

    public static function findAllWithDepartment(): array
    {
        $stmt = self::db()->query(
            'SELECT m.*, d.name AS department_name
             FROM meetings m
             LEFT JOIN departments d ON d.id = m.department_id
             ORDER BY COALESCE(m.occurred_at, m.scheduled_for, m.created_at) DESC'
        );

        return $stmt->fetchAll();
    }

    public static function findRecent(int $limit = 5): array
    {
        $limit = max(1, $limit);
        $stmt = self::db()->query(
            'SELECT id, title
             FROM meetings
             ORDER BY COALESCE(occurred_at, scheduled_for, created_at) DESC
             LIMIT ' . $limit
        );

        return $stmt->fetchAll();
    }

    public static function searchByTerm(string $term, int $limit = 8): array
    {
        $limit = max(1, $limit);
        $stmt = self::db()->prepare(
            'SELECT id, title, summary
             FROM meetings
             WHERE title LIKE :term OR summary LIKE :term OR notes LIKE :term
             ORDER BY COALESCE(occurred_at, scheduled_for, created_at) DESC
             LIMIT ' . $limit
        );
        $stmt->execute(['term' => '%' . $term . '%']);

        return $stmt->fetchAll();
    }

    public static function findWithDepartment(int $id): ?array
    {
        $stmt = self::db()->prepare(
            'SELECT m.*, d.name AS department_name
             FROM meetings m
             LEFT JOIN departments d ON d.id = m.department_id
             WHERE m.id = :id
             LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        $meeting = $stmt->fetch();

        return $meeting ?: null;
    }

    public static function findDecisions(int $meetingId): array
    {
        $stmt = self::db()->prepare(
            'SELECT md.*, u.name AS owner_name
             FROM meeting_decisions md
             LEFT JOIN users u ON u.id = md.owner_user_id
             WHERE md.meeting_id = :meeting_id
             ORDER BY md.created_at ASC'
        );
        $stmt->execute(['meeting_id' => $meetingId]);

        return $stmt->fetchAll();
    }
}
