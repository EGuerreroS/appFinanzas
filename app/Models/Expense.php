<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $fillable = [
        'user_id',
        'category_id',
        'amount',
        'description',
        'date',
    ];

    protected $casts = [
        'amount' => 'float',
        'date' => 'date:Y-m-d',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
