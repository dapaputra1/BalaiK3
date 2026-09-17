<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SuketK3Comment extends Model
{
    use HasFactory;

    protected $table = 'suket_k3_comments';

    protected $fillable = [
        'suket_id',
        'user_id',
        'target',
        'bagian',
        'highlight_text',
        'tipe',
        'document_type',
        'comment',
    ];

    public function suket(): BelongsTo
    {
        return $this->belongsTo(SuketK3::class, 'suket_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
