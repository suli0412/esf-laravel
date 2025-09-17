<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Beratung extends Model
{
    use HasFactory;

    protected $table = 'beratungen';
    protected $primaryKey = 'beratung_id';
    public $incrementing = true;
    protected $keyType = 'int';

    public $timestamps = false; // ← важно, если нет created_at/updated_at

    protected $fillable = [
        'art_id','thema_id','teilnehmer_id','mitarbeiter_id','datum','dauer_h','notizen'
    ];

    protected $casts = [
        'datum' => 'date',
        'dauer_h' => 'decimal:2',
    ];

    public function art()        { return $this->belongsTo(Beratungsart::class,   'art_id',        'Art_id'); }
    public function thema()      { return $this->belongsTo(Beratungsthema::class, 'thema_id',      'Thema_id'); }
    public function teilnehmer() { return $this->belongsTo(Teilnehmer::class,     'teilnehmer_id', 'Teilnehmer_id'); }
    public function mitarbeiter(){ return $this->belongsTo(Mitarbeiter::class,    'mitarbeiter_id','Mitarbeiter_id'); }
}
