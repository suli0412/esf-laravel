<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeilnehmerPraktikum extends Model
{
    protected $table = 'teilnehmer_praktika';
    protected $primaryKey = 'praktikum_id';
    public $timestamps = true;

    protected $fillable = [
        'teilnehmer_id','bereich','land','firma',
        'stunden_ausmass','anmerkung','beginn','ende',
    ];

    protected $casts = [
        'beginn' => 'date',
        'ende'   => 'date',
    ];

    public function teilnehmer()
    {
        return $this->belongsTo(Teilnehmer::class, 'teilnehmer_id', 'Teilnehmer_id');
    }

    // Komfort-Alias: $p->stundenumfang
    public function getStundenumfangAttribute()
    {
        return $this->attributes['stunden_ausmass'] ?? null;
    }

    public function praktika()
{
    return $this->hasMany(TeilnehmerPraktikum::class, 'teilnehmer_id', 'Teilnehmer_id')
                ->orderByDesc('beginn');
}

}
