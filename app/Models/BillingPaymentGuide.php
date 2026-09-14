<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BillingPaymentGuide extends Model
{
    protected $fillable = [
        'file_name',
        'file_path',
        'uploaded_by',
    ];

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
