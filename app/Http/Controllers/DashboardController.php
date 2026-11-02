<?php

namespace App\Http\Controllers;

use App\Services\BudgetPlanService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(
        private BudgetPlanService $budgetPlanService
    ) {}

    public function index(Request $request): Response
    {
        $year = $request->has('year') && is_numeric($request->input('year'))
            ? (int) $request->input('year')
            : null;

        $month = $request->has('month') && is_numeric($request->input('month'))
            ? (int) $request->input('month')
            : null;

        $data = $this->budgetPlanService->getDashboardData($year, $month);

        return Inertia::render('Dashboard', $data);
    }
}
