<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategoryRequest;
use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        return Inertia::render('Categories/Index', [
            'categories' => $request->user()->categories()
                ->withCount('expenses')
                ->orderBy('name')
                ->get(),
            'groups' => Category::GROUPS,
        ]);
    }

    public function store(CategoryRequest $request)
    {
        $request->user()->categories()->create($request->validated());

        return back()->with('success', 'Categoría creada');
    }

    public function update(CategoryRequest $request, Category $category)
    {
        $this->ensureOwner($category);

        $category->update($request->validated());

        return back()->with('success', 'Categoría actualizada');
    }

    public function destroy(Category $category)
    {
        $this->ensureOwner($category);

        if ($category->expenses()->exists()) {
            return back()->with('error', 'No se puede borrar una categoría con gastos registrados.');
        }

        $category->delete();

        return back()->with('success', 'Categoría eliminada');
    }
}
