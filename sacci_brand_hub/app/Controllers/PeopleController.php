<?php

namespace App\Controllers;

use Core\Database;
use PDOException;

class PeopleController extends BaseController
{
    public function index(): void
    {
        $this->requireLogin();

        try {
            $stmt = Database::getConnection()->query(
                "SELECT u.id, u.name, u.email, u.job_title, u.profile_summary,
                        GROUP_CONCAT(d.name ORDER BY uda.assignment_type SEPARATOR ', ') AS departments
                 FROM users u
                 LEFT JOIN user_department_assignments uda ON uda.user_id = u.id
                 LEFT JOIN departments d ON d.id = uda.department_id
                 WHERE u.is_active = 1
                 GROUP BY u.id
                 ORDER BY u.name"
            );
            $people = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (PDOException) {
            $people = [];
        }

        $this->render('app/people/index', ['people' => $people]);
    }
}
