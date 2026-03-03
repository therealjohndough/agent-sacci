<?php

namespace App\Controllers;

use App\Models\Document;
use PDOException;

class DocumentController extends BaseController
{
    public function index(): void
    {
        $this->requireLogin();

        $documentId = (int) ($_GET['id'] ?? 0);

        try {
            if ($documentId > 0) {
                $this->show($documentId);
                return;
            }

            $this->render('app/documents/index', [
                'documents' => Document::findAllWithRelations(),
            ]);
        } catch (PDOException) {
            $this->render('app/documents/index', [
                'documents' => [],
                'setupRequired' => true,
            ]);
        }
    }

    private function show(int $documentId): void
    {
        $document = Document::findWithRelations($documentId);
        if (!$document) {
            http_response_code(404);
            echo 'Document not found';
            return;
        }

        $this->render('app/documents/show', [
            'document' => $document,
        ]);
    }
}
