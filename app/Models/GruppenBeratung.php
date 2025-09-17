<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class GruppenBeratung extends Model
{
    use HasFactory;

    protected $table = 'gruppen_beratungen';
    protected $primaryKey = 'gruppen_beratung_id';
    protected $fillable = ['art_id','thema_id','mitarbeiter_id','datum','dauer_h','thema','inhalt','TNUnterlagen'];
    protected $casts = ['datum'=>'date','dauer_h'=>'decimal:2','TNUnterlagen'=>'boolean'];

    public function art()        { return $this->belongsTo(Beratungsart::class, 'art_id', 'Art_id'); }
    public function themaRef()   { return $this->belongsTo(Beratungsthema::class, 'thema_id', 'Thema_id'); }
    public function mitarbeiter(){ return $this->belongsTo(Mitarbeiter::class, 'mitarbeiter_id', 'Mitarbeiter_id'); }

    public function teilnehmer()
    {
        return $this->belongsToMany(
            Teilnehmer::class,
            'gruppen_beratung_teilnehmer',
            'gruppen_beratung_id',
            'teilnehmer_id'
        );
    }
}
