<?php

namespace App\Controllers;

use App\Models\Department;
use App\Models\Report;
use Core\Auth;
use Core\Csrf;
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

    public function create(): void
    {
        $this->requireLogin();

        try {
            $this->renderReportForm($this->defaultFormValues());
        } catch (PDOException) {
            $this->renderReportForm($this->defaultFormValues(), null, true);
        }
    }

    public function edit(): void
    {
        $this->requireLogin();

        $reportId = (int) ($_GET['id'] ?? 0);
        $report = Report::find($reportId);
        if (!$report) {
            http_response_code(404);
            echo 'Report not found';
            return;
        }

        $this->renderReportForm([
            'id' => (string) $report['id'],
            'title' => $report['title'] ?? '',
            'report_type' => $report['report_type'] ?? 'general',
            'department_id' => $report['department_id'] !== null ? (string) $report['department_id'] : '',
            'reporting_period_start' => $report['reporting_period_start'] ?? '',
            'reporting_period_end' => $report['reporting_period_end'] ?? '',
            'status' => $report['status'] ?? 'draft',
            'source_url' => $report['source_url'] ?? '',
            'summary' => $report['summary'] ?? '',
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

        if ($values['title'] === '') {
            $this->renderReportForm($values, 'Title is required.');
            return;
        }

        try {
            $reportId = Report::create([
                'title' => $values['title'],
                'slug' => $this->makeSlug($values['title']),
                'report_type' => $values['report_type'],
                'department_id' => $values['department_id'] !== '' ? (int) $values['department_id'] : null,
                'owner_user_id' => Auth::user()['id'] ?? null,
                'reporting_period_start' => $values['reporting_period_start'] !== '' ? $values['reporting_period_start'] : null,
                'reporting_period_end' => $values['reporting_period_end'] !== '' ? $values['reporting_period_end'] : null,
                'status' => $values['status'],
                'source_url' => $values['source_url'] !== '' ? $values['source_url'] : null,
                'summary' => $values['summary'] !== '' ? $values['summary'] : null,
            ]);
        } catch (PDOException) {
            $this->renderReportForm($values, 'The reports tables are not ready yet. Run migrations first.', true);
            return;
        }

        $this->redirect('/reports?id=' . $reportId);
    }

    public function update(): void
    {
        $this->requireLogin();

        $reportId = (int) ($_POST['id'] ?? 0);
        $report = Report::find($reportId);
        if (!$report) {
            http_response_code(404);
            echo 'Report not found';
            return;
        }

        $values = $this->submittedFormValues();
        $values['id'] = (string) $reportId;
        $token = (string) ($_POST['_csrf'] ?? '');

        if (!Csrf::validate($token)) {
            die('Invalid CSRF token');
        }

        if ($values['title'] === '') {
            $this->renderReportForm($values, 'Title is required.', false, true);
            return;
        }

        try {
            Report::update($reportId, [
                'title' => $values['title'],
                'report_type' => $values['report_type'],
                'department_id' => $values['department_id'] !== '' ? (int) $values['department_id'] : null,
                'reporting_period_start' => $values['reporting_period_start'] !== '' ? $values['reporting_period_start'] : null,
                'reporting_period_end' => $values['reporting_period_end'] !== '' ? $values['reporting_period_end'] : null,
                'status' => $values['status'],
                'source_url' => $values['source_url'] !== '' ? $values['source_url'] : null,
                'summary' => $values['summary'] !== '' ? $values['summary'] : null,
            ]);
        } catch (PDOException) {
            $this->renderReportForm($values, 'Unable to update report right now.', true, true);
            return;
        }

        $this->redirect('/reports?id=' . $reportId);
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

    private function renderReportForm(array $values, ?string $error = null, bool $setupRequired = false, bool $isEdit = false): void
    {
        try {
            $departments = Department::findAllOrdered();
        } catch (PDOException) {
            $departments = [];
            $setupRequired = true;
        }

        $this->render('app/reports/create', [
            'csrf' => $this->csrfToken(),
            'departments' => $departments,
            'values' => $values,
            'error' => $error,
            'setupRequired' => $setupRequired,
            'isEdit' => $isEdit,
        ]);
    }

    private function defaultFormValues(): array
    {
        return [
            'title' => '',
            'report_type' => 'general',
            'department_id' => '',
            'reporting_period_start' => '',
            'reporting_period_end' => '',
            'status' => 'draft',
            'source_url' => '',
            'summary' => '',
        ];
    }

    private function submittedFormValues(): array
    {
        return [
            'title' => trim((string) ($_POST['title'] ?? '')),
            'report_type' => trim((string) ($_POST['report_type'] ?? 'general')),
            'department_id' => trim((string) ($_POST['department_id'] ?? '')),
            'reporting_period_start' => trim((string) ($_POST['reporting_period_start'] ?? '')),
            'reporting_period_end' => trim((string) ($_POST['reporting_period_end'] ?? '')),
            'status' => trim((string) ($_POST['status'] ?? 'draft')),
            'source_url' => trim((string) ($_POST['source_url'] ?? '')),
            'summary' => trim((string) ($_POST['summary'] ?? '')),
        ];
    }

    private function makeSlug(string $title): string
    {
        $slug = strtolower($title);
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug) ?? '';
        $slug = trim($slug, '-');

        if ($slug === '') {
            $slug = 'report';
        }

        return $slug . '-' . date('YmdHis');
    }
}
