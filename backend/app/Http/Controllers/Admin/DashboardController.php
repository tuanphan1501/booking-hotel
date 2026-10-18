<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminUser;
use App\Services\Dashboard\DashboardService;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Web Admin home screen (AD-02 Dashboard). Administrators land here right after signing in.
 *
 * @author PH.Tuan
 * @created 2026-10-18
 */
class DashboardController extends Controller
{
    /**
     * @param  DashboardService  $dashboardService  Computes KPIs, chart data and task counters.
     */
    public function __construct(private readonly DashboardService $dashboardService) {}

    /**
     * Render the home screen.
     *
     * - GET /admin
     * - Figures are limited to the administrator's hotel (head office sees the whole chain).
     *
     * @param  Request  $request  Authenticated request.
     *
     * @return View
     */
    public function index(Request $request): View
    {
        /** @var AdminUser $administrator */
        $administrator = $request->user('admin');

        return view('admin.dashboard', [
            'summary' => $this->dashboardService->summarize($administrator, CarbonImmutable::today()),
        ]);
    }
}
