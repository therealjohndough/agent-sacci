<?php

namespace App\Controllers;

use App\Models\Asset;
use Core\Auth;
use Core\View;

class AssetController extends BaseController
{
    public function index(): void
    {
        $this->requireLogin();
        $assets = Asset::findBy([]);
        $this->render('app/assets/index', [
            'assets' => $assets,
        ]);
    }

    public function show(): void
    {
        $this->requireLogin();
        $id = (int)($_GET['id'] ?? 0);
        $asset = Asset::find($id);
        if (!$asset) {
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
        $id = (int)($_GET['id'] ?? 0);
        $asset = Asset::find($id);
        if (!$asset) {
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
}