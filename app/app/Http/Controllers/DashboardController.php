<?php

namespace App\Http\Controllers;

use App\Services\Dashboard\DashboardService;

class DashboardController extends Controller
{
    public function __construct(
        private DashboardService $dashboardService
    ) {}

    public function index()
    {
        // Quick actions now redirect to dedicated create pages,
        // so we no longer need to fetch form data (clients, sites, etc.) here.
        // This improves dashboard load performance.
        $data = $this->dashboardService->getDashboardData();

        return view('dashboard', $data);
    }
}
