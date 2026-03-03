<?php

namespace App\Controllers;

use App\Models\Batch;
use App\Models\Strain;
use Core\Csrf;
use PDOException;

class BatchController extends BaseController
{
    public function index(): void
    {
        $this->requireLogin();

        $batchId = (int) ($_GET['id'] ?? 0);

        try {
            if ($batchId > 0) {
                $this->show($batchId);
                return;
            }

            $this->render('app/batches/index', [
                'batches' => Batch::findAllWithRelations(),
            ]);
        } catch (PDOException) {
            $this->render('app/batches/index', [
                'batches' => [],
                'setupRequired' => true,
            ]);
        }
    }

    public function create(): void
    {
        $this->requireLogin();

        try {
            $this->renderBatchForm($this->defaultFormValues());
        } catch (PDOException) {
            $this->renderBatchForm($this->defaultFormValues(), null, true);
        }
    }

    public function edit(): void
    {
        $this->requireLogin();

        $batchId = (int) ($_GET['id'] ?? 0);
        $batch = Batch::find($batchId);
        if (!$batch) {
            http_response_code(404);
            echo 'Batch not found';
            return;
        }

        try {
            $this->renderBatchForm([
                'id' => (string) $batch['id'],
                'strain_id' => (string) $batch['strain_id'],
                'batch_code' => $batch['batch_code'] ?? '',
                'harvest_date' => $batch['harvest_date'] ?? '',
                'production_status' => $batch['production_status'] ?? 'planned',
                'thc_percent' => $batch['thc_percent'] !== null ? (string) $batch['thc_percent'] : '',
                'cbd_percent' => $batch['cbd_percent'] !== null ? (string) $batch['cbd_percent'] : '',
                'notes' => $batch['notes'] ?? '',
            ], null, false, true);
        } catch (PDOException) {
            $this->renderBatchForm($this->defaultFormValues(), 'The strains tables are not ready yet. Run migration 014 first.', true, true);
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

        if ($values['strain_id'] === '' || $values['batch_code'] === '') {
            $this->renderBatchForm($values, 'Strain and batch code are required.');
            return;
        }

        try {
            $batchId = Batch::create([
                'strain_id' => (int) $values['strain_id'],
                'batch_code' => $values['batch_code'],
                'harvest_date' => $values['harvest_date'] !== '' ? $values['harvest_date'] : null,
                'production_status' => $values['production_status'],
                'thc_percent' => $values['thc_percent'] !== '' ? (float) $values['thc_percent'] : null,
                'cbd_percent' => $values['cbd_percent'] !== '' ? (float) $values['cbd_percent'] : null,
                'notes' => $values['notes'] !== '' ? $values['notes'] : null,
            ]);
        } catch (PDOException) {
            $this->renderBatchForm($values, 'The batch tables are not ready yet. Run migration 014 first.', true);
            return;
        }

        $this->redirect('/batches?id=' . $batchId);
    }

    public function update(): void
    {
        $this->requireLogin();

        $batchId = (int) ($_POST['id'] ?? 0);
        $batch = Batch::find($batchId);
        if (!$batch) {
            http_response_code(404);
            echo 'Batch not found';
            return;
        }

        $values = $this->submittedFormValues();
        $values['id'] = (string) $batchId;
        $token = (string) ($_POST['_csrf'] ?? '');

        if (!Csrf::validate($token)) {
            die('Invalid CSRF token');
        }

        if ($values['strain_id'] === '' || $values['batch_code'] === '') {
            $this->renderBatchForm($values, 'Strain and batch code are required.', false, true);
            return;
        }

        try {
            Batch::update($batchId, [
                'strain_id' => (int) $values['strain_id'],
                'batch_code' => $values['batch_code'],
                'harvest_date' => $values['harvest_date'] !== '' ? $values['harvest_date'] : null,
                'production_status' => $values['production_status'],
                'thc_percent' => $values['thc_percent'] !== '' ? (float) $values['thc_percent'] : null,
                'cbd_percent' => $values['cbd_percent'] !== '' ? (float) $values['cbd_percent'] : null,
                'notes' => $values['notes'] !== '' ? $values['notes'] : null,
            ]);
        } catch (PDOException) {
            $this->renderBatchForm($values, 'Unable to update batch right now.', true, true);
            return;
        }

        $this->redirect('/batches?id=' . $batchId);
    }

    public function archive(): void
    {
        $this->requireLogin();

        $batchId = (int) ($_POST['id'] ?? 0);
        $batch = Batch::find($batchId);
        if (!$batch) {
            http_response_code(404);
            echo 'Batch not found';
            return;
        }

        $token = (string) ($_POST['_csrf'] ?? '');
        if (!Csrf::validate($token)) {
            die('Invalid CSRF token');
        }

        try {
            Batch::update($batchId, [
                'production_status' => 'archived',
            ]);
        } catch (PDOException) {
            echo 'Unable to archive batch';
            return;
        }

        $this->redirect('/batches');
    }

    private function show(int $batchId): void
    {
        $batch = Batch::findWithRelations($batchId);
        if (!$batch) {
            http_response_code(404);
            echo 'Batch not found';
            return;
        }

        $this->render('app/batches/show', [
            'batch' => $batch,
            'csrf' => $this->csrfToken(),
        ]);
    }

    private function renderBatchForm(array $values, ?string $error = null, bool $setupRequired = false, bool $isEdit = false): void
    {
        try {
            $strains = Strain::findAllOrdered();
        } catch (PDOException) {
            $strains = [];
            $setupRequired = true;
        }

        $this->render('app/batches/create', [
            'csrf' => $this->csrfToken(),
            'strains' => $strains,
            'values' => $values,
            'error' => $error,
            'setupRequired' => $setupRequired,
            'isEdit' => $isEdit,
        ]);
    }

    private function defaultFormValues(): array
    {
        return [
            'strain_id' => '',
            'batch_code' => '',
            'harvest_date' => '',
            'production_status' => 'planned',
            'thc_percent' => '',
            'cbd_percent' => '',
            'notes' => '',
        ];
    }

    private function submittedFormValues(): array
    {
        return [
            'strain_id' => trim((string) ($_POST['strain_id'] ?? '')),
            'batch_code' => trim((string) ($_POST['batch_code'] ?? '')),
            'harvest_date' => trim((string) ($_POST['harvest_date'] ?? '')),
            'production_status' => trim((string) ($_POST['production_status'] ?? 'planned')),
            'thc_percent' => trim((string) ($_POST['thc_percent'] ?? '')),
            'cbd_percent' => trim((string) ($_POST['cbd_percent'] ?? '')),
            'notes' => trim((string) ($_POST['notes'] ?? '')),
        ];
    }
}
