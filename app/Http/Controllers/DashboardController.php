<?php

namespace App\Http\Controllers;

use App\Models\SavingPreset;
use App\Services\FinanceService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __construct(private FinanceService $financeService)
    {
    }

    public function __invoke(Request $request)
    {
        [$month, $year] = $this->period($request);
        $user = $request->user();

        $presets = SavingPreset::orderBy('id')->get();

        return Inertia::render('Dashboard', [
            ...$this->financeService->dashboard($user, $month, $year),
            'month' => $month,
            'year' => $year,
            'presets' => $presets,
            'presetId' => $user->saving_preset_id ?? $presets->first()?->id,
        ]);
    }
}
