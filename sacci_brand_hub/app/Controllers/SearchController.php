<?php

namespace App\Controllers;

use App\Models\ActionItem;
use App\Models\Document;
use App\Models\Meeting;
use App\Models\Report;
use PDOException;

class SearchController extends BaseController
{
    public function index(): void
    {
        $this->requireLogin();

        $query = trim((string) ($_GET['q'] ?? ''));

        if ($query === '') {
            $this->render('app/search/index', [
                'query' => '',
                'results' => [],
            ]);
            return;
        }

        try {
            $this->render('app/search/index', [
                'query' => $query,
                'results' => [
                    'meetings' => Meeting::searchByTerm($query),
                    'actions' => ActionItem::searchByTerm($query),
                    'reports' => Report::searchByTerm($query),
                    'documents' => Document::searchByTerm($query),
                ],
            ]);
        } catch (PDOException) {
            $this->render('app/search/index', [
                'query' => $query,
                'results' => [],
                'setupRequired' => true,
            ]);
        }
    }
}
