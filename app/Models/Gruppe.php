<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Gruppe extends Model
{
    protected $table = 'gruppen';
    protected $primaryKey = 'gruppe_id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

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

    /**
     * Für Route-Model-Binding (optional; mit deinen Routen kompatibel)
     */
    public function getRouteKeyName()
    {
        return 'gruppe_id';
    }

    /**
     * Teilnehmer dieser Gruppe (Pivot: gruppe_teilnehmer)
     * WICHTIG: Nicht-Standard PKs angeben (5./6. Parameter).
     */
    public function teilnehmer()
    {
        return $this->belongsToMany(
            \App\Models\Teilnehmer::class,
            'gruppe_teilnehmer',
            'gruppe_id',          // FK auf diese Gruppe in Pivot
            'teilnehmer_id',      // FK auf Teilnehmer in Pivot
            'gruppe_id',          // lokaler PK (dieses Modell)
            'Teilnehmer_id'       // PK in Teilnehmer
        )
        ->withPivot(['beitritt_von', 'beitritt_bis'])
        ->withTimestamps(); // entfernen, falls Pivot KEINE created_at/updated_at hat
    }

    /**
     * Mitarbeiter dieser Gruppe (Pivot: gruppe_mitarbeiter)
     */
    public function mitarbeiter()
    {
        return $this->belongsToMany(
            \App\Models\Mitarbeiter::class,
            'gruppe_mitarbeiter',
            'gruppe_id',          // FK auf diese Gruppe in Pivot
            'mitarbeiter_id',     // FK auf Mitarbeiter in Pivot
            'gruppe_id',          // lokaler PK
            'Mitarbeiter_id'      // PK in Mitarbeiter
        )
        // ->withPivot(['rolle']) // falls vorhanden
        ->withTimestamps(); // entfernen, falls Pivot KEINE created_at/updated_at hat
    }

    /**
     * Zugehöriges Projekt
     */
    public function projekt()
    {
        return $this->belongsTo(\App\Models\Projekt::class, 'projekt_id', 'projekt_id');
    }

    /**
     * Standard-Verantwortliche*r Mitarbeiter*in
     */
    public function standardMitarbeiter()
    {
        return $this->belongsTo(\App\Models\Mitarbeiter::class, 'standard_mitarbeiter_id', 'Mitarbeiter_id');
    }

    /**
     * Scope: nur aktive Gruppen
     */
    public function scopeAktiv($q)
    {
        return $q->where('aktiv', true);
    }

    /**
     * Scope: nach Projekt filtern
     */
    public function scopeForProjekt($q, $projektId)
    {
        return $q->where('projekt_id', $projektId);
    }

    /**
     * Scope: einfache Suche über Name/Code
     */
    public function scopeSearch($q, ?string $term)
    {
        $term = trim((string) $term);
        if ($term === '') return $q;

        return $q->where(function ($qq) use ($term) {
            $qq->where('name', 'like', "%{$term}%")
               ->orWhere('code', 'like', "%{$term}%");
        });
    }

    /** POST /gruppen – neue Gruppe speichern */
public function store(Request $request)
{
    $data = $request->validate([
        'bezeichnung' => ['required','string','max:255'],
    ]);

    $gruppe = Gruppe::create([
        'bezeichnung' => $data['bezeichnung'],
    ]);

    return redirect()
        ->route('gruppen.index') // passt zu Route::resource(...)
        ->with('status', 'Gruppe wurde angelegt.');
}
}
