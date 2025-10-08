<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\LogOptions;
use App\Models\Traits\Blameable;

class Teilnehmer extends Model
{
    use HasFactory, LogsActivity, Blameable;

    protected $table = 'teilnehmer';

    // WICHTIG: Primärschlüssel ist "Teilnehmer_id"
    protected $primaryKey = 'Teilnehmer_id';
    public $incrementing = true;   // passt zu deinem Schema
    protected $keyType = 'int';

    public $timestamps = true;

    protected $fillable = [
        'Nachname','Vorname','Geschlecht','SVN','Strasse','Hausnummer','PLZ','Wohnort','Land',
        'Email','Telefonnummer','Geburtsdatum','Geburtsland','Staatszugehörigkeit',
        'Staatszugehörigkeit_Kategorie','Aufenthaltsstatus',
        'Minderheit','Behinderung','Obdachlos','LändlicheGebiete','ElternImAuslandGeboren',
        'Armutsbetroffen','Armutsgefährdet','Bildungshintergrund',
        'IDEA_Stammdatenblatt','IDEA_Dokumente','PAZ',
        'Berufserfahrung_als','Bereich_berufserfahrung','Land_berufserfahrung','Firma_berufserfahrung',
        'Zeit_berufserfahrung','Stundenumfang_berufserfahrung','Zertifikate',
        'Berufswunsch','Berufswunsch_branche','Berufswunsch_branche2',
        'Clearing_gruppe','Unterrichtseinheiten','Anmerkung','gruppe_id',
        // Kompetenzstände-Übernahmefelder am Teilnehmer
        'de_lesen_in','de_hoeren_in','de_schreiben_in','de_sprechen_in','en_in','ma_in',
        'de_lesen_out','de_hoeren_out','de_schreiben_out','de_sprechen_out','en_out','ma_out',
    ];

    protected $casts = [
        'Minderheit' => 'integer',
        'Behinderung' => 'integer',
        'Obdachlos' => 'integer',
        'LändlicheGebiete' => 'integer',
        'ElternImAuslandGeboren' => 'integer',
        'Armutsbetroffen' => 'integer',
        'Armutsgefährdet' => 'integer',
        'IDEA_Stammdatenblatt' => 'boolean',
        'IDEA_Dokumente' => 'boolean',
        'Clearing_gruppe' => 'boolean',
        'Geburtsdatum' => 'date:Y-m-d',
    ];

    /** ---------- Activitylog ---------- */
    protected static $recordEvents = ['created','updated','deleted'];
    protected static $logName = 'teilnehmer';
    protected static $logAttributes = ['*'];
    protected static $logOnlyDirty = true;
    protected static $submitEmptyLogs = false;
    protected static $logAttributesToIgnore = [];

    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
    public function updater() { return $this->belongsTo(User::class, 'updated_by'); }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('teilnehmer')
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function getDescriptionForEvent(string $eventName): string
    {
        return "Teilnehmer {$eventName}";
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
    /** ---------- /Activitylog ---------- */

    public function checkliste()
    {
        return $this->hasOne(ChecklisteNermin::class, 'Teilnehmer_id', 'Teilnehmer_id');
    }

    // Damit Route Model Binding mit {teilnehmer:Teilnehmer_id} funktioniert
    public function getRouteKeyName(): string
    {
        return 'Teilnehmer_id';
    }

    public function beratungen()
    {
        return $this->hasMany(Beratung::class, 'teilnehmer_id', 'Teilnehmer_id')
                    ->orderBy('datum','desc');
    }

    public function gruppenBeratungen()
    {
        return $this->belongsToMany(
            GruppenBeratung::class,
            'gruppen_beratung_teilnehmer',
            'teilnehmer_id',
            'gruppen_beratung_id'
        );
    }

    public function projekte()
    {
        return $this->belongsToMany(
            Projekt::class,
            'teilnehmer_projekt',
            'teilnehmer_id',
            'projekt_id'
        )->withPivot('Teilnehmer_Projekt_id','beginn','ende','status','anmerkung');
    }

    public function anwesenheiten()
    {
        return $this->hasMany(TeilnehmerAnwesenheit::class, 'teilnehmer_id', 'Teilnehmer_id');
    }

    public function teilnehmerProjekte()
    {
        return $this->hasMany(TeilnehmerProjekt::class, 'teilnehmer_id', 'Teilnehmer_id')
                    ->with('projekt');
    }

    public function kenntnisse()
    {
        return $this->hasOne(TeilnehmerKenntnisse::class, 'teilnehmer_id', 'Teilnehmer_id');
    }

    public function dokumente()
    {
        return $this->hasMany(TeilnehmerDokument::class, 'teilnehmer_id', 'Teilnehmer_id');
    }

    public function gruppe()
    {
        return $this->belongsTo(Gruppe::class, 'gruppe_id', 'gruppe_id');
    }

    public function praktika()
    {
        return $this->hasMany(TeilnehmerPraktikum::class, 'teilnehmer_id', 'Teilnehmer_id')
                    ->orderByDesc('beginn');
    }

    // Komfort: $teilnehmer->full_name
    public function getFullNameAttribute(): string
    {
        return trim(($this->Vorname ?? '').' '.($this->Nachname ?? ''));
    }

    /** ---------- Kompetenzstände ---------- */
    public function kompetenzstaende()
    {
        // FK in kompetenzstand = teilnehmer_id, lokaler Key in teilnehmer = Teilnehmer_id
        return $this->hasMany(Kompetenzstand::class, 'teilnehmer_id', 'Teilnehmer_id');
    }

    public function kompetenzstandEintritt()
    {
        return $this->kompetenzstaende()->where('zeitpunkt_norm', 'eintritt');
    }

    public function kompetenzstandAustritt()
    {
        return $this->kompetenzstaende()->where('zeitpunkt_norm', 'austritt');
    }

    /** ---------- Prüfungs-Teilnahmen ---------- */
    public function pruefungstermine()
    {
        return $this->belongsToMany(
            Pruefungstermin::class,
            'Pruefungsteilnahme',
            'teilnehmer_id',
            'termin_id'
        )->withPivot(['bestanden','selbstzahler']);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
