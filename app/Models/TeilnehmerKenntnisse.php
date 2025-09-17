<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeilnehmerKenntnisse extends Model
{
    protected $table = 'teilnehmer_kenntnisse';
    protected $fillable = [
        'teilnehmer_id','de_lesen','de_hoeren','de_schreiben','de_sprechen',
        'en_gesamt','mathe','ikt',
    ];

    public function teilnehmer()
    {
        return $this->belongsTo(Teilnehmer::class, 'teilnehmer_id', 'Teilnehmer_id');
    }
}

