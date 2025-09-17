<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pruefungsteilnahme extends Model
{
    protected $table = 'pruefungsteilnahme';
    public $timestamps = false;
    protected $primaryKey = null; // composite
    public $incrementing = false;

    protected $fillable = ['termin_id','teilnehmer_id','bestanden','selbstzahler'];

    protected $casts = [
        'bestanden'   => 'boolean',
        'selbstzahler'=> 'boolean',
    ];

    public function termin()
    {
        return $this->belongsTo(Pruefungstermin::class, 'termin_id', 'termin_id');
    }

    public function teilnehmer()
    {
        return $this->belongsTo(Teilnehmer::class, 'teilnehmer_id', 'Teilnehmer_id');
    }
}
