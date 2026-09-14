<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Notifications\ResetPasswordNotification;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
        'signature_path',
        'nip',
        'jabatan',
        'golongan',
    ];

    protected $hidden = [
        'password',
    ];

    public function permohonanAssignments()
    {
        return $this->hasMany(PermohonanAssignment::class);
    }

    public function ulasanResponses()
    {
        return $this->hasMany(UlasanPermohonanResponse::class);
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($token));
    }
}
