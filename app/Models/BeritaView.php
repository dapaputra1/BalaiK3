<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BeritaView extends Model
{
    protected $table = 'berita_views';

    protected $fillable = [
        'slug',
        'views_count',
    ];
}

