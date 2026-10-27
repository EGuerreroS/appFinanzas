<?php

namespace App\Http\Controllers;

use App\Models\SavingPreset;
use Illuminate\Http\Request;

class SavingPresetController extends Controller
{
    // Selecciona la metodología de ahorro con la que se compara el dashboard
    public function select(Request $request)
    {
        $data = $request->validate([
            'saving_preset_id' => ['required', 'exists:saving_presets,id'],
        ]);

        $request->user()->update($data);

        return back();
    }

    // Solo el preset "Personalizado" es editable
    public function update(Request $request, SavingPreset $savingPreset)
    {
        abort_unless($savingPreset->is_custom, 403);

        $data = $request->validate([
            'essential_pct' => ['required', 'integer', 'between:0,100'],
            'non_essential_pct' => ['required', 'integer', 'between:0,100'],
            'savings_pct' => ['required', 'integer', 'between:0,100'],
        ]);

        if (array_sum($data) !== 100) {
            return back()->withErrors(['essential_pct' => 'Los porcentajes deben sumar 100.']);
        }

        $savingPreset->update($data);

        return back()->with('success', 'Preset personalizado actualizado');
    }
}
