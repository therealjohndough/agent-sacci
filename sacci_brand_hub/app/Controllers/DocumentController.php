<?php

namespace App\Controllers;

use App\Models\Department;
use App\Models\Document;
use Core\Auth;
use Core\Csrf;
use PDOException;

class DocumentController extends BaseController
{
    public function index(): void
    {
        $this->requireLogin();

        $documentId = (int) ($_GET['id'] ?? 0);

        try {
            if ($documentId > 0) {
                $this->show($documentId);
                return;
            }

            $this->render('app/documents/index', [
                'documents' => Document::findAllWithRelations(),
            ]);
        } catch (PDOException) {
            $this->render('app/documents/index', [
                'documents' => [],
                'setupRequired' => true,
            ]);
        }
    }

    public function create(): void
    {
        $this->requireLogin();

        try {
            $this->renderDocumentForm($this->defaultFormValues());
        } catch (PDOException) {
            $this->renderDocumentForm($this->defaultFormValues(), null, true);
        }
    }

    public function edit(): void
    {
        $this->requireLogin();

        $documentId = (int) ($_GET['id'] ?? 0);
        $document = Document::find($documentId);
        if (!$document) {
            http_response_code(404);
            echo 'Document not found';
            return;
        }

        $this->renderDocumentForm([
            'id' => (string) $document['id'],
            'title' => $document['title'] ?? '',
            'document_type' => $document['document_type'] ?? 'reference',
            'department_id' => $document['department_id'] !== null ? (string) $document['department_id'] : '',
            'status' => $document['status'] ?? 'draft',
            'source_url' => $document['source_url'] ?? '',
            'version_label' => $document['version_label'] ?? '',
            'content' => $document['content'] ?? '',
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

        if ($values['title'] === '' || $values['content'] === '') {
            $this->renderDocumentForm($values, 'Title and content are required.');
            return;
        }

        try {
            $documentId = Document::create([
                'title' => $values['title'],
                'slug' => $this->makeSlug($values['title']),
                'document_type' => $values['document_type'],
                'department_id' => $values['department_id'] !== '' ? (int) $values['department_id'] : null,
                'owner_user_id' => Auth::user()['id'] ?? null,
                'status' => $values['status'],
                'source_url' => $values['source_url'] !== '' ? $values['source_url'] : null,
                'content' => $values['content'],
                'version_label' => $values['version_label'] !== '' ? $values['version_label'] : null,
            ]);
        } catch (PDOException) {
            $this->renderDocumentForm($values, 'The documents tables are not ready yet. Run migrations first.', true);
            return;
        }

        $this->redirect('/documents?id=' . $documentId);
    }

    public function update(): void
    {
        $this->requireLogin();

        $documentId = (int) ($_POST['id'] ?? 0);
        $document = Document::find($documentId);
        if (!$document) {
            http_response_code(404);
            echo 'Document not found';
            return;
        }

        $values = $this->submittedFormValues();
        $values['id'] = (string) $documentId;
        $token = (string) ($_POST['_csrf'] ?? '');

        if (!Csrf::validate($token)) {
            die('Invalid CSRF token');
        }

        if ($values['title'] === '' || $values['content'] === '') {
            $this->renderDocumentForm($values, 'Title and content are required.', false, true);
            return;
        }

        try {
            Document::update($documentId, [
                'title' => $values['title'],
                'document_type' => $values['document_type'],
                'department_id' => $values['department_id'] !== '' ? (int) $values['department_id'] : null,
                'status' => $values['status'],
                'source_url' => $values['source_url'] !== '' ? $values['source_url'] : null,
                'content' => $values['content'],
                'version_label' => $values['version_label'] !== '' ? $values['version_label'] : null,
            ]);
        } catch (PDOException) {
            $this->renderDocumentForm($values, 'Unable to update document right now.', true, true);
            return;
        }

        $this->redirect('/documents?id=' . $documentId);
    }

    private function show(int $documentId): void
    {
        $document = Document::findWithRelations($documentId);
        if (!$document) {
            http_response_code(404);
            echo 'Document not found';
            return;
        }

        $this->render('app/documents/show', [
            'document' => $document,
        ]);
    }

    private function renderDocumentForm(array $values, ?string $error = null, bool $setupRequired = false, bool $isEdit = false): void
    {
        try {
            $departments = Department::findAllOrdered();
        } catch (PDOException) {
            $departments = [];
            $setupRequired = true;
        }

        $this->render('app/documents/create', [
            'csrf' => $this->csrfToken(),
            'departments' => $departments,
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
            'document_type' => 'reference',
            'department_id' => '',
            'status' => 'draft',
            'source_url' => '',
            'version_label' => '',
            'content' => '',
        ];
    }

    private function submittedFormValues(): array
    {
        return [
            'title' => trim((string) ($_POST['title'] ?? '')),
            'document_type' => trim((string) ($_POST['document_type'] ?? 'reference')),
            'department_id' => trim((string) ($_POST['department_id'] ?? '')),
            'status' => trim((string) ($_POST['status'] ?? 'draft')),
            'source_url' => trim((string) ($_POST['source_url'] ?? '')),
            'version_label' => trim((string) ($_POST['version_label'] ?? '')),
            'content' => trim((string) ($_POST['content'] ?? '')),
        ];
    }

    private function makeSlug(string $title): string
    {
        $slug = strtolower($title);
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug) ?? '';
        $slug = trim($slug, '-');

        if ($slug === '') {
            $slug = 'document';
        }

        return $slug . '-' . date('YmdHis');
    }
}
