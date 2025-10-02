<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class GruppenBeratung extends Model
{
    use HasFactory;


    public function teilnehmer()
    {
        return $this->belongsToMany(
            \App\Models\Teilnehmer::class,
            'gruppen_beratung_teilnehmer',
            'gruppen_beratung_id',   // FK auf diese Session im Pivot
            'teilnehmer_id',         // FK auf Teilnehmer im Pivot
            'id',                    // PK dieser Session (anpassen falls anders)
            'Teilnehmer_id'          // PK bei Teilnehmer (anpassen falls anders)
        )->withTimestamps();
    }


    protected $table = 'gruppen_beratungen';
    protected $primaryKey = 'gruppen_beratung_id';
    protected $fillable = ['art_id','thema_id','mitarbeiter_id','datum','dauer_h','thema','inhalt','TNUnterlagen'];
    protected $casts = ['datum'=>'date','dauer_h'=>'decimal:2','TNUnterlagen'=>'boolean'];

    public function mitarbeiter(){ return $this->belongsTo(Mitarbeiter::class, 'mitarbeiter_id','Mitarbeiter_id'); }
    public function gruppe()    { return $this->belongsTo(Gruppe::class, 'gruppe_id','gruppe_id'); }
    public function art()       { return $this->belongsTo(Beratungsart::class, 'beratungsart_id'); }
    public function thema()     { return $this->belongsTo(Beratungsthema::class, 'beratungsthema_id'); }


}
