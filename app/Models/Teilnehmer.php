<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;      // LogsActivity
use Spatie\Activitylog\Models\Activity;          // Activity Model
use Spatie\Activitylog\LogOptions;
use App\Models\Traits\Blameable;

class Teilnehmer extends Model
{
    use HasFactory, LogsActivity;
     use Blameable;

    protected $table = 'teilnehmer';
    protected $primaryKey = 'Teilnehmer_id';
    public $incrementing = true;
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
        'Clearing_gruppe','Unterrichtseinheiten','Anmerkung','gruppe_id','de_lesen_in','de_hoeren_in','de_schreiben_in','de_sprechen_in','en_in','ma_in',
    'de_lesen_out','de_hoeren_out','de_schreiben_out','de_sprechen_out','en_out','ma_out'
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



    /** ---------- Activitylog-Konfiguration ---------- */
    protected static $recordEvents = ['created','updated','deleted'];
    protected static $logName = 'teilnehmer';
    protected static $logAttributes = ['*'];     // alle Felder loggen
    protected static $logOnlyDirty = true;       // nur Veränderungen
    protected static $submitEmptyLogs = false;
    protected static $logAttributesToIgnore = [
        // z. B. große/sensible Felder hier eintragen (falls nötig)
        // 'some_big_text_column',
    ];

    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
    public function updater() { return $this->belongsTo(User::class, 'updated_by'); }

    public function getActivitylogOptions(): LogOptions
    {
    return LogOptions::defaults()
        ->useLogName('teilnehmer')
        ->logAll()              // alle Attribute
        ->logOnlyDirty()        // nur Änderungen
        ->dontSubmitEmptyLogs();// keine leeren Logs
    }

    public function getDescriptionForEvent(string $eventName): string
    {
        return "Teilnehmer {$eventName}";
    }

    public function tapActivity(Activity $activity, string $eventName): void
    {
        // bei CLI/Seeder/Migration keine Request-Daten anhängen
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

    public function getRouteKeyName(): string
    {
        return 'Teilnehmer_id';
    }

    public function beratungen()
    {
        return $this->hasMany(\App\Models\Beratung::class, 'teilnehmer_id', 'Teilnehmer_id')
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
        return $this->hasMany(\App\Models\TeilnehmerAnwesenheit::class, 'teilnehmer_id', 'Teilnehmer_id');
    }

    public function teilnehmerProjekte()
    {
        return $this->hasMany(\App\Models\TeilnehmerProjekt::class, 'teilnehmer_id', 'Teilnehmer_id')
                    ->with('projekt');
    }

    public function kenntnisse()
    {
        return $this->hasOne(TeilnehmerKenntnisse::class, 'teilnehmer_id', 'Teilnehmer_id');
    }

    public function dokumente()
    {
        return $this->hasMany(\App\Models\TeilnehmerDokument::class, 'teilnehmer_id', 'Teilnehmer_id');
    }


    public function gruppe()
    {
        return $this->belongsTo(\App\Models\Gruppe::class, 'gruppe_id', 'gruppe_id');
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

    // Kompetenzstände
    public function kompetenzstaende() {
        return $this->hasMany(\App\Models\Kompetenzstand::class, 'teilnehmer_id', 'Teilnehmer_id');
    }
    public function kompetenzstandEintritt() {
        return $this->kompetenzstaende()->where('zeitpunkt','Eintritt');
    }
    public function kompetenzstandAustritt() {
        return $this->kompetenzstaende()->where('zeitpunkt','Austritt');
    }

    // Prüfungs-Teilnahmen
    public function pruefungstermine() {
        return $this->belongsToMany(\App\Models\Pruefungstermin::class, 'Pruefungsteilnahme',
            'teilnehmer_id', 'termin_id')->withPivot(['bestanden','selbstzahler']);
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
