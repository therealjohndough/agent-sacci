<?php

namespace App\Controllers;

use App\Models\Asset;
use Core\Auth;

class PortalController extends BaseController
{
    public function index(): void
    {
        $this->requireLogin();
        $user = Auth::user();
        // Filter assets by organization or public
        $assets = Asset::findBy([]); // TODO: filter by org and visibility
        $this->render('portal/dashboard', [
            'assets' => $assets,
        ]);
    }
}