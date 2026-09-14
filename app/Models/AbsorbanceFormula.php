<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AbsorbanceFormula extends Model
{
    protected $fillable = [
        'parameter_key',
        'intercept',
        'slope',
        'effective_date',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'intercept' => 'decimal:12',
        'slope' => 'decimal:12',
        'effective_date' => 'date',
        'is_active' => 'boolean',
    ];
}
