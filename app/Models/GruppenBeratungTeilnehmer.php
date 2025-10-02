<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;



class GruppenBeratung extends Model
{
    protected $table = 'gruppen_beratungen'; // an deine Tabelle anpassen
    protected $fillable = [
        'gruppe_id','mitarbeiter_id','thema','datum','dauer_min','notiz',
    ];

    public function mitarbeiter()
    {
        return $this->belongsTo(\App\Models\Mitarbeiter::class,
            'mitarbeiter_id', 'Mitarbeiter_id');
    }

    public function gruppe()
    {
        return $this->belongsTo(\App\Models\Gruppe::class,
            'gruppe_id', 'gruppe_id');
    }
}
