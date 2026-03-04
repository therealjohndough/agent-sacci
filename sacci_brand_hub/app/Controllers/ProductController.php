<?php

namespace App\Controllers;

use App\Models\Strain;
use Core\Csrf;
use Core\Database;
use PDOException;

class ProductController extends BaseController
{
    public function index(): void
    {
        $this->requireLogin();

        $id = (int) ($_GET['id'] ?? 0);
        if ($id > 0) {
            $this->show($id);
            return;
        }

        $pdo = Database::getConnection();

        $strainId = isset($_GET['strain_id']) ? (int) $_GET['strain_id'] : null;
        $type     = isset($_GET['type']) ? trim($_GET['type']) : null;

        $where  = [];
        $params = [];

        if ($strainId) {
            $where[]              = 'p.strain_id = :strain_id';
            $params['strain_id']  = $strainId;
        }
        if ($type && in_array($type, ['flower', 'pre-roll', 'concentrate', 'vape'], true)) {
            $where[]       = 'p.format = :format';
            $params['format'] = $type;
        }

        $whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

        try {
            $stmt = $pdo->prepare(
                "SELECT p.id, p.sku, p.product_name, p.format, p.weight_label,
                        p.notes_label, p.mood_tag, p.internal_status,
                        s.id AS strain_id, s.name AS strain_name
                 FROM products p
                 JOIN strains s ON p.strain_id = s.id
                 {$whereSql}
                 ORDER BY s.name, p.format, p.weight_label"
            );
            $stmt->execute($params);
            $products = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $strains = $pdo->query('SELECT id, name FROM strains WHERE status = \'active\' ORDER BY name')
                          ->fetchAll(\PDO::FETCH_ASSOC);
        } catch (PDOException) {
            $products = [];
            $strains  = [];
        }

        $this->render('app/products/index', [
            'products'        => $products,
            'strains'         => $strains,
            'filterStrainId'  => $strainId,
            'filterType'      => $type,
        ]);
    }

