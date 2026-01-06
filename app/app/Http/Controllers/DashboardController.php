<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Credential;
use App\Services\Dashboard\DashboardService;

class DashboardController extends Controller
{
    public function __construct(
        private DashboardService $dashboardService
    ) {}

    public function index()
    {
        $data = $this->dashboardService->getDashboardData();

        // Additional data for quick action slide-overs
        $data['clients'] = Client::select('id', 'name')->orderBy('name')->get();
        $data['sites'] = Credential::getUniqueSites();

        return view('dashboard', $data);
    }
}
