<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;

class Pruefungstermin extends Model
{
    protected $table = 'pruefungstermin';
    protected $primaryKey = 'termin_id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false; // Tabelle hat keine created_at/updated_at


    protected $fillable = [
        'niveau_id','bezeichnung','titel','datum','start_at','end_at','institut',
        'created_by','updated_by',
    ];

    // Falls 'datum' als DATETIME in der DB liegt: 'datetime'
    // Bei reinem DATE -> 'date'
    protected $casts = [
        'datum'    => 'date',      // Kompatibilität (nur Datum)
        'start_at' => 'datetime',  // NEU: mit Uhrzeit
        'end_at'   => 'datetime',
    ];

    public function createdBy() { return $this->belongsTo(User::class,'created_by'); }
    public function updatedBy() { return $this->belongsTo(User::class,'updated_by'); }


    public function niveau()
    {
        return $this->belongsTo(Niveau::class, 'niveau_id', 'niveau_id');
    }

    /**
     * Pivot: pruefungsteilnahme (termin_id, teilnehmer_id, bestanden, selbstzahler)
     */
    public function teilnehmer()
    {
        return $this->belongsToMany(Teilnehmer::class, 'pruefungsteilnahme', 'termin_id', 'teilnehmer_id')
                    ->withPivot(['bestanden', 'selbstzahler']);
    }

    /* -------- Komfort-Scopes für Filter in der Liste -------- */

    public function scopeUpcoming($q)
    {
        return $q->whereDate('datum', '>=', Carbon::today());
    }

    public function scopePast($q)
    {
        return $q->whereDate('datum', '<', Carbon::today());
    }

    public function scopeSearch($q, ?string $text)
    {
        $text = trim((string)$text);
        if ($text === '') return $q;

        return $q->where(function ($x) use ($text) {
            $x->where('institut', 'like', "%{$text}%")
              ->orWhere('bezeichnung', 'like', "%{$text}%")
              ->orWhere('titel', 'like', "%{$text}%");
        });
    }

    public function scopeForNiveau($q, $niveauId)
    {
        if (empty($niveauId)) return $q;
        return $q->where('niveau_id', $niveauId);
    }
}


