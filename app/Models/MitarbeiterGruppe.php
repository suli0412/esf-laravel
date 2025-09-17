<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MitarbeiterGruppe extends Model
{
    use HasFactory;

    protected $table = 'mitarbeiter_gruppen';
    protected $primaryKey = 'gruppe_id';
    protected $fillable = ['bezeichnung'];

    /** Многие-ко-многим: Gruppe ↔ Mitarbeiter */
    public function mitarbeiter()
    {
        return $this->belongsToMany(
            Mitarbeiter::class,
            'mitarbeiter_gruppe',
            'gruppe_id',
            'mitarbeiter_id'
        );
    }
}
