<?php

namespace App\Controllers;

use Core\Auth;
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
}
