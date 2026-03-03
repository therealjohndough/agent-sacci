<?php

namespace App\Controllers;

use App\Models\Report;
use PDOException;

class ReportController extends BaseController
{
    public function index(): void
    {
        $this->requireLogin();

        $reportId = (int) ($_GET['id'] ?? 0);

        try {
            if ($reportId > 0) {
                $this->show($reportId);
                return;
            }

            $this->render('app/reports/index', [
                'reports' => Report::findAllWithRelations(),
            ]);
        } catch (PDOException) {
            $this->render('app/reports/index', [
                'reports' => [],
                'setupRequired' => true,
            ]);
        }
    }

    private function show(int $reportId): void
    {
        $report = Report::findWithRelations($reportId);
        if (!$report) {
            http_response_code(404);
            echo 'Report not found';
            return;
        }

        $this->render('app/reports/show', [
            'report' => $report,
            'entries' => Report::findEntries($reportId),
        ]);
    }
}
