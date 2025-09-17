<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pruefungstermin extends Model
{
    protected $table = 'pruefungstermin';
    protected $primaryKey = 'termin_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = ['niveau_id','bezeichnung','datum','institut'];

    protected $casts = [
        'datum' => 'date',
    ];

    public function niveau()
    {
        return $this->belongsTo(Niveau::class, 'niveau_id', 'niveau_id');
    }

    public function teilnehmer()
    {
        // Pivot: pruefungsteilnahme (termin_id, teilnehmer_id, bestanden, selbstzahler)
        return $this->belongsToMany(Teilnehmer::class, 'pruefungsteilnahme', 'termin_id', 'teilnehmer_id')
                    ->withPivot(['bestanden','selbstzahler']);
    }
}
