<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UlasanPermohonanResponse extends Model
{
    protected $fillable = [
        'question_id',
        'permohonan_id',
        'user_id',
        'rating_value',
        'text_answer',
    ];

    public function question()
    {
        return $this->belongsTo(UlasanPermohonanQuestion::class, 'question_id');
    }

    public function permohonan()
    {
        return $this->belongsTo(Permohonan::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
