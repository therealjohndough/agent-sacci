<?php

namespace App\Controllers;

use App\Models\ContentBlock;
use Core\Auth;

class ContentController extends BaseController
{
    public function index(): void
    {
        $this->requireLogin();
        // Only admins can manage content
        if (!Auth::hasPermission('content.manage')) {
            http_response_code(403);
            echo 'Forbidden';
            return;
        }
        $blocks = ContentBlock::findBy([]);
        $this->render('app/content/index', [
            'blocks' => $blocks,
        ]);
    }
}