<?php

namespace App\Controllers;

use App\Models\ActionItem;
use App\Models\Meeting;
use PDOException;

class MeetingController extends BaseController
{
    public function index(): void
    {
        $this->requireLogin();

        $meetingId = (int) ($_GET['id'] ?? 0);

        try {
            if ($meetingId > 0) {
                $this->show($meetingId);
                return;
            }

            $this->render('app/meetings/index', [
                'meetings' => Meeting::findAllWithDepartment(),
            ]);
        } catch (PDOException) {
            $this->render('app/meetings/index', [
                'meetings' => [],
                'setupRequired' => true,
            ]);
        }
    }

    private function show(int $meetingId): void
    {
        $meeting = Meeting::findWithDepartment($meetingId);
        if (!$meeting) {
            http_response_code(404);
            echo 'Meeting not found';
            return;
        }

        $this->render('app/meetings/show', [
            'meeting' => $meeting,
            'decisions' => Meeting::findDecisions($meetingId),
            'actionItems' => $this->findRelatedActionItems($meetingId),
        ]);
    }

    private function findRelatedActionItems(int $meetingId): array
    {
        try {
            return ActionItem::findBySource('meeting', $meetingId);
        } catch (PDOException) {
            return [];
        }
    }
}
