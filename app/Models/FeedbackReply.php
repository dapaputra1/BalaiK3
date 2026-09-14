<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeedbackReply extends Model
{
    protected $table = 'feedback_replies';

    protected $fillable = [
        'feedbacks_id',
        'user_id',
        'reply_message'
    ];

    public function feedback()
    {
        return $this->belongsTo(Feedback::class, 'feedbacks_id');
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
