<?php

namespace App\Controllers;

use Core\Auth;
use Core\Csrf;
use Core\Database;
use PDOException;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as MailerException;

class AdminController extends BaseController
{
    /**
     * GET /admin/test-mail
     *
     * Sends a test email to the logged-in admin's address using the configured
     * SMTP credentials. Returns a JSON response indicating success or failure.
     *
     * Requires: authenticated session + content.manage permission (admin or super_admin).
     */
    public function testMail(): void
    {
        header('Content-Type: application/json');

        if (!Auth::check()) {
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => 'Not authenticated']);
            return;
        }

        if (!Auth::hasPermission('content.manage')) {
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => 'Forbidden — admin or super_admin role required']);
            return;
        }

        $user = Auth::user();
        $toEmail = $user['email'] ?? '';

        if ($toEmail === '') {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Could not determine logged-in user email']);
            return;
        }

        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = \Config\env('MAIL_HOST', 'localhost');
            $mail->Port       = (int) \Config\env('MAIL_PORT', 587);
            $mail->SMTPAuth   = true;
            $mail->Username   = \Config\env('MAIL_USERNAME', '');
            $mail->Password   = \Config\env('MAIL_PASSWORD', '');
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;

            $fromAddress = \Config\env('MAIL_FROM', \Config\env('MAIL_USERNAME', ''));
            $mail->setFrom($fromAddress, \Config\env('APP_NAME', 'Sacci Brand Hub'));
            $mail->addAddress($toEmail);

            $mail->Subject = 'Sacci Brand Hub — SMTP test';
            $mail->Body    = 'This is a test email sent from the Sacci Brand Hub admin panel. If you received this, your SMTP configuration is working correctly.';

            $mail->send();

            echo json_encode(['status' => 'ok', 'message' => 'Test email sent to ' . $toEmail]);
        } catch (MailerException $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $mail->ErrorInfo]);
        }
    }

    // -----------------------------------------------------------------------
    // User management
    // -----------------------------------------------------------------------

    /**
     * GET /admin/users
     * Lists all users with their assigned roles.
     */
    public function users(): void
    {
        $this->requireLogin();
        if (!Auth::hasPermission('content.manage')) {
            http_response_code(403);
            echo '403 Forbidden';
            return;
        }

        try {
            $pdo = Database::getConnection();

            $users = $pdo->query(
                "SELECT u.id, u.name, u.email, u.organization_id,
                        GROUP_CONCAT(r.name ORDER BY r.name SEPARATOR ', ') AS role_names
                 FROM users u
                 LEFT JOIN user_roles ur ON ur.user_id = u.id
                 LEFT JOIN roles r ON r.id = ur.role_id
                 GROUP BY u.id
                 ORDER BY u.name"
            )->fetchAll(\PDO::FETCH_ASSOC);
        } catch (PDOException) {
            $users = [];
        }

        $this->render('admin/users', ['users' => $users]);
    }

    /**
     * GET /admin/users/roles
     * Role assignment form for a single user.
     */
    public function editUserRoles(): void
    {
        $this->requireLogin();
        if (!Auth::hasPermission('content.manage')) {
            http_response_code(403);
            echo '403 Forbidden';
            return;
        }

        $userId   = (int) ($_GET['id'] ?? 0);
        $pdo      = Database::getConnection();
        $userData = $this->fetchUser($pdo, $userId);

        if (!$userData) {
            http_response_code(404);
            echo 'User not found';
            return;
        }

        $allRoles = $pdo->query('SELECT id, name, description FROM roles ORDER BY name')->fetchAll(\PDO::FETCH_ASSOC);

        $stmt = $pdo->prepare('SELECT role_id FROM user_roles WHERE user_id = :uid');
        $stmt->execute(['uid' => $userId]);
        $currentRoleIds = array_map('intval', $stmt->fetchAll(\PDO::FETCH_COLUMN));

        $this->render('admin/user_roles', [
            'csrf'           => $this->csrfToken(),
            'userData'       => $userData,
            'allRoles'       => $allRoles,
            'currentRoleIds' => $currentRoleIds,
        ]);
    }

    /**
     * POST /admin/users/roles
     * Save role assignments for a user.
     */
    public function updateUserRoles(): void
    {
        $this->requireLogin();
        if (!Auth::hasPermission('content.manage')) {
            http_response_code(403);
            echo '403 Forbidden';
            return;
        }

        $token = (string) ($_POST['_csrf'] ?? '');
        if (!Csrf::validate($token)) {
            die('Invalid CSRF token');
        }

        $userId      = (int) ($_POST['user_id'] ?? 0);
        $selectedIds = array_map('intval', (array) ($_POST['roles'] ?? []));

        if ($userId <= 0) {
            $this->redirect('/admin/users');
            return;
        }

        $pdo = Database::getConnection();

        // Delete existing, then insert selected
        $pdo->prepare('DELETE FROM user_roles WHERE user_id = :uid')->execute(['uid' => $userId]);

        if (!empty($selectedIds)) {
            $insert = $pdo->prepare('INSERT IGNORE INTO user_roles (user_id, role_id) VALUES (:uid, :rid)');
            foreach ($selectedIds as $roleId) {
                if ($roleId > 0) {
                    $insert->execute(['uid' => $userId, 'rid' => $roleId]);
                }
            }
        }

        $this->redirect('/admin/users');
    }

    private function fetchUser(\PDO $pdo, int $id): ?array
    {
        try {
            $stmt = $pdo->prepare('SELECT id, name, email FROM users WHERE id = :id LIMIT 1');
            $stmt->execute(['id' => $id]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $row ?: null;
        } catch (PDOException) {
            return null;
        }
    }
}