    private function show(int $id): void
    {
        $pdo = Database::getConnection();

        try {
            $stmt = $pdo->prepare(
                "SELECT p.*, s.id AS strain_id, s.name AS strain_name,
                        s.category AS strain_category, s.thc_ref, s.cbg_ref, s.cbn_ref,
                        s.terp_1_ref, s.terp_2_ref, s.terp_3_ref, s.description AS strain_description
                 FROM products p
                 JOIN strains s ON p.strain_id = s.id
                 WHERE p.id = :id
                 LIMIT 1"
            );
            $stmt->execute(['id' => $id]);
            $product = $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (PDOException) {
            $product = null;
        }

        if (!$product) {
            http_response_code(404);
            echo 'Product not found';
            return;
        }

        try {
            $batchStmt = $pdo->prepare(
                "SELECT b.id, b.batch_code, b.production_status, b.harvest_date,
                        b.thc_percent, b.cbd_percent, b.cbg_percent, b.cbn_percent,
                        b.terp_total, b.terp_1_name, b.terp_1_pct,
                        b.terp_2_name, b.terp_2_pct, b.mood_tag,
                        c.id AS coa_id, c.file_path AS coa_path
                 FROM batches b
                 LEFT JOIN coas c ON c.batch_id = b.id
                 WHERE b.strain_id = :strain_id AND b.production_status != 'archived'
                 ORDER BY b.id DESC
                 LIMIT 5"
            );
            $batchStmt->execute(['strain_id' => $product['strain_id']]);
            $batches = $batchStmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (PDOException) {
            $batches = [];
        }

        $this->render('app/products/show', [
            'product' => $product,
            'batches' => $batches,
        ]);
    }

    public function importForm(): void
    {
        $this->requireLogin();
        $this->render('app/products/import', [
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
            $this->render('app/products/import', [
                'csrf'  => $this->csrfToken(),
                'error' => 'No file uploaded.',
            ]);
            return;
        }

        $handle = fopen($_FILES['csv']['tmp_name'], 'r');
        if ($handle === false) {
            $this->render('app/products/import', [
                'csrf'  => $this->csrfToken(),
                'error' => 'Could not read uploaded file.',
            ]);
            return;
        }

        // Build strain lookup: slug → id, and normalized-slug → id fallback
        $pdo = Database::getConnection();
        $strainRows = $pdo->query('SELECT id, name, slug FROM strains')->fetchAll();
        $strainBySlug = [];
        $strainByNorm = [];
        foreach ($strainRows as $s) {
            $strainBySlug[$s['slug']] = (int) $s['id'];
            $strainByNorm[$this->normalizeSlug($s['slug'])] = (int) $s['id'];
        }

        // Skip header row
        fgetcsv($handle);

        $inserted = 0;
        $updated  = 0;
        $skipped  = 0;
        $errors   = [];

        while (($row = fgetcsv($handle)) !== false) {
            // Columns: Product Name, Variety/Note, Weight/Size, Category, Notes,
            //          THC, Genetics A, Genetics B, Effects, Flavor, Consumer Psychology,
            //          DESCRIPTION, Dough's New Copy, Vibe
            if (count($row) < 4) {
                continue;
            }

            $rawName     = $row[0] ?? '';
            $weightLabel = trim($row[2] ?? '');
            $category    = strtolower(trim($row[3] ?? ''));
            $notesLabel  = trim($row[4] ?? '');
            $description = trim($row[11] ?? '');
            $moodTag     = trim($row[13] ?? '');

            $cleanName = $this->stripEmoji($rawName);
            if ($cleanName === '') {
                continue;
            }

            // Resolve strain
            $strainSlug = $this->toSlug($cleanName);
            $strainId   = $strainBySlug[$strainSlug]
                ?? $strainByNorm[$this->normalizeSlug($strainSlug)]
                ?? null;

            if ($strainId === null) {
                $skipped++;
                $errors[] = 'No strain found for: ' . htmlspecialchars($cleanName);
                continue;
            }

            // Normalize product_type
            $format = match (true) {
                str_contains($category, 'pre') => 'pre-roll',
                str_contains($category, 'concentrate') => 'concentrate',
                str_contains($category, 'vape') => 'vape',
                default => 'flower',
            };

            // Build SKU
            $strainSlugForSku = array_search($strainId, $strainBySlug) ?: $strainSlug;
            $sku = substr($strainSlugForSku . '-' . $format . '-' . $this->normalizeSlug($weightLabel), 0, 100);

            $productName = $cleanName . ' — ' . strtoupper($format) . ' ' . $weightLabel;

            $data = [
                'strain_id'    => $strainId,
                'sku'          => $sku,
                'product_name' => $productName,
                'format'       => $format,
                'weight_label' => $weightLabel !== '' ? $weightLabel : null,
                'notes_label'  => $notesLabel !== '' ? $notesLabel : null,
                'description'  => $description !== '' ? $description : null,
                'mood_tag'     => $moodTag !== '' ? $moodTag : null,
                'internal_status' => 'active',
            ];

            try {
                $existing = $pdo->prepare('SELECT id FROM products WHERE sku = :sku LIMIT 1');
                $existing->execute(['sku' => $sku]);
                $existingRow = $existing->fetch();

                if ($existingRow) {
                    unset($data['sku']);
                    unset($data['strain_id']);
                    \App\Models\Product::update((int) $existingRow['id'], $data);
                    $updated++;
                } else {
                    \App\Models\Product::create($data);
                    $inserted++;
                }
            } catch (\Exception $e) {
                $errors[] = htmlspecialchars($sku) . ': ' . htmlspecialchars($e->getMessage());
            }
        }

        fclose($handle);

        $this->render('app/products/import', [
            'csrf'   => $this->csrfToken(),
            'result' => compact('inserted', 'updated', 'skipped', 'errors'),
        ]);
    }

    /** Strip emoji and non-Latin-1 characters, then trim. */
    private function stripEmoji(string $s): string
    {
        $s = preg_replace('/[^\x{0000}-\x{00FF}]/u', '', $s) ?? $s;
        return trim($s);
    }

    /** Convert a name to a URL slug. */
    private function toSlug(string $name): string
    {
        $slug = strtolower(trim($name));
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug) ?? '';
        return trim($slug, '-');
    }

    /**
     * Normalize a slug for fuzzy matching (remove hyphens/spaces).
     * Handles "PuffinZ" vs "puffin-z" → both become "puffinz".
     */
    private function normalizeSlug(string $slug): string
    {
        return preg_replace('/[^a-z0-9]/', '', strtolower($slug)) ?? '';
    }
}
