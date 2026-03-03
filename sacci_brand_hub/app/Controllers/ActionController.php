<?php

namespace App\Controllers;

use App\Models\ActionItem;
use App\Models\Department;
use App\Models\Meeting;
use Core\Auth;
use Core\Csrf;
use PDOException;

class ActionController extends BaseController
{
    public function index(): void
    {
        $this->requireLogin();

        try {
            $this->render('app/actions/index', [
                'actionItems' => ActionItem::findAllWithRelations(),
            ]);
        } catch (PDOException) {
            $this->render('app/actions/index', [
                'actionItems' => [],
                'setupRequired' => true,
            ]);
        }
    }

    public function create(): void
    {
        $this->requireLogin();

        try {
            $this->render('app/actions/create', [
                'csrf' => $this->csrfToken(),
                'departments' => Department::findAllOrdered(),
                'meetings' => Meeting::findRecent(20),
                'values' => $this->defaultFormValues(),
            ]);
        } catch (PDOException) {
            $this->render('app/actions/create', [
                'csrf' => $this->csrfToken(),
                'departments' => [],
                'meetings' => [],
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

        if ($values['title'] === '') {
            $this->renderCreateForm($values, 'Title is required.');
            return;
        }

        try {
            ActionItem::create([
                'title' => $values['title'],
                'details' => $values['details'] !== '' ? $values['details'] : null,
                'status' => $values['status'],
                'priority' => $values['priority'],
                'department_id' => $values['department_id'] !== '' ? (int) $values['department_id'] : null,
                'owner_user_id' => Auth::user()['id'] ?? null,
                'created_by_user_id' => Auth::user()['id'] ?? null,
                'source_type' => $values['meeting_id'] !== '' ? 'meeting' : 'manual',
                'source_id' => $values['meeting_id'] !== '' ? (int) $values['meeting_id'] : null,
                'due_date' => $values['due_date'] !== '' ? $values['due_date'] : null,
            ]);
        } catch (PDOException) {
            $this->renderCreateForm($values, 'The actions tables are not ready yet. Run migrations first.', true);
            return;
        }

        $this->redirect('/actions');
    }

    private function renderCreateForm(array $values, string $error, bool $setupRequired = false): void
    {
        try {
            $departments = Department::findAllOrdered();
            $meetings = Meeting::findRecent(20);
        } catch (PDOException) {
            $departments = [];
            $meetings = [];
            $setupRequired = true;
        }

        $this->render('app/actions/create', [
            'csrf' => $this->csrfToken(),
            'departments' => $departments,
            'meetings' => $meetings,
            'values' => $values,
            'error' => $error,
            'setupRequired' => $setupRequired,
        ]);
    }

    private function defaultFormValues(): array
    {
        return [
            'title' => '',
            'details' => '',
            'status' => 'open',
            'priority' => 'medium',
            'department_id' => '',
            'meeting_id' => '',
            'due_date' => '',
        ];
    }

    private function submittedFormValues(): array
    {
        return [
            'title' => trim((string) ($_POST['title'] ?? '')),
            'details' => trim((string) ($_POST['details'] ?? '')),
            'status' => trim((string) ($_POST['status'] ?? 'open')),
            'priority' => trim((string) ($_POST['priority'] ?? 'medium')),
            'department_id' => trim((string) ($_POST['department_id'] ?? '')),
            'meeting_id' => trim((string) ($_POST['meeting_id'] ?? '')),
            'due_date' => trim((string) ($_POST['due_date'] ?? '')),
        ];
    }
}
