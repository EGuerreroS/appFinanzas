<?php

namespace App\Http\Controllers;

use App\Http\Requests\BudgetRequest;
use App\Models\Budget;
use App\Services\FinanceService;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BudgetController extends Controller
{
    public function __construct(private FinanceService $financeService)
    {
    }

    public function index(Request $request)
    {
        [$month, $year] = $this->period($request);
        $user = $request->user();

        return Inertia::render('Budgets/Index', [
            'budgets' => $this->financeService->budgetStatus($user, $month, $year),
            'categories' => $user->categories()->orderBy('name')->get(['id', 'name', 'group', 'color']),
            'month' => $month,
            'year' => $year,
        ]);
    }

    // Crea o reemplaza el presupuesto de la categoría para ese mes
    public function store(BudgetRequest $request)
    {
        $data = $request->validated();

        $request->user()->budgets()->updateOrCreate(
            ['category_id' => $data['category_id'], 'month' => $data['month'], 'year' => $data['year']],
            ['amount' => $data['amount']],
        );

        return back()->with('success', 'Presupuesto guardado');
    }

    public function update(Request $request, Budget $budget)
    {
        $this->ensureOwner($budget);

        $budget->update($request->validate([
            'amount' => ['required', 'numeric', 'min:0', 'max:9999999999'],
        ]));

        return back()->with('success', 'Presupuesto actualizado');
    }

    public function destroy(Budget $budget)
    {
        $this->ensureOwner($budget);

        $budget->delete();

        return back()->with('success', 'Presupuesto eliminado');
    }

    // Copia los presupuestos del mes anterior a las categorías que aún no tienen presupuesto este mes
    public function copyPrevious(Request $request)
    {
        [$month, $year] = $this->period($request);
        $user = $request->user();
        $previous = CarbonImmutable::create($year, $month, 1)->subMonth();

        $existing = $user->budgets()->where('month', $month)->where('year', $year)->pluck('category_id');
        $toCopy = $user->budgets()
            ->where('month', $previous->month)
            ->where('year', $previous->year)
            ->whereNotIn('category_id', $existing)
            ->get();

        foreach ($toCopy as $budget) {
            $user->budgets()->create([
                'category_id' => $budget->category_id,
                'amount' => $budget->amount,
                'month' => $month,
                'year' => $year,
            ]);
        }

        return back()->with('success', "Se copiaron {$toCopy->count()} presupuestos del mes anterior");
    }
}
