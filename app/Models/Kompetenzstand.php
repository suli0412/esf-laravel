<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;


class Kompetenzstand extends Model
{
    protected $table = 'kompetenzstand';
    protected $fillable = ['teilnehmer_id','kompetenz_id','niveau_id','zeitpunkt','datum','bemerkung'];

    // Mutator: beim Speichern normalisieren
    public function setZeitpunktAttribute($value)
    {
        $norm = strtolower(trim((string)$value));
        // akzeptiere Varianten
        $map = ['eintritt' => 'eintritt', 'austritt' => 'austritt'];
        $this->attributes['zeitpunkt'] = $map[$norm] ?? $norm;
    }

    // Accessor: sicheres, genormtes Feld
    public function getZeitpunktNormAttribute(): string
    {
        return strtolower(trim((string)($this->attributes['zeitpunkt'] ?? '')));
    }




    // Scopes
        public function scopeEintritt(Builder $q): Builder
    {
        return Schema::hasColumn('kompetenzstand', 'zeitpunkt_norm')
            ? $q->where('zeitpunkt_norm', 'eintritt')
            : $q->whereRaw('LOWER(TRIM(zeitpunkt)) = ?', ['eintritt']);
    }

    public function scopeAustritt(Builder $q): Builder
    {
        return Schema::hasColumn('kompetenzstand', 'zeitpunkt_norm')
            ? $q->where('zeitpunkt_norm', 'austritt')
            : $q->whereRaw('LOWER(TRIM(zeitpunkt)) = ?', ['austritt']);
    }

}
