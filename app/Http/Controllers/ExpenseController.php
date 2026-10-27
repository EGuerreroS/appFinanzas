<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExpenseRequest;
use App\Models\Expense;
use App\Services\FinanceService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ExpenseController extends Controller
{
    public function __construct(private FinanceService $financeService)
    {
    }

    public function index(Request $request)
    {
        $filters = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
            'category_id' => ['nullable', 'integer'],
        ]);
        $filters['from'] ??= now()->startOfMonth()->toDateString();
        $filters['to'] ??= now()->endOfMonth()->toDateString();

        $user = $request->user();
        $expenses = $user->expenses()
            ->with('category:id,name,color,group')
            ->whereBetween('date', [$filters['from'], $filters['to']])
            ->when($filters['category_id'] ?? null, fn ($q, $id) => $q->where('category_id', $id))
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->get();

        return Inertia::render('Expenses/Index', [
            'expenses' => $expenses,
            'categories' => $user->categories()->orderBy('name')->get(['id', 'name', 'group', 'color']),
            'filters' => $filters,
        ]);
    }

    public function store(ExpenseRequest $request)
    {
        $expense = $request->user()->expenses()->create($request->validated());

        return $this->redirectWithBudgetCheck($expense, 'Gasto registrado');
    }

    public function update(ExpenseRequest $request, Expense $expense)
    {
        $this->ensureOwner($expense);

        $expense->update($request->validated());

        return $this->redirectWithBudgetCheck($expense, 'Gasto actualizado');
    }

    public function destroy(Expense $expense)
    {
        $this->ensureOwner($expense);

        $expense->delete();

        return back()->with('success', 'Gasto eliminado');
    }

    // Exceder un presupuesto no bloquea el registro, solo muestra una alerta
    private function redirectWithBudgetCheck(Expense $expense, string $message)
    {
        $warning = $this->financeService->overBudgetWarning($expense);

        return back()->with($warning ? 'warning' : 'success', $warning ?? $message);
    }
}
