<?php

namespace App\Controllers;

use App\Models\Department;
use PDOException;

class DepartmentController extends BaseController
{
    public function index(): void
    {
        $this->requireLogin();

        try {
            $this->render('app/departments/index', [
                'departments' => Department::findAllOrdered(),
            ]);
        } catch (PDOException) {
            $this->render('app/departments/index', [
                'departments' => [],
                'setupRequired' => true,
            ]);
        }
    }
}
