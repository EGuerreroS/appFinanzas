<?php

namespace App\Services;

use App\Models\Budget;
use App\Models\Category;
use App\Models\Expense;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class FinanceService
{
    /**
     * Primer y último día del mes indicado.
     *
     * @return array{0: string, 1: string}
     */
    public function monthRange(int $month, int $year): array
    {
        $start = CarbonImmutable::create($year, $month, 1);

        return [$start->toDateString(), $start->endOfMonth()->toDateString()];
    }

    /**
     * Gasto total por categoría en un rango de fechas: [category_id => total].
     */
    public function spentByCategory(User $user, string $from, string $to): Collection
    {
        return $user->expenses()
            ->whereBetween('date', [$from, $to])
            ->selectRaw('category_id, SUM(amount) as total')
            ->groupBy('category_id')
            ->pluck('total', 'category_id')
            ->map(fn ($total) => round((float) $total, 2));
    }

    /**
     * Presupuestos del mes con lo gastado y el restante de cada uno.
     */
    public function budgetStatus(User $user, int $month, int $year): Collection
    {
        [$from, $to] = $this->monthRange($month, $year);
        $spent = $this->spentByCategory($user, $from, $to);

        return $user->budgets()
            ->with('category:id,name,group,color')
            ->where('month', $month)
            ->where('year', $year)
            ->get()
            ->sortBy('category.name')
            ->values()
            ->map(function (Budget $budget) use ($spent) {
                $spentAmount = $spent->get($budget->category_id, 0.0);

                return [
                    'id' => $budget->id,
                    'category_id' => $budget->category_id,
                    'category' => $budget->category,
                    'amount' => $budget->amount,
                    'month' => $budget->month,
                    'year' => $budget->year,
                    'spent' => $spentAmount,
                    'remaining' => round($budget->amount - $spentAmount, 2),
                    'percent' => $budget->amount > 0 ? round($spentAmount / $budget->amount * 100, 1) : null,
                    'over' => $spentAmount > $budget->amount,
                ];
            });
    }

    /**
     * Si el gasto hace que su categoría rebase el presupuesto del mes, devuelve el mensaje de alerta.
     */
    public function overBudgetWarning(Expense $expense): ?string
    {
        $date = CarbonImmutable::parse($expense->date);
        $budget = Budget::where('category_id', $expense->category_id)
            ->where('month', $date->month)
            ->where('year', $date->year)
            ->first();

        if (! $budget) {
            return null;
        }

        [$from, $to] = $this->monthRange($date->month, $date->year);
        $spent = (float) Expense::where('category_id', $expense->category_id)
            ->whereBetween('date', [$from, $to])
            ->sum('amount');

        if ($spent <= $budget->amount) {
            return null;
        }

        $excess = number_format($spent - $budget->amount, 2);
        $category = $expense->category()->value('name');

        return "Rebasaste el presupuesto de \"{$category}\" por \${$excess} MXN este mes.";
    }

    /**
     * Datos agregados para el dashboard del mes indicado.
     */
    public function dashboard(User $user, int $month, int $year): array
    {
        [$from, $to] = $this->monthRange($month, $year);

        $incomeMonth = round((float) $user->incomes()->whereBetween('date', [$from, $to])->sum('amount'), 2);
        $expenseMonth = round((float) $user->expenses()->whereBetween('date', [$from, $to])->sum('amount'), 2);
        $incomeTotal = (float) $user->incomes()->sum('amount');
        $expenseTotal = (float) $user->expenses()->sum('amount');

        $categories = $user->categories()->orderBy('name')->get(['id', 'name', 'group', 'color']);
        $spent = $this->spentByCategory($user, $from, $to);

        $byCategory = $categories
            ->filter(fn (Category $c) => $spent->has($c->id))
            ->map(fn (Category $c) => [
                'id' => $c->id,
                'name' => $c->name,
                'color' => $c->color,
                'group' => $c->group,
                'total' => $spent->get($c->id),
            ])
            ->sortByDesc('total')
            ->values();

        // Gasto por grupo (esencial / no esencial / ahorro) para comparar contra el preset
        $byGroup = collect(Category::GROUPS)->map(fn ($label, $group) => [
            'group' => $group,
            'label' => $label,
            'total' => round($byCategory->where('group', $group)->sum('total'), 2),
        ])->values();

        return [
            'summary' => [
                'income_month' => $incomeMonth,
                'expense_month' => $expenseMonth,
                'balance_month' => round($incomeMonth - $expenseMonth, 2),
                'balance_total' => round($incomeTotal - $expenseTotal, 2),
            ],
            'byCategory' => $byCategory,
            'byGroup' => $byGroup,
            'budgets' => $this->budgetStatus($user, $month, $year),
        ];
    }
}
