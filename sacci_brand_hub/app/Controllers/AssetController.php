<?php

namespace App\Controllers;

use App\Models\Asset;
use Core\Auth;
use Core\Csrf;
use Core\Database;
use PDOException;

class AssetController extends BaseController
{
    /** Roles that may only see public assets. */
    private const RETAILER_ROLES = ['retailer_manager', 'retailer_user'];

    public function index(): void
    {
        $this->requireLogin();

        $category = isset($_GET['category']) ? trim($_GET['category']) : null;
        $search   = isset($_GET['q']) ? trim($_GET['q']) : null;

        $pdo = Database::getConnection();

        try {
            $where  = [];
            $params = [];

            // Visibility gate for retailer roles
            if ($this->isRetailer()) {
                $where[] = "a.visibility = 'public'";
                $user    = Auth::user();
                if (!empty($user['organization_id'])) {
                    $where[]          = '(a.org_id IS NULL OR a.org_id = :org_id)';
                    $params['org_id'] = $user['organization_id'];
                } else {
                    $where[] = 'a.org_id IS NULL';
                }
            }

            if ($category) {
                $where[]            = 'a.category = :category';
                $params['category'] = $category;
            }

            if ($search !== null && $search !== '') {
                $where[]     = '(a.name LIKE :q OR a.description LIKE :q2)';
                $params['q'] = '%' . $search . '%';
                $params['q2'] = '%' . $search . '%';
            }

            $whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

            $stmt = $pdo->prepare(
                "SELECT a.id, a.name, a.description, a.category, a.brand,
                        a.file_type, a.visibility, a.created_at
                 FROM assets a
                 {$whereSql}
                 ORDER BY a.created_at DESC"
            );
            $stmt->execute($params);
            $assets = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (PDOException) {
            $assets = [];
        }

        $categories = [
            'sell-sheet', 'social', 'photography', 'packaging',
            'logo', 'video', 'document', 'other',
        ];

        $this->render('app/assets/index', [
            'assets'         => $assets,
            'categories'     => $categories,
            'filterCategory' => $category,
            'filterSearch'   => $search,
            'canUpload'      => Auth::hasPermission('assets.manage'),
        ]);
    }

    public function show(): void
    {
        $this->requireLogin();
        $id    = (int) ($_GET['id'] ?? 0);
        $asset = Asset::find($id);

        if (!$asset || !$this->canAccessAsset($asset)) {
            http_response_code(404);
            echo 'Asset not found';
            return;
        }

        $this->render('app/assets/show', [
            'asset' => $asset,
        ]);
    }

    public function download(): void
    {
        $this->requireLogin();
        $id    = (int) ($_GET['id'] ?? 0);
        $asset = Asset::find($id);

        if (!$asset || !$this->canAccessAsset($asset)) {
            http_response_code(404);
            echo 'Asset not found';
            return;
        }

        $path = dirname(__DIR__, 2) . '/storage/' . $asset['filepath'];
        if (!file_exists($path)) {
            http_response_code(404);
            echo 'File not found';
            return;
        }

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($asset['filepath']) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($path));
        readfile($path);
        exit;
    }

    public function uploadForm(): void
    {
        $this->requireLogin();
        if (!Auth::hasPermission('assets.manage')) {
            http_response_code(403);
            echo '403 Forbidden';
            return;
        }

        $this->render('app/assets/upload', [
            'csrf'     => $this->csrfToken(),
            'products' => $this->loadProducts(),
        ]);
    }

