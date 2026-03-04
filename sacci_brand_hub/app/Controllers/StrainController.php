<?php

namespace App\Controllers;

use App\Models\Strain;
use Core\Csrf;
use Core\Database;
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

    public function importForm(): void
    {
        $this->requireLogin();
        $this->render('app/strains/import', [
            'csrf' => $this->csrfToken(),
        ]);
    }

    public function import(): void
    {
        $this->requireLogin();

        $token = (string) ($_POST['_csrf'] ?? '');
        if (!Csrf::validate($token)) {
            die('Invalid CSRF token');
        }

        if (empty($_FILES['csv']['tmp_name'])) {
            $this->render('app/strains/import', [
                'csrf'  => $this->csrfToken(),
                'error' => 'No file uploaded.',
            ]);
            return;
        }

        $handle = fopen($_FILES['csv']['tmp_name'], 'r');
        if ($handle === false) {
            $this->render('app/strains/import', [
                'csrf'  => $this->csrfToken(),
                'error' => 'Could not read uploaded file.',
            ]);
            return;
        }

        // Skip header row
        fgetcsv($handle);

        $pdo      = Database::getConnection();
        $inserted = 0;
        $updated  = 0;
        $errors   = [];

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 12) {
                continue;
            }

            [
                $name, $category, $geneticsA, $geneticsB,
                $lineageNotes, $thcRaw, $cbgRaw, $cbnRaw,
                $terp1, $terp2, $terp3, $description,
            ] = array_pad($row, 12, '');

            $name = trim($name);
            if ($name === '') {
                continue;
            }

            $slug    = $this->pureSlug($name);
            $lineage = trim($geneticsA);
            if (trim($geneticsB) !== '') {
                $lineage .= ' x ' . trim($geneticsB);
            }

            $data = [
                'name'        => $name,
                'slug'        => $slug,
                'category'    => trim($category) !== '' ? trim($category) : null,
                'lineage'     => $lineage !== '' ? $lineage : null,
                'awards'      => trim($lineageNotes) !== '' ? trim($lineageNotes) : null,
                'description' => trim($description) !== '' ? trim($description) : null,
                'thc_ref'     => $this->parsePct($thcRaw),
                'cbg_ref'     => $this->parsePct($cbgRaw),
                'cbn_ref'     => $this->parsePct($cbnRaw),
                'terp_1_ref'  => trim($terp1) !== '' ? trim($terp1) : null,
                'terp_2_ref'  => trim($terp2) !== '' ? trim($terp2) : null,
                'terp_3_ref'  => trim($terp3) !== '' ? trim($terp3) : null,
                'status'      => 'active',
            ];

            try {
                $existing = $pdo->prepare('SELECT id FROM strains WHERE slug = :slug LIMIT 1');
                $existing->execute(['slug' => $slug]);
                $row = $existing->fetch();

                if ($row) {
                    unset($data['slug']); // don't change slug on update
                    Strain::update((int) $row['id'], $data);
                    $updated++;
                } else {
                    Strain::create($data);
                    $inserted++;
                }
            } catch (\Exception $e) {
                $errors[] = htmlspecialchars($name) . ': ' . htmlspecialchars($e->getMessage());
            }
        }

        fclose($handle);

        $this->render('app/strains/import', [
            'csrf'     => $this->csrfToken(),
            'result'   => compact('inserted', 'updated', 'errors'),
        ]);
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

    /** Slug without timestamp suffix — used for CSV import upserts. */
    private function pureSlug(string $name): string
    {
        $slug = strtolower(trim($name));
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug) ?? '';
        $slug = trim($slug, '-');
        return $slug !== '' ? $slug : 'strain';
    }

    /** Parse a percentage string like "30%", "<1%", "1.2%" → float or null. */
    private function parsePct(string $value): ?float
    {
        $v = trim($value);
        if ($v === '' || $v === '<1%' || $v === '<1') {
            return null;
        }
        return (float) rtrim($v, '%');
    }
}
