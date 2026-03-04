<?php

namespace App\Controllers;

use App\Models\Batch;
use App\Models\Strain;
use Core\Csrf;
use Core\CoaParser;
use Core\TerpeneVibeMap;
use Core\Database;
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

    public function coaUploadForm(): void
    {
        $this->requireLogin();

        try {
            $strains = Strain::findAllOrdered();
        } catch (PDOException) {
            $strains = [];
        }

        $this->render('app/batches/coa_upload', [
            'csrf'    => $this->csrfToken(),
            'strains' => $strains,
        ]);
    }

    public function coaUpload(): void
    {
        $this->requireLogin();

        $token = (string) ($_POST['_csrf'] ?? '');
        if (!Csrf::validate($token)) {
            die('Invalid CSRF token');
        }

        $strainId = (int) ($_POST['strain_id'] ?? 0);
        if ($strainId <= 0) {
            $this->renderCoaUploadError('Please select a strain.', $strainId);
            return;
        }

        if (empty($_FILES['coa_pdf']['tmp_name'])) {
            $this->renderCoaUploadError('No PDF uploaded.', $strainId);
            return;
        }

        $tmpPath = $_FILES['coa_pdf']['tmp_name'];
        $origName = basename($_FILES['coa_pdf']['name'] ?? 'coa.pdf');

        // Validate PDF
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $tmpPath);
        finfo_close($finfo);
        if ($mime !== 'application/pdf') {
            $this->renderCoaUploadError('Uploaded file must be a PDF.', $strainId);
            return;
        }

        if (filesize($tmpPath) > 10 * 1024 * 1024) {
            $this->renderCoaUploadError('PDF must be 10 MB or smaller.', $strainId);
            return;
        }

        // Fetch strain slug for filename
        $pdo = Database::getConnection();
        $strainRow = $pdo->prepare('SELECT slug FROM strains WHERE id = :id LIMIT 1');
        $strainRow->execute(['id' => $strainId]);
        $strain = $strainRow->fetch();
        if (!$strain) {
            $this->renderCoaUploadError('Strain not found.', $strainId);
            return;
        }

        // Save to storage/coas/
        $storageDir = __DIR__ . '/../../storage/coas';
        if (!is_dir($storageDir)) {
            mkdir($storageDir, 0755, true);
        }
        $filename = $strain['slug'] . '_' . time() . '.pdf';
        $destPath = $storageDir . '/' . $filename;
        if (!move_uploaded_file($tmpPath, $destPath)) {
            $this->renderCoaUploadError('Could not save uploaded file.', $strainId);
            return;
        }

        // Insert coas record first
        $coaId = (int) $pdo->query("SELECT LAST_INSERT_ID()")->fetchColumn(); // placeholder
        try {
            $stmt = $pdo->prepare(
                "INSERT INTO coas (strain_id, file_path, status, parse_source, created_at, updated_at)
                 VALUES (:strain_id, :file_path, 'received', 'ai', NOW(), NOW())"
            );
            $stmt->execute([
                'strain_id' => $strainId,
                'file_path' => 'storage/coas/' . $filename,
            ]);
            $coaId = (int) $pdo->lastInsertId();
        } catch (\Exception $e) {
            $this->renderCoaUploadError('Could not save COA record: ' . htmlspecialchars($e->getMessage()), $strainId);
            return;
        }

        // Parse with Claude
        try {
            $parsed = CoaParser::parse($destPath);
        } catch (\RuntimeException $e) {
            $this->renderCoaUploadError('COA parsing failed: ' . htmlspecialchars($e->getMessage()), $strainId);
            return;
        }

        // Derive mood_tag from dominant terpene
        $moodTag = !empty($parsed['terp_1_name'])
            ? TerpeneVibeMap::tag($parsed['terp_1_name'])
            : null;

        // Insert batch
        try {
            $batchData = [
                'strain_id'          => $strainId,
                'batch_code'         => $parsed['batch_number'] ?? ('COA-' . date('Ymd-His')),
                'production_status'  => 'received',
                'thc_percent'        => $parsed['thc_percent'] ?? null,
                'cbd_percent'        => $parsed['cbd_percent'] ?? null,
                'cbg_percent'        => $parsed['cbg_percent'] ?? null,
                'cbn_percent'        => $parsed['cbn_percent'] ?? null,
                'terp_total'         => $parsed['terp_total_percent'] ?? null,
                'terp_1_name'        => $parsed['terp_1_name'] ?? null,
                'terp_1_pct'         => $parsed['terp_1_pct'] ?? null,
                'terp_2_name'        => $parsed['terp_2_name'] ?? null,
                'terp_2_pct'         => $parsed['terp_2_pct'] ?? null,
                'terp_3_name'        => $parsed['terp_3_name'] ?? null,
                'terp_3_pct'         => $parsed['terp_3_pct'] ?? null,
                'mood_tag'           => $moodTag,
            ];
            $batchId = Batch::create($batchData);
        } catch (\Exception $e) {
            $this->renderCoaUploadError('Could not save batch: ' . htmlspecialchars($e->getMessage()), $strainId);
            return;
        }

        // Update coa record with batch_id, parsed_at, parse_raw
        try {
            $pdo->prepare(
                "UPDATE coas SET batch_id = :batch_id, parsed_at = NOW(), parse_raw = :raw, updated_at = NOW() WHERE id = :id"
            )->execute([
                'batch_id' => $batchId,
                'raw'      => $parsed['raw'] ?? null,
                'id'       => $coaId,
            ]);
        } catch (\Exception) {
            // Non-fatal; batch was created
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

    private function renderCoaUploadError(string $error, int $strainId = 0): void
    {
        try {
            $strains = Strain::findAllOrdered();
        } catch (PDOException) {
            $strains = [];
        }

        $this->render('app/batches/coa_upload', [
            'csrf'             => $this->csrfToken(),
            'strains'          => $strains,
            'error'            => $error,
            'selected_strain'  => $strainId,
        ]);
    }
}
