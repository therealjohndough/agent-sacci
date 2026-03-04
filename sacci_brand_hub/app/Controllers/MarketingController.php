<?php

namespace App\Controllers;

use Core\Auth;
use Core\Csrf;
use Core\Database;
use PDOException;

class MarketingController extends BaseController
{
    public const REQUEST_TYPES = [
        'social_content' => 'Social Content',
        'sell_sheet'     => 'Sell Sheet',
        'photography'    => 'Photography',
        'packaging'      => 'Packaging',
        'event'          => 'Event',
        'email'          => 'Email Campaign',
        'video'          => 'Video',
        'other'          => 'Other',
    ];

    public const PRIORITIES = ['low', 'normal', 'high', 'urgent'];

    public function index(): void
    {
        $this->requireLogin();
        $pdo = Database::getConnection();

        try {
            $stmt = $pdo->query(
                "SELECT t.id, t.title, t.request_type, t.priority, t.status,
                        t.due_date, t.linked_strain_id, t.linked_product_id,
                        s.name AS strain_name, p.product_name,
                        u.name AS assignee_name
                 FROM tickets t
                 LEFT JOIN strains s ON s.id = t.linked_strain_id
                 LEFT JOIN products p ON p.id = t.linked_product_id
                 LEFT JOIN users u ON u.id = t.assigned_to
                 WHERE t.request_type IS NOT NULL
                 ORDER BY
                     FIELD(t.priority, 'urgent','high','normal','low'),
                     t.due_date ASC,
                     t.id DESC"
            );
            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (PDOException) {
            $rows = [];
        }

        // Map existing ticket status ENUM values to display buckets
        $statusMap = [
            'New'         => 'open',
            'Triaged'     => 'open',
            'open'        => 'open',
            'In Progress' => 'in-progress',
            'Review'      => 'in-progress',
            'in-progress' => 'in-progress',
            'Done'        => 'done',
            'done'        => 'done',
        ];

        $grouped = ['open' => [], 'in-progress' => [], 'done' => [], 'other' => []];
        foreach ($rows as $r) {
            $rawStatus = $r['status'] ?? '';
            $bucket    = $statusMap[$rawStatus] ?? 'other';
            $grouped[$bucket][] = $r;
        }

        $this->render('app/marketing/index', [
            'grouped'      => $grouped,
            'requestTypes' => self::REQUEST_TYPES,
        ]);
    }

    public function requestForm(): void
    {
        $this->requireLogin();
        $pdo = Database::getConnection();

        try {
            $strains  = $pdo->query('SELECT id, name FROM strains WHERE status = \'active\' ORDER BY name')
                            ->fetchAll(\PDO::FETCH_ASSOC);
            $products = $pdo->query('SELECT id, product_name FROM products ORDER BY product_name')
                            ->fetchAll(\PDO::FETCH_ASSOC);
            $users    = $pdo->query('SELECT id, name FROM users ORDER BY name')
                            ->fetchAll(\PDO::FETCH_ASSOC);
        } catch (PDOException) {
            $strains = $products = $users = [];
        }

        $this->render('app/marketing/request', [
            'csrf'         => $this->csrfToken(),
            'requestTypes' => self::REQUEST_TYPES,
            'priorities'   => self::PRIORITIES,
            'strains'      => $strains,
            'products'     => $products,
            'users'        => $users,
        ]);
    }

    public function submitRequest(): void
    {
        $this->requireLogin();

        $token = (string) ($_POST['_csrf'] ?? '');
        if (!Csrf::validate($token)) {
            die('Invalid CSRF token');
        }

        $requestType = trim($_POST['request_type'] ?? '');
        $title       = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $priority    = trim($_POST['priority'] ?? 'normal');
        $dueDate     = trim($_POST['due_date'] ?? '') ?: null;
        $strainId    = (int) ($_POST['linked_strain_id'] ?? 0) ?: null;
        $productId   = (int) ($_POST['linked_product_id'] ?? 0) ?: null;
        $assignTo    = (int) ($_POST['assigned_to'] ?? 0) ?: null;

        if (!isset(self::REQUEST_TYPES[$requestType])) {
            $requestType = 'other';
        }
        if (!in_array($priority, self::PRIORITIES, true)) {
            $priority = 'normal';
        }
        if ($title === '') {
            $title = self::REQUEST_TYPES[$requestType] . ' Request';
        }

        $user = Auth::user();
        $data = [
            'title'        => $title,
            'request_type' => $requestType,
            'description'  => $description !== '' ? $description : null,
            'priority'     => $priority,
            'status'       => 'New',
            'requester_id' => $user['id'],
        ];
        if ($dueDate) {
            $data['due_date'] = $dueDate;
        }
        if ($strainId) {
            $data['linked_strain_id'] = $strainId;
        }
        if ($productId) {
            $data['linked_product_id'] = $productId;
        }
        if ($assignTo) {
            $data['assigned_to'] = $assignTo;
        }

        try {
            \App\Models\Ticket::create($data);
        } catch (\Exception $e) {
            // Re-render form with error
            $pdo = Database::getConnection();
            try {
                $strains  = $pdo->query('SELECT id, name FROM strains WHERE status = \'active\' ORDER BY name')->fetchAll(\PDO::FETCH_ASSOC);
                $products = $pdo->query('SELECT id, product_name FROM products ORDER BY product_name')->fetchAll(\PDO::FETCH_ASSOC);
                $users    = $pdo->query('SELECT id, name FROM users ORDER BY name')->fetchAll(\PDO::FETCH_ASSOC);
            } catch (PDOException) {
                $strains = $products = $users = [];
            }
            $this->render('app/marketing/request', [
                'csrf'         => $this->csrfToken(),
                'requestTypes' => self::REQUEST_TYPES,
                'priorities'   => self::PRIORITIES,
                'strains'      => $strains,
                'products'     => $products,
                'users'        => $users,
                'error'        => 'Could not save request: ' . htmlspecialchars($e->getMessage()),
            ]);
            return;
        }

        $this->redirect('/marketing');
    }
}
