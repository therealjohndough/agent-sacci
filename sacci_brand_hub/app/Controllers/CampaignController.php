<?php

namespace App\Controllers;

use Core\Auth;
use Core\Csrf;
use Core\Database;
use PDOException;

class CampaignController extends BaseController
{
    private const STATUSES = ['draft', 'active', 'completed', 'archived'];

    public function index(): void
    {
        $this->requireLogin();
        $pdo = Database::getConnection();

        $id = (int) ($_GET['id'] ?? 0);
        if ($id > 0) {
            $this->show($id);
            return;
        }

        try {
            $campaigns = $pdo->query(
                "SELECT c.id, c.name, c.status, c.start_date, c.end_date,
                        u.name AS owner_name,
                        COUNT(ct.ticket_id) AS ticket_count
                 FROM campaigns c
                 LEFT JOIN users u ON u.id = c.owner_id
                 LEFT JOIN campaign_tickets ct ON ct.campaign_id = c.id
                 WHERE c.status != 'archived'
                 GROUP BY c.id
                 ORDER BY FIELD(c.status,'active','draft','completed'), c.start_date DESC"
            )->fetchAll(\PDO::FETCH_ASSOC);
        } catch (PDOException) {
            $campaigns = [];
        }

        $this->render('app/campaigns/index', [
            'campaigns' => $campaigns,
            'csrf'      => $this->csrfToken(),
        ]);
    }

    public function create(): void
    {
        $this->requireLogin();
        $this->render('app/campaigns/form', [
            'csrf'     => $this->csrfToken(),
            'statuses' => self::STATUSES,
            'users'    => $this->loadUsers(),
            'campaign' => null,
        ]);
    }

    public function store(): void
    {
        $this->requireLogin();

        $token = (string) ($_POST['_csrf'] ?? '');
        if (!Csrf::validate($token)) {
            die('Invalid CSRF token');
        }

        $data = $this->postValues();

        if ($data['name'] === '') {
            $this->renderForm(null, $data, 'Campaign name is required.');
            return;
        }

        try {
            $pdo = Database::getConnection();
            $pdo->prepare(
                'INSERT INTO campaigns (name, description, status, start_date, end_date, owner_id)
                 VALUES (:name, :description, :status, :start_date, :end_date, :owner_id)'
            )->execute($data);
            $id = (int) $pdo->lastInsertId();
        } catch (\Exception $e) {
            $this->renderForm(null, $data, 'Could not save: ' . htmlspecialchars($e->getMessage()));
            return;
        }

        $this->redirect('/campaigns?id=' . $id);
    }

    public function edit(): void
    {
        $this->requireLogin();
        $id       = (int) ($_GET['id'] ?? 0);
        $campaign = $this->findCampaign($id);

        if (!$campaign) {
            http_response_code(404);
            echo 'Campaign not found';
            return;
        }

        $this->renderForm($campaign, $campaign, null);
    }

    public function update(): void
    {
        $this->requireLogin();

        $token = (string) ($_POST['_csrf'] ?? '');
        if (!Csrf::validate($token)) {
            die('Invalid CSRF token');
        }

        $id       = (int) ($_POST['id'] ?? 0);
        $campaign = $this->findCampaign($id);

        if (!$campaign) {
            http_response_code(404);
            echo 'Campaign not found';
            return;
        }

        $data = $this->postValues();

        if ($data['name'] === '') {
            $this->renderForm($campaign, $data, 'Campaign name is required.');
            return;
        }

        try {
            Database::getConnection()->prepare(
                'UPDATE campaigns
                 SET name=:name, description=:description, status=:status,
                     start_date=:start_date, end_date=:end_date, owner_id=:owner_id
                 WHERE id=:id'
            )->execute(array_merge($data, ['id' => $id]));
        } catch (\Exception $e) {
            $this->renderForm($campaign, $data, 'Could not update: ' . htmlspecialchars($e->getMessage()));
            return;
        }

        $this->redirect('/campaigns?id=' . $id);
    }

    public function archive(): void
    {
        $this->requireLogin();

        $token = (string) ($_POST['_csrf'] ?? '');
        if (!Csrf::validate($token)) {
            die('Invalid CSRF token');
        }

        $id = (int) ($_POST['id'] ?? 0);
        if ($id > 0) {
            Database::getConnection()
                ->prepare("UPDATE campaigns SET status='archived' WHERE id=:id")
                ->execute(['id' => $id]);
        }

        $this->redirect('/campaigns');
    }

    // -----------------------------------------------------------------------

    private function show(int $id): void
    {
        $campaign = $this->findCampaign($id);

        if (!$campaign) {
            http_response_code(404);
            echo 'Campaign not found';
            return;
        }

        // Load linked marketing request tickets
        try {
            $tickets = Database::getConnection()->prepare(
                "SELECT t.id, t.title, t.request_type, t.priority, t.status,
                        t.due_date, u.name AS assignee_name
                 FROM campaign_tickets ct
                 JOIN tickets t ON t.id = ct.ticket_id
                 LEFT JOIN users u ON u.id = t.assigned_to
                 WHERE ct.campaign_id = :id
                 ORDER BY FIELD(t.priority,'urgent','high','normal','low'), t.due_date"
            );
            $tickets->execute(['id' => $id]);
            $tickets = $tickets->fetchAll(\PDO::FETCH_ASSOC);
        } catch (PDOException) {
            $tickets = [];
        }

        $this->render('app/campaigns/show', [
            'campaign' => $campaign,
            'tickets'  => $tickets,
            'csrf'     => $this->csrfToken(),
        ]);
    }

    private function findCampaign(int $id): ?array
    {
        try {
            $stmt = Database::getConnection()->prepare(
                'SELECT c.*, u.name AS owner_name
                 FROM campaigns c
                 LEFT JOIN users u ON u.id = c.owner_id
                 WHERE c.id = :id LIMIT 1'
            );
            $stmt->execute(['id' => $id]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $row ?: null;
        } catch (PDOException) {
            return null;
        }
    }

    private function postValues(): array
    {
        return [
            'name'        => trim($_POST['name'] ?? ''),
            'description' => trim($_POST['description'] ?? '') ?: null,
            'status'      => in_array($_POST['status'] ?? '', self::STATUSES, true) ? $_POST['status'] : 'draft',
            'start_date'  => trim($_POST['start_date'] ?? '') ?: null,
            'end_date'    => trim($_POST['end_date'] ?? '') ?: null,
            'owner_id'    => (int) ($_POST['owner_id'] ?? 0) ?: null,
        ];
    }

    private function renderForm(?array $campaign, array $values, ?string $error): void
    {
        $this->render('app/campaigns/form', [
            'csrf'     => $this->csrfToken(),
            'statuses' => self::STATUSES,
            'users'    => $this->loadUsers(),
            'campaign' => $campaign,
            'values'   => $values,
            'error'    => $error,
        ]);
    }

    private function loadUsers(): array
    {
        try {
            return Database::getConnection()
                ->query('SELECT id, name FROM users ORDER BY name')
                ->fetchAll(\PDO::FETCH_ASSOC);
        } catch (PDOException) {
            return [];
        }
    }
}
