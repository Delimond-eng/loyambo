<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable, HasRoles;

    // Optionnel mais conseillé pour éviter les soucis de guard
    protected $guard_name = 'web';

    protected $fillable = [
        'name', 'email', 'password', 'actif', 'salaire','role', 'ets_id','emplacement_id'
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'actif' => 'boolean',
        'salaire' => 'decimal:2'    ,
    ];

    public function etablissement()
    {
        return $this->belongsTo(Etablissement::class, "ets_id");
    }
}
