<?php

namespace App\Controllers;

use App\Models\ActionItem;
use App\Models\Document;
use App\Models\Meeting;
use App\Models\Report;
use App\Models\Ticket;
use Core\Auth;
use Core\Database;
use PDOException;

class DashboardController extends BaseController
{
    public function index(): void
    {
        $this->requireLogin();
        $user = Auth::user();
        $tickets = Ticket::findByAssignee($user['id']);
        $metrics = $this->loadSummaryMetrics();

        // Surface the storage exposure warning to admin users only.
        $storageWarning = false;
        if (!empty($_SERVER['_STORAGE_EXPOSED']) && Auth::hasPermission('user.manage')) {
            $storageWarning = true;
        }

        $this->render('app/dashboard', [
            'user'          => $user,
            'tickets'       => $tickets,
            'metrics'       => $metrics,
            'stats'         => $this->loadCatalogStats(),
            'recentBatches' => $this->loadRecentBatches(),
            'strains'       => $this->loadStrains(),
            'csrf'          => $this->csrfToken(),
            'storageWarning' => $storageWarning,
        ]);
    }

    private function loadCatalogStats(): array
    {
        try {
            $pdo = Database::getConnection();
            return [
                'strains'  => (int) $pdo->query("SELECT COUNT(*) FROM strains WHERE status = 'active'")->fetchColumn(),
                'batches'  => (int) $pdo->query("SELECT COUNT(*) FROM batches WHERE production_status != 'archived'")->fetchColumn(),
                'products' => (int) $pdo->query('SELECT COUNT(*) FROM products')->fetchColumn(),
                'coas'     => (int) $pdo->query('SELECT COUNT(*) FROM coas')->fetchColumn(),
            ];
        } catch (PDOException) {
            return ['strains' => 0, 'batches' => 0, 'products' => 0, 'coas' => 0];
        }
    }

    private function loadRecentBatches(): array
    {
        try {
            $pdo  = Database::getConnection();
            $stmt = $pdo->query(
                "SELECT b.id, b.batch_code, b.production_status, b.harvest_date,
                        b.thc_percent, b.cbd_percent, b.terp_1_name, b.terp_1_pct,
                        b.mood_tag, s.name AS strain_name
                 FROM batches b
                 JOIN strains s ON b.strain_id = s.id
                 WHERE b.production_status != 'archived'
                 ORDER BY b.id DESC
                 LIMIT 6"
            );
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (PDOException) {
            return [];
        }
    }

    private function loadStrains(): array
    {
        try {
            $pdo  = Database::getConnection();
            $stmt = $pdo->query(
                "SELECT s.id, s.name, s.category, s.thc_ref, s.terp_1_ref, s.terp_2_ref,
                        (SELECT COUNT(*) FROM batches
                         WHERE strain_id = s.id AND production_status != 'archived') AS batch_count,
                        (SELECT COUNT(*) FROM products WHERE strain_id = s.id) AS product_count
                 FROM strains s
                 WHERE s.status = 'active'
                 ORDER BY s.name"
            );
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (PDOException) {
            return [];
        }
    }

    private function loadSummaryMetrics(): array
    {
        try {
            return [
                [
                    'label' => 'Meetings',
                    'value' => Meeting::countAll(),
                    'link'  => '/meetings',
                ],
                [
                    'label' => 'Open Actions',
                    'value' => ActionItem::countOpen(),
                    'link'  => '/actions',
                ],
                [
                    'label' => 'Published Reports',
                    'value' => Report::countPublished(),
                    'link'  => '/reports',
                ],
                [
                    'label' => 'Active Documents',
                    'value' => Document::countActive(),
                    'link'  => '/documents',
                ],
            ];
        } catch (PDOException) {
            return [];
        }
    }
}
