<?php

namespace App\Http\Controllers;

use App\Http\Requests\IncomeRequest;
use App\Models\Income;
use Illuminate\Http\Request;
use Inertia\Inertia;

class IncomeController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
        ]);
        $filters['from'] ??= now()->startOfMonth()->toDateString();
        $filters['to'] ??= now()->endOfMonth()->toDateString();

        return Inertia::render('Incomes/Index', [
            'incomes' => $request->user()->incomes()
                ->whereBetween('date', [$filters['from'], $filters['to']])
                ->orderByDesc('date')
                ->orderByDesc('id')
                ->get(),
            'filters' => $filters,
        ]);
    }

    public function store(IncomeRequest $request)
    {
        $request->user()->incomes()->create($request->validated());

        return back()->with('success', 'Ingreso registrado');
    }

    public function update(IncomeRequest $request, Income $income)
    {
        $this->ensureOwner($income);

        $income->update($request->validated());

        return back()->with('success', 'Ingreso actualizado');
    }

    public function destroy(Income $income)
    {
        $this->ensureOwner($income);

        $income->delete();

        return back()->with('success', 'Ingreso eliminado');
    }
}
