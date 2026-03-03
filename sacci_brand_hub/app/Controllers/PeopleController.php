<?php

namespace App\Controllers;

use App\Models\Setting;
use PDOException;

class PeopleController extends BaseController
{
    public function index(): void
    {
        $this->requireLogin();

        try {
            $this->render('app/people/index', [
                'people' => Setting::getJson('team_directory_seed_v1'),
            ]);
        } catch (PDOException) {
            $this->render('app/people/index', [
                'people' => [],
                'setupRequired' => true,
            ]);
        }
    }
}
