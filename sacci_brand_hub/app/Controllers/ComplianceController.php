<?php

namespace App\Controllers;

use Core\Database;
use PDOException;

class ComplianceController extends BaseController
{
    public function index(): void
    {
        $this->requireLogin();

        try {
            $pdo = Database::getConnection();

            // All non-archived batches with their most recent COA
            $stmt = $pdo->query(
                "SELECT b.id AS batch_id, b.batch_code, b.harvest_date,
                        b.production_status,
                        s.name AS strain_name,
                        c.id AS coa_id, c.status AS coa_status,
                        c.lab_name, c.tested_date, c.received_date, c.file_path
                 FROM batches b
                 JOIN strains s ON s.id = b.strain_id
                 LEFT JOIN coas c ON c.batch_id = b.id
                     AND c.id = (
                         SELECT MAX(c2.id) FROM coas c2 WHERE c2.batch_id = b.id
                     )
                 WHERE b.production_status != 'archived'
                 ORDER BY
                     FIELD(b.production_status, 'active', 'testing', 'approved', 'planned'),
                     b.batch_code"
            );
            $batches = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (PDOException) {
            $batches = [];
        }

        // Summary counts
        $total    = count($batches);
        $approved = 0;
        $missing  = 0;
        $flagged  = 0; // pending or expired

        foreach ($batches as $b) {
            if ($b['coa_status'] === 'approved') {
                $approved++;
            } elseif ($b['coa_id'] === null) {
                $missing++;
            } elseif (in_array($b['coa_status'], ['pending', 'expired'], true)) {
                $flagged++;
            }
        }

        $this->render('app/compliance/index', [
            'batches'  => $batches,
            'total'    => $total,
            'approved' => $approved,
            'missing'  => $missing,
            'flagged'  => $flagged,
        ]);
    }
}
