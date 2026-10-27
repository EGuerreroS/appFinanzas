<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Budget extends Model
{
    protected $fillable = [
        'user_id',
        'category_id',
        'amount',
        'month',
        'year',
    ];

    protected $casts = [
        'amount' => 'float',
        'month' => 'integer',
        'year' => 'integer',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
