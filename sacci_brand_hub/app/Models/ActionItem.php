<?php

namespace App\Models;

class ActionItem extends BaseModel
{
    protected static string $table = 'action_items';

    private const ALLOWED_STATUSES = ['open', 'in_progress', 'blocked', 'done', 'archived'];
    private const ALLOWED_PRIORITIES = ['low', 'medium', 'high', 'urgent'];

    public static function countOpen(): int
    {
        $stmt = self::db()->prepare(
            'SELECT COUNT(*)
             FROM action_items
             WHERE status IN ("open", "in_progress", "blocked")'
        );
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

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

    public static function findRecentOpen(int $limit = 6): array
    {
        $limit = max(1, $limit);
        $stmt = self::db()->query(
            'SELECT id, title, due_date
             FROM action_items
             WHERE status IN ("open", "in_progress", "blocked")
             ORDER BY due_date IS NULL, due_date ASC, created_at DESC
             LIMIT ' . $limit
        );

        return $stmt->fetchAll();
    }

    public static function searchByTerm(string $term, int $limit = 8): array
    {
        $limit = max(1, $limit);
        $stmt = self::db()->prepare(
            'SELECT id, title, details
             FROM action_items
             WHERE title LIKE :term OR details LIKE :term
             ORDER BY due_date IS NULL, due_date ASC, created_at DESC
             LIMIT ' . $limit
        );
        $stmt->execute(['term' => '%' . $term . '%']);

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

    public static function findByAirtableRecordId(string $recordId): ?array
    {
        $stmt = self::db()->prepare(
            'SELECT *
             FROM action_items
             WHERE airtable_record_id = :record_id
             LIMIT 1'
        );
        $stmt->execute(['record_id' => $recordId]);
        $item = $stmt->fetch();

        return $item ?: null;
    }

    public static function syncFromAirtableRecord(array $record, ?int $createdByUserId = null): void
    {
        $recordId = (string) ($record['id'] ?? '');
        $fields = $record['fields'] ?? [];
        if ($recordId === '' || !is_array($fields)) {
            return;
        }

        $title = trim((string) ($fields['Title'] ?? ''));
        if ($title === '') {
            return;
        }

        $data = [
            'title' => $title,
            'details' => self::nullableString($fields['Details'] ?? null),
            'status' => self::normalizeStatus($fields['Status'] ?? null),
            'priority' => self::normalizePriority($fields['Priority'] ?? null),
            'due_date' => self::normalizeDate($fields['Due Date'] ?? null),
            'airtable_record_id' => $recordId,
        ];

        $existing = self::findByAirtableRecordId($recordId);
        if ($existing) {
            self::update((int) $existing['id'], $data);
            return;
        }

        $data['created_by_user_id'] = $createdByUserId;
        $data['owner_user_id'] = $createdByUserId;
        $data['source_type'] = 'manual';
        self::create($data);
    }

    private static function nullableString(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private static function normalizeStatus(mixed $value): string
    {
        $value = strtolower(trim((string) $value));
        $value = str_replace([' ', '-'], '_', $value);

        return in_array($value, self::ALLOWED_STATUSES, true) ? $value : 'open';
    }

    private static function normalizePriority(mixed $value): string
    {
        $value = strtolower(trim((string) $value));

        return in_array($value, self::ALLOWED_PRIORITIES, true) ? $value : 'medium';
    }

    private static function normalizeDate(mixed $value): ?string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        return substr($value, 0, 10);
    }
}
