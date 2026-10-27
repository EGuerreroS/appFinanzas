<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SavingPreset extends Model
{
    protected $fillable = [
        'name',
        'essential_pct',
        'non_essential_pct',
        'savings_pct',
        'is_custom',
    ];

    protected $casts = [
        'essential_pct' => 'integer',
        'non_essential_pct' => 'integer',
        'savings_pct' => 'integer',
        'is_custom' => 'boolean',
    ];
}
