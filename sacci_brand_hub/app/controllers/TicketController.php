<?php

namespace App\Controllers;

use App\Models\Ticket;
use Core\Auth;

class TicketController extends BaseController
{
    public function index(): void
    {
        $this->requireLogin();
        $user = Auth::user();
        // For now, show tickets assigned to user or created by user
        $tickets = Ticket::findByAssignee($user['id']);
        $this->render('app/tickets/index', [
            'tickets' => $tickets,
            'user' => $user,
            'csrf' => $this->csrfToken(),
        ]);
    }

    public function show(): void
    {
        $this->requireLogin();
        $id = (int)($_GET['id'] ?? 0);
        $ticket = Ticket::find($id);
        if (!$ticket) {
            http_response_code(404);
            echo 'Ticket not found';
            return;
        }
        $this->render('app/tickets/show', [
            'ticket' => $ticket,
        ]);
    }
}