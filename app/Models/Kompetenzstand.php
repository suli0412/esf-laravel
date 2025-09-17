<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kompetenzstand extends Model
{
    protected $table = 'kompetenzstand';
    public $timestamps = false;
    protected $primaryKey = null; // Composite PK
    public $incrementing = false;

    protected $fillable = [
        'teilnehmer_id','zeitpunkt','kompetenz_id','niveau_id','datum','bemerkung'
    ];

    public function teilnehmer()
    {
        return $this->belongsTo(Teilnehmer::class, 'teilnehmer_id', 'Teilnehmer_id');
    }

    public function kompetenz()
    {
        return $this->belongsTo(Kompetenz::class, 'kompetenz_id', 'kompetenz_id');
    }

    public function niveau()
    {
        return $this->belongsTo(Niveau::class, 'niveau_id', 'niveau_id');
    }
}
