<?php

namespace App\Controllers;

use App\Models\ActionItem;
use App\Models\Document;
use App\Models\Meeting;
use App\Models\Report;
use PDOException;

class ExecutiveDashboardController extends BaseController
{
    public function index(): void
    {
        $this->requireLogin();

        try {
            $this->render('app/dashboard/executive', [
                'recentMeetings' => Meeting::findRecent(5),
                'openActions' => ActionItem::findRecentOpen(6),
                'recentReports' => Report::findRecentPublished(5),
                'recentDocuments' => Document::findRecentActive(5),
            ]);
        } catch (PDOException) {
            $this->render('app/dashboard/executive', [
                'recentMeetings' => [],
                'openActions' => [],
                'recentReports' => [],
                'recentDocuments' => [],
                'setupRequired' => true,
            ]);
        }
    }
}
