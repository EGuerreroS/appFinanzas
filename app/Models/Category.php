<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    public const ESSENTIAL = 'essential';
    public const NON_ESSENTIAL = 'non_essential';
    public const SAVINGS = 'savings';

    public const GROUPS = [
        self::ESSENTIAL => 'Esencial',
        self::NON_ESSENTIAL => 'No esencial',
        self::SAVINGS => 'Ahorro',
    ];

    protected $fillable = [
        'user_id',
        'name',
        'group',
        'color',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function budgets()
    {
        return $this->hasMany(Budget::class);
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }
}
