<?php

namespace App\Controllers;

use App\Models\Strain;
use Core\Csrf;
use PDOException;

class StrainController extends BaseController
{
    public function index(): void
    {
        $this->requireLogin();

        $strainId = (int) ($_GET['id'] ?? 0);

        try {
            if ($strainId > 0) {
                $this->show($strainId);
                return;
            }

            $this->render('app/strains/index', [
                'strains' => Strain::findAllWithCounts(),
            ]);
        } catch (PDOException) {
            $this->render('app/strains/index', [
                'strains' => [],
                'setupRequired' => true,
            ]);
        }
    }

    public function create(): void
    {
        $this->requireLogin();
        $this->renderStrainForm($this->defaultFormValues());
    }

    public function edit(): void
    {
        $this->requireLogin();

        $strainId = (int) ($_GET['id'] ?? 0);
        $strain = Strain::find($strainId);
        if (!$strain) {
            http_response_code(404);
            echo 'Strain not found';
            return;
        }

        $this->renderStrainForm([
            'id' => (string) $strain['id'],
            'name' => $strain['name'] ?? '',
            'lineage' => $strain['lineage'] ?? '',
            'category' => $strain['category'] ?? '',
            'breeder' => $strain['breeder'] ?? '',
            'status' => $strain['status'] ?? 'active',
            'description' => $strain['description'] ?? '',
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

        if ($values['name'] === '') {
            $this->renderStrainForm($values, 'Name is required.');
            return;
        }

        try {
            $strainId = Strain::create([
                'name' => $values['name'],
                'slug' => $this->makeSlug($values['name']),
                'lineage' => $values['lineage'] !== '' ? $values['lineage'] : null,
                'category' => $values['category'] !== '' ? $values['category'] : null,
                'breeder' => $values['breeder'] !== '' ? $values['breeder'] : null,
                'description' => $values['description'] !== '' ? $values['description'] : null,
                'status' => $values['status'],
            ]);
        } catch (PDOException) {
            $this->renderStrainForm($values, 'The strains tables are not ready yet. Run migration 014 first.', true);
            return;
        }

        $this->redirect('/strains?id=' . $strainId);
    }

    public function update(): void
    {
        $this->requireLogin();

        $strainId = (int) ($_POST['id'] ?? 0);
        $strain = Strain::find($strainId);
        if (!$strain) {
            http_response_code(404);
            echo 'Strain not found';
            return;
        }

        $values = $this->submittedFormValues();
        $values['id'] = (string) $strainId;
        $token = (string) ($_POST['_csrf'] ?? '');

        if (!Csrf::validate($token)) {
            die('Invalid CSRF token');
        }

        if ($values['name'] === '') {
            $this->renderStrainForm($values, 'Name is required.', false, true);
            return;
        }

        try {
            Strain::update($strainId, [
                'name' => $values['name'],
                'lineage' => $values['lineage'] !== '' ? $values['lineage'] : null,
                'category' => $values['category'] !== '' ? $values['category'] : null,
                'breeder' => $values['breeder'] !== '' ? $values['breeder'] : null,
                'description' => $values['description'] !== '' ? $values['description'] : null,
                'status' => $values['status'],
            ]);
        } catch (PDOException) {
            $this->renderStrainForm($values, 'Unable to update strain right now.', true, true);
            return;
        }

        $this->redirect('/strains?id=' . $strainId);
    }

    public function archive(): void
    {
        $this->requireLogin();

        $strainId = (int) ($_POST['id'] ?? 0);
        $strain = Strain::find($strainId);
        if (!$strain) {
            http_response_code(404);
            echo 'Strain not found';
            return;
        }

        $token = (string) ($_POST['_csrf'] ?? '');
        if (!Csrf::validate($token)) {
            die('Invalid CSRF token');
        }

        try {
            Strain::update($strainId, [
                'status' => 'archived',
            ]);
        } catch (PDOException) {
            echo 'Unable to archive strain';
            return;
        }

        $this->redirect('/strains');
    }

    private function show(int $strainId): void
    {
        $strain = Strain::findWithCounts($strainId);
        if (!$strain) {
            http_response_code(404);
            echo 'Strain not found';
            return;
        }

        $this->render('app/strains/show', [
            'strain' => $strain,
            'csrf' => $this->csrfToken(),
        ]);
    }

    private function renderStrainForm(array $values, ?string $error = null, bool $setupRequired = false, bool $isEdit = false): void
    {
        $this->render('app/strains/create', [
            'csrf' => $this->csrfToken(),
            'values' => $values,
            'error' => $error,
            'setupRequired' => $setupRequired,
            'isEdit' => $isEdit,
        ]);
    }

    private function defaultFormValues(): array
    {
        return [
            'name' => '',
            'lineage' => '',
            'category' => '',
            'breeder' => '',
            'status' => 'active',
            'description' => '',
        ];
    }

    private function submittedFormValues(): array
    {
        return [
            'name' => trim((string) ($_POST['name'] ?? '')),
            'lineage' => trim((string) ($_POST['lineage'] ?? '')),
            'category' => trim((string) ($_POST['category'] ?? '')),
            'breeder' => trim((string) ($_POST['breeder'] ?? '')),
            'status' => trim((string) ($_POST['status'] ?? 'active')),
            'description' => trim((string) ($_POST['description'] ?? '')),
        ];
    }

    private function makeSlug(string $name): string
    {
        $slug = strtolower($name);
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug) ?? '';
        $slug = trim($slug, '-');

        if ($slug === '') {
            $slug = 'strain';
        }

        return $slug . '-' . date('YmdHis');
    }
}
