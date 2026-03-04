<?php

namespace App\Controllers;

use App\Models\Strain;
use Core\Csrf;
use Core\Database;
use PDOException;

class ProductController extends BaseController
{
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
