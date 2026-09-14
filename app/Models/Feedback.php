<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    protected $table = 'feedbacks'; // ← tambahkan ini

    protected $fillable = [
        'user_id',
        'rating',
        'message'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reply()
    {
        return $this->hasOne(FeedbackReply::class, 'feedbacks_id');
    }
}
