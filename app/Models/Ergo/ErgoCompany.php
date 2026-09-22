<?php

namespace App\Models\Ergo;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErgoCompany extends Model
{
    use HasFactory;
    protected $table = 'ergo_companies';
    protected $guarded = ['id'];

    public function assessments()
    {
        return $this->hasMany(ErgoAssessment::class, 'company_id');
    }
}