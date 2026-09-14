<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengujianDokumenFile extends Model
{
    protected $table = 'pengujian_dokumen_files';

    protected $fillable = [
        'dokumen_id',
        'file_path',
        'original_name',
        'mime',
        'size',
        'uploaded_by',
    ];

    public function dokumen()
    {
        return $this->belongsTo(PengujianDokumen::class, 'dokumen_id');
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
