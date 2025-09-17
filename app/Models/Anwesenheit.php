<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Anwesenheit extends Model
{
    protected $table = 'anwesenheiten'; // falls anders: anpassen
    protected $fillable = ['teilnehmer_id','gruppe_id','datum','status','bemerkung'];
    protected $casts = [
        'datum' => 'date',
        'status' => 'boolean',
    ];

    public function teilnehmer() { return $this->belongsTo(Teilnehmer::class, 'teilnehmer_id', 'Teilnehmer_id'); }
    public function gruppe()     { return $this->belongsTo(Gruppe::class, 'gruppe_id'); }
}
