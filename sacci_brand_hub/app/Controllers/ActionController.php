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
            $this->renderActionForm($this->defaultFormValues());
        } catch (PDOException) {
            $this->renderActionForm($this->defaultFormValues(), null, true);
        }
    }

    public function edit(): void
    {
        $this->requireLogin();

        $actionId = (int) ($_GET['id'] ?? 0);
        $actionItem = ActionItem::find($actionId);
        if (!$actionItem) {
            http_response_code(404);
            echo 'Action item not found';
            return;
        }

        $this->renderActionForm([
            'id' => (string) $actionItem['id'],
            'title' => $actionItem['title'] ?? '',
            'details' => $actionItem['details'] ?? '',
            'status' => $actionItem['status'] ?? 'open',
            'priority' => $actionItem['priority'] ?? 'medium',
            'department_id' => $actionItem['department_id'] !== null ? (string) $actionItem['department_id'] : '',
            'meeting_id' => ($actionItem['source_type'] ?? '') === 'meeting' && $actionItem['source_id'] !== null ? (string) $actionItem['source_id'] : '',
            'due_date' => $actionItem['due_date'] ?? '',
        ], null, false, true);
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
            $this->renderActionForm($values, 'Title is required.');
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
            $this->renderActionForm($values, 'The actions tables are not ready yet. Run migrations first.', true);
            return;
        }

        $this->redirect('/actions');
    }

    public function update(): void
    {
        $this->requireLogin();

        $actionId = (int) ($_POST['id'] ?? 0);
        $actionItem = ActionItem::find($actionId);
        if (!$actionItem) {
            http_response_code(404);
            echo 'Action item not found';
            return;
        }

        $values = $this->submittedFormValues();
        $values['id'] = (string) $actionId;
        $token = (string) ($_POST['_csrf'] ?? '');

        if (!Csrf::validate($token)) {
            die('Invalid CSRF token');
        }

        if ($values['title'] === '') {
            $this->renderActionForm($values, 'Title is required.', false, true);
            return;
        }

        try {
            ActionItem::update($actionId, [
                'title' => $values['title'],
                'details' => $values['details'] !== '' ? $values['details'] : null,
                'status' => $values['status'],
                'priority' => $values['priority'],
                'department_id' => $values['department_id'] !== '' ? (int) $values['department_id'] : null,
                'source_type' => $values['meeting_id'] !== '' ? 'meeting' : 'manual',
                'source_id' => $values['meeting_id'] !== '' ? (int) $values['meeting_id'] : null,
                'due_date' => $values['due_date'] !== '' ? $values['due_date'] : null,
            ]);
        } catch (PDOException) {
            $this->renderActionForm($values, 'Unable to update action item right now.', true, true);
            return;
        }

        $this->redirect('/actions');
    }

    private function renderActionForm(array $values, ?string $error = null, bool $setupRequired = false, bool $isEdit = false): void
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
            'isEdit' => $isEdit,
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