    public function upload(): void
    {
        $this->requireLogin();
        if (!Auth::hasPermission('assets.manage')) {
            http_response_code(403);
            echo '403 Forbidden';
            return;
        }

        $token = (string) ($_POST['_csrf'] ?? '');
        if (!Csrf::validate($token)) {
            die('Invalid CSRF token');
        }

        $name       = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $category   = trim($_POST['category'] ?? 'other');
        $brand      = trim($_POST['brand'] ?? '');
        $productId  = (int) ($_POST['linked_product_id'] ?? 0) ?: null;
        $visibility = trim($_POST['visibility'] ?? 'internal');

        $validCategories   = ['sell-sheet', 'social', 'photography', 'packaging', 'logo', 'video', 'document', 'other'];
        $validVisibilities = ['public', 'internal', 'org'];

        if ($name === '') {
            $this->renderUploadError('Name is required.');
            return;
        }
        if (!in_array($category, $validCategories, true)) {
            $category = 'other';
        }
        if (!in_array($visibility, $validVisibilities, true)) {
            $visibility = 'internal';
        }

        if (empty($_FILES['asset_file']['tmp_name'])) {
            $this->renderUploadError('No file uploaded.');
            return;
        }

        if ($_FILES['asset_file']['size'] > 50 * 1024 * 1024) {
            $this->renderUploadError('File exceeds 50 MB limit.');
            return;
        }

        $allowedMimes = [
            'image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml',
            'application/pdf',
            'video/mp4', 'video/quicktime', 'video/x-msvideo',
            'application/zip', 'application/x-zip-compressed',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        ];

        $finfo    = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($_FILES['asset_file']['tmp_name']);

        if (!in_array($mimeType, $allowedMimes, true)) {
            $this->renderUploadError('File type not allowed: ' . htmlspecialchars($mimeType));
            return;
        }

        // Build safe filename
        $origName = basename($_FILES['asset_file']['name']);
        $safeName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $origName);
        $safeName = ltrim($safeName, '.');
        $filename = time() . '_' . $safeName;
        $relPath  = 'assets/' . $category . '/' . $filename;
        $absDir   = dirname(__DIR__, 2) . '/storage/assets/' . $category;
        $absPath  = $absDir . '/' . $filename;

        if (!is_dir($absDir) && !mkdir($absDir, 0755, true)) {
            $this->renderUploadError('Could not create storage directory.');
            return;
        }

        if (!move_uploaded_file($_FILES['asset_file']['tmp_name'], $absPath)) {
            $this->renderUploadError('File upload failed.');
            return;
        }

        $user = Auth::user();
        $data = [
            'name'        => $name,
            'description' => $description !== '' ? $description : null,
            'brand'       => $brand !== '' ? $brand : null,
            'category'    => $category,
            'file_type'   => $mimeType,
            'filepath'    => $relPath,
            'visibility'  => $visibility,
            'org_id'      => $user['organization_id'] ?? null,
            'uploaded_by' => $user['id'],
        ];
        if ($productId) {
            $data['linked_product_id'] = $productId;
        }

        try {
            Asset::create($data);
        } catch (\Exception $e) {
            @unlink($absPath);
            $this->renderUploadError('Database error: ' . htmlspecialchars($e->getMessage()));
            return;
        }

        $this->redirect('/assets');
    }

    // -----------------------------------------------------------------------

    private function isRetailer(): bool
    {
        $user = Auth::user();
        if (!$user) {
            return false;
        }
        $roles = \App\Models\Role::findByUserId($user['id']);
        foreach ($roles as $role) {
            if (in_array($role['name'], self::RETAILER_ROLES, true)) {
                return true;
            }
        }
        return false;
    }

    private function canAccessAsset(array $asset): bool
    {
        if (!$this->isRetailer()) {
            return true;
        }
        if ($asset['visibility'] !== 'public') {
            return false;
        }
        $user = Auth::user();
        if (!empty($user['organization_id']) && !empty($asset['org_id'])) {
            return (int) $asset['org_id'] === (int) $user['organization_id'];
        }
        return true;
    }

    private function loadProducts(): array
    {
        try {
            return Database::getConnection()
                ->query('SELECT id, product_name FROM products ORDER BY product_name')
                ->fetchAll(\PDO::FETCH_ASSOC);
        } catch (PDOException) {
            return [];
        }
    }

    private function renderUploadError(string $error): void
    {
        $this->render('app/assets/upload', [
            'csrf'     => $this->csrfToken(),
            'products' => $this->loadProducts(),
            'error'    => $error,
        ]);
    }
}
