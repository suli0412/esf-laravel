<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Gruppe extends Model
{
    protected $table = 'gruppen';
    protected $primaryKey = 'gruppe_id';

    protected $fillable = [
        'name',
        'code',
        'projekt_id',
        'standard_mitarbeiter_id',
        'aktiv',
    ];

    protected $casts = [
        'aktiv' => 'boolean',
    ];

    /** Teilnehmergruppe */
    public function teilnehmer()
    {
         return $this->belongsToMany(Teilnehmer::class, 'gruppe_teilnehmer', 'gruppe_id', 'teilnehmer_id')
                    ->withPivot(['beitritt_von','beitritt_bis'])
                    ->withTimestamps();
    }

    public function getRouteKeyName() { return 'gruppe_id';

    }

    public function gruppen()
    {
        return $this->belongsToMany(
            \App\Models\Gruppe::class,
            'gruppe_teilnehmer',
            'teilnehmer_id',           // FK in Pivot → Teilnehmer
            'gruppe_id'                // FK in Pivot → Gruppe
        )->withPivot(['beitritt_von','beitritt_bis'])
        ->withTimestamps();
    }


    /** gruppen  Projekt */
    public function projekt()
    {
        return $this->belongsTo(Projekt::class, 'projekt_id', 'projekt_id');
    }

    /** Verantwortliche Mitarbeiter*in der Gruppe*/
    public function standardMitarbeiter()
    {
        return $this->belongsTo(Mitarbeiter::class, 'standard_mitarbeiter_id', 'Mitarbeiter_id');
    }

    /** Mitarbeitergruppe für die Teilnehmergruppe */
    public function mitarbeiter()
    {
        return $this->belongsToMany(
            Mitarbeiter::class,
            'gruppe_mitarbeiter',   // pivot-таблица
            'gruppe_id',            // foreignPivotKey (текущая модель)
            'mitarbeiter_id',       // relatedPivotKey
            'gruppe_id',            // parentKey на этой модели (нестандартный PK)
            'Mitarbeiter_id'        // relatedKey на модели Mitarbeiter (нестандартный PK)
        )->withTimestamps();
    }

    /** Scope: только активные группы */
    public function scopeAktiv($q)
    {
        return $q->where('aktiv', true);
    }
}
