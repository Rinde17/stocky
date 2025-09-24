<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Services\DashboardService; // NOUVEAU : Import du DashboardService
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    protected DashboardService $dashboardService;

    // Injection du DashboardService via le constructeur
    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    public function index(): Response
    {
        // Récupère l'utilisateur actuellement authentifié
        $user = $this->user();

        // Délègue la récupération de toutes les données du tableau de bord au DashboardService
        $dashboardData = $this->dashboardService->getDashboardData($user);

        return Inertia::render('Dashboard', $dashboardData);
    }
}
