<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\LogOptions;

class Projekt extends Model
{
    use HasFactory, LogsActivity;

    /**
     * 1) Tabellen-Setup passend zur Migration
     *    (deine Migration heißt 'projekte' – lowercase!)
     */
    protected $table = 'projekte';

    protected $primaryKey = 'projekt_id';
    public $incrementing = true;
    protected $keyType = 'int';

    /**
     * 2) Timestamps: deine Migration enthält created_at/updated_at.
     *    Falls du das NICHT willst, auf false lassen.
     */
    public $timestamps = true;

    /**
     * 3) Zuweisbare Felder – exakt die, die es laut deiner Migration sicher gibt.
     *    (Wenn du später 'beschreibung', 'inhalte', 'verantwortlicher_id' nachrüstest,
     *     kannst du sie hier einfach ergänzen.)
     */
    protected $fillable = ['code','bezeichnung','start','ende','aktiv','beschreibung','inhalte','verantwortlicher_id'];

    protected $casts = [
        'start' => 'date',
        'ende'  => 'date',
        'aktiv' => 'boolean',
    ];

    /**
     * 4) Scopes für bequeme Abfragen
     */
    public function scopeAktiv($q)
    {
        return $q->where('aktiv', true);
    }

    public function scopeInaktiv($q)
    {
        return $q->where('aktiv', false);
    }

    /**
     * 5) Alias-Attribut is_active <-> aktiv
     *    So kannst du im Code/Forms 'is_active' verwenden,
     *    ohne die DB-Spalte 'aktiv' umzubenennen.
     */
    protected $appends = ['is_active'];

    public function getIsActiveAttribute(): bool
    {
        return (bool) ($this->attributes['aktiv'] ?? false);
    }

    public function setIsActiveAttribute($value): void
    {
        $this->attributes['aktiv'] = (bool) $value;
    }

    /**
     * 6) Activitylog-Konfiguration (einheitlicher Log-Name 'projekte')
     */
    protected static $recordEvents = ['created','updated','deleted'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('projekte')
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function getDescriptionForEvent(string $eventName): string
    {
        return "Projekt {$eventName}";
    }

    public function tapActivity(Activity $activity, string $eventName): void
    {
        if (app()->runningInConsole()) return;

        $activity->properties = $activity->properties->merge([
            'ip'         => request()->ip(),
            'url'        => request()->fullUrl(),
            'route'      => optional(request()->route())->getName(),
            'user_agent' => mb_substr((string)request()->userAgent(), 0, 255),
        ]);
    }

    /**
     * 7) Route-Model-Binding über 'projekt_id'
     */
    public function getRouteKeyName(): string
    {
        return 'projekt_id';
    }

    /**
     * 8) Relationen
     *    m:n Teilnehmer über Pivot 'Teilnehmer_Projekt' (so wie bei dir verwendet)
     */
    public function teilnehmer()
    {
        return $this->belongsToMany(
            Teilnehmer::class,
            'Teilnehmer_Projekt',
            'projekt_id',
            'teilnehmer_id'
        )->withPivot('Teilnehmer_Projekt_id','beginn','ende','status','anmerkung');
    }

    public function teilnehmerProjekte()
    {
        return $this->hasMany(TeilnehmerProjekt::class, 'projekt_id', 'projekt_id');
    }

    /**
     * Optional: Verantwortlicher (nur aktiv, wenn du die Spalte später hinzufügst)
     * - ungefährliche Relation: existiert die Spalte (noch) nicht, wird sie einfach nie gesetzt.
     */
    public function verantwortlicher()
    {
        return $this->belongsTo(Mitarbeiter::class, 'verantwortlicher_id', 'Mitarbeiter_id');
    }

    /**
     * Optional: Standard-Mitarbeiter (falls vorhanden)
     */
    public function standardMitarbeiter()
    {
        return $this->belongsTo(Mitarbeiter::class, 'standard_mitarbeiter_id', 'Mitarbeiter_id');
    }

    // app/Models/Projekt.php



}
