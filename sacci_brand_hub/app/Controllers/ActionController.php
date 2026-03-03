<?php

namespace App\Controllers;

use App\Models\ActionItem;
use PDOException;

class ActionController extends BaseController
{
    public function index(): void
    {
        $this->requireLogin();

        try {
            $this->render('app/actions/index', [
                'actionItems' => ActionItem::findAllWithRelations(),
            ]);
        } catch (PDOException) {
            $this->render('app/actions/index', [
                'actionItems' => [],
                'setupRequired' => true,
            ]);
        }
    }
}
