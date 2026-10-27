<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

abstract class Controller
{
    // Evita que un usuario modifique registros de otro usuario
    protected function ensureOwner(Model $model): void
    {
        abort_unless((int) $model->user_id === (int) auth()->id(), 403);
    }

    /**
     * Mes y año a consultar desde el query string (por defecto el mes actual).
     *
     * @return array{0: int, 1: int}
     */
    protected function period(Request $request): array
    {
        $month = (int) $request->query('month', now()->month);
        $year = (int) $request->query('year', now()->year);

        if ($month < 1 || $month > 12 || $year < 2000 || $year > 2100) {
            return [now()->month, now()->year];
        }

        return [$month, $year];
    }
}
