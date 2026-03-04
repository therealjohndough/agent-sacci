<?php

namespace App\Controllers;

use Core\Auth;
use Core\Database;
use PDOException;

class PortalController extends BaseController
{
    public function index(): void
    {
        $this->requireLogin();
        $user = Auth::user();
        $orgId = $user['organization_id'] ?? null;

        try {
            $pdo = Database::getConnection();

            if ($orgId) {
                $stmt = $pdo->prepare(
                    "SELECT a.id, a.name, a.description, a.category, a.brand,
                            a.file_type, a.created_at
                     FROM assets a
                     WHERE a.visibility = 'public'
                       AND (a.org_id IS NULL OR a.org_id = :org_id)
                     ORDER BY a.created_at DESC"
                );
                $stmt->execute(['org_id' => $orgId]);
            } else {
                $stmt = $pdo->query(
                    "SELECT a.id, a.name, a.description, a.category, a.brand,
                            a.file_type, a.created_at
                     FROM assets a
                     WHERE a.visibility = 'public'
                       AND a.org_id IS NULL
                     ORDER BY a.created_at DESC"
                );
            }

            $assets = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (PDOException) {
            $assets = [];
        }

        $this->render('portal/dashboard', [
            'assets' => $assets,
            'orgId'  => $orgId,
        ]);
    }
}
