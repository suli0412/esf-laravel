<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeilnehmerAnwesenheit extends Model
{
    protected $table = 'teilnehmer_anwesenheit';
    protected $primaryKey = 'anwesenheit_id';
    public $timestamps = false;

    protected $fillable = ['teilnehmer_id','datum','status','fehlminuten'];

    protected $casts = [
        'datum' => 'date',
        'fehlminuten' => 'integer',
    ];

    public const STATI = [
        'Anwesend',
        'Abwesend',
        'Abwesend Entschuldigt',
        'religiöser Feiertag',
    ];

    public function teilnehmer()
    {
        return $this->belongsTo(Teilnehmer::class, 'teilnehmer_id', 'Teilnehmer_id');
    }
}
