<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kompetenzstand extends Model
{
    protected $table = 'kompetenzstand';

    // WICHTIG: Tabelle hat keinen PK & keine Timestamps
    protected $primaryKey = null;
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'teilnehmer_id',
        'kompetenz_id',
        'niveau_id',
        'zeitpunkt',
        'zeitpunkt_norm',
        'datum',
        'bemerkung',
    ];

    // Relations (Teilnehmer-PK = Teilnehmer_id)
    public function teilnehmer()
    {
        return $this->belongsTo(\App\Models\Teilnehmer::class, 'teilnehmer_id', 'Teilnehmer_id');
    }

    public function kompetenz()
    {
        return $this->belongsTo(\App\Models\Kompetenz::class, 'kompetenz_id', 'kompetenz_id');
    }

    public function niveau()
    {
        return $this->belongsTo(\App\Models\Niveau::class, 'niveau_id', 'niveau_id');
    }
}
