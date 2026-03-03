<?php

namespace App\Controllers;

use App\Models\ActionItem;
use App\Models\Department;
use App\Models\Meeting;
use Core\Auth;
use Core\Csrf;
use PDOException;

class MeetingController extends BaseController
{
    public function index(): void
    {
        $this->requireLogin();

        $meetingId = (int) ($_GET['id'] ?? 0);

        try {
            if ($meetingId > 0) {
                $this->show($meetingId);
                return;
            }

            $this->render('app/meetings/index', [
                'meetings' => Meeting::findAllWithDepartment(),
            ]);
        } catch (PDOException) {
            $this->render('app/meetings/index', [
                'meetings' => [],
                'setupRequired' => true,
            ]);
        }
    }

    public function create(): void
    {
        $this->requireLogin();

        try {
            $this->render('app/meetings/create', [
                'csrf' => $this->csrfToken(),
                'departments' => Department::findAllOrdered(),
                'values' => $this->defaultFormValues(),
            ]);
        } catch (PDOException) {
            $this->render('app/meetings/create', [
                'csrf' => $this->csrfToken(),
                'departments' => [],
                'values' => $this->defaultFormValues(),
                'setupRequired' => true,
            ]);
        }
    }

    public function store(): void
    {
        $this->requireLogin();

        $values = $this->submittedFormValues();
        $token = (string) ($_POST['_csrf'] ?? '');

        if (!Csrf::validate($token)) {
            die('Invalid CSRF token');
        }

        if ($values['title'] === '' || $values['notes'] === '') {
            $this->renderCreateForm($values, 'Title and notes are required.');
            return;
        }

        try {
            $meetingId = Meeting::create([
                'title' => $values['title'],
                'slug' => $this->makeSlug($values['title']),
                'meeting_type' => $values['meeting_type'],
                'department_id' => $values['department_id'] !== '' ? (int) $values['department_id'] : null,
                'owner_user_id' => Auth::user()['id'] ?? null,
                'scheduled_for' => $values['scheduled_for'] !== '' ? $values['scheduled_for'] : null,
                'occurred_at' => $values['occurred_at'] !== '' ? $values['occurred_at'] : null,
                'status' => $values['status'],
                'summary' => $values['summary'] !== '' ? $values['summary'] : null,
                'notes' => $values['notes'],
                'source_url' => $values['source_url'] !== '' ? $values['source_url'] : null,
            ]);
        } catch (PDOException) {
            $this->renderCreateForm($values, 'The meetings tables are not ready yet. Run migrations first.', true);
            return;
        }

        $this->redirect('/meetings?id=' . $meetingId);
    }

    private function show(int $meetingId): void
    {
        $meeting = Meeting::findWithDepartment($meetingId);
        if (!$meeting) {
            http_response_code(404);
            echo 'Meeting not found';
            return;
        }

        $this->render('app/meetings/show', [
            'meeting' => $meeting,
            'decisions' => Meeting::findDecisions($meetingId),
            'actionItems' => $this->findRelatedActionItems($meetingId),
        ]);
    }

    private function findRelatedActionItems(int $meetingId): array
    {
        try {
            return ActionItem::findBySource('meeting', $meetingId);
        } catch (PDOException) {
            return [];
        }
    }

    private function renderCreateForm(array $values, string $error, bool $setupRequired = false): void
    {
        try {
            $departments = Department::findAllOrdered();
        } catch (PDOException) {
            $departments = [];
            $setupRequired = true;
        }

        $this->render('app/meetings/create', [
            'csrf' => $this->csrfToken(),
            'departments' => $departments,
            'values' => $values,
            'error' => $error,
            'setupRequired' => $setupRequired,
        ]);
    }

    private function defaultFormValues(): array
    {
        return [
            'title' => '',
            'meeting_type' => 'general',
            'department_id' => '',
            'scheduled_for' => '',
            'occurred_at' => '',
            'status' => 'draft',
            'summary' => '',
            'notes' => '',
            'source_url' => '',
        ];
    }

    private function submittedFormValues(): array
    {
        return [
            'title' => trim((string) ($_POST['title'] ?? '')),
            'meeting_type' => trim((string) ($_POST['meeting_type'] ?? 'general')),
            'department_id' => trim((string) ($_POST['department_id'] ?? '')),
            'scheduled_for' => $this->normalizeDateTime((string) ($_POST['scheduled_for'] ?? '')),
            'occurred_at' => $this->normalizeDateTime((string) ($_POST['occurred_at'] ?? '')),
            'status' => trim((string) ($_POST['status'] ?? 'draft')),
            'summary' => trim((string) ($_POST['summary'] ?? '')),
            'notes' => trim((string) ($_POST['notes'] ?? '')),
            'source_url' => trim((string) ($_POST['source_url'] ?? '')),
        ];
    }

    private function makeSlug(string $title): string
    {
        $slug = strtolower($title);
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug) ?? '';
        $slug = trim($slug, '-');

        if ($slug === '') {
            $slug = 'meeting';
        }

        return $slug . '-' . date('YmdHis');
    }

    private function normalizeDateTime(string $value): string
    {
        $value = trim($value);

        return str_replace('T', ' ', $value);
    }
}
