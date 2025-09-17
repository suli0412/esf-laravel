<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

// Spatie-Package für Rollen & Berechtigungen
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    /**
     * Welcher Guard für dieses Modell genutzt wird.
     * Standardmäßig 'web'
     */
    protected $guard_name = 'web';

    /**
     * Mass-Assignment Schutz.
     * Wenn du alles freigeben willst: protected $guarded = [];
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * Felder, die versteckt werden sollen (z. B. in Arrays/JSON).
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Typ-Casts für bestimmte Felder.
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];
}
