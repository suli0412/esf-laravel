<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Beratung extends Model
{
    protected $table = 'beratungen';
    protected $primaryKey = 'beratung_id';  // <-- wichtig
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    // Für URL-Generierung + Route Model Binding explizit machen:
    public function getRouteKeyName(): string
    {
        return 'beratung_id';
    }

    protected $fillable = [
        'art_id','thema_id','teilnehmer_id','mitarbeiter_id','datum','dauer_h','notizen'
    ];

    public function art()        { return $this->belongsTo(Beratungsart::class, 'art_id', 'Art_id'); }
    public function thema()      { return $this->belongsTo(Beratungsthema::class, 'thema_id', 'Thema_id'); }
    public function mitarbeiter(){ return $this->belongsTo(Mitarbeiter::class, 'mitarbeiter_id', 'Mitarbeiter_id'); }
    public function teilnehmer() { return $this->belongsTo(Teilnehmer::class, 'teilnehmer_id', 'Teilnehmer_id'); }
}
