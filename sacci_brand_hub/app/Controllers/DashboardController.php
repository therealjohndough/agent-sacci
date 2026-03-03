<?php

namespace App\Controllers;

use App\Models\ActionItem;
use App\Models\Document;
use App\Models\Meeting;
use App\Models\Report;
use App\Models\Ticket;
use Core\Auth;
use PDOException;

class DashboardController extends BaseController
{
    public function index(): void
    {
        $this->requireLogin();
        $user = Auth::user();
        $tickets = Ticket::findByAssignee($user['id']);
        $metrics = $this->loadSummaryMetrics();
        $this->render('app/dashboard', [
            'user' => $user,
            'tickets' => $tickets,
            'metrics' => $metrics,
            'csrf' => $this->csrfToken(),
        ]);
    }

    private function loadSummaryMetrics(): array
    {
        try {
            return [
                [
                    'label' => 'Meetings',
                    'value' => Meeting::countAll(),
                    'link' => '/meetings',
                ],
                [
                    'label' => 'Open Actions',
                    'value' => ActionItem::countOpen(),
                    'link' => '/actions',
                ],
                [
                    'label' => 'Published Reports',
                    'value' => Report::countPublished(),
                    'link' => '/reports',
                ],
                [
                    'label' => 'Active Documents',
                    'value' => Document::countActive(),
                    'link' => '/documents',
                ],
            ];
        } catch (PDOException) {
            return [];
        }
    }
}
