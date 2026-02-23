<?php

namespace App\Controllers;

use App\Models\Ticket;
use Core\Auth;

class DashboardController extends BaseController
{
    public function index(): void
    {
        $this->requireLogin();
        $user = Auth::user();
        $tickets = Ticket::findByAssignee($user['id']);
        $this->render('app/dashboard', [
            'user' => $user,
            'tickets' => $tickets,
            'csrf' => $this->csrfToken(),
        ]);
    }
}