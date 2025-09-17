<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Teilnehmer extends Model
{
    use HasFactory;

    protected $table = 'teilnehmer';
    protected $primaryKey = 'Teilnehmer_id';
    public $incrementing = true;
    protected $keyType = 'int';
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
        'Clearing_gruppe','Unterrichtseinheiten','Anmerkung','gruppe_id'
    ];

    protected $casts = [
        // Flags
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

        // Datum
        'Geburtsdatum' => 'date:Y-m-d',
    ];

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
        return $this->hasMany(Beratung::class, 'teilnehmer_id', 'Teilnehmer_id');
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
       return $this->belongsToMany(Gruppe::class, 'gruppe_teilnehmer', 'teilnehmer_id', 'gruppe_id')
                    ->withPivot(['beitritt_von','beitritt_bis'])
                    ->withTimestamps();
    }

    public function praktika()
    {
        return $this->hasMany(TeilnehmerPraktikum::class, 'teilnehmer_id', 'Teilnehmer_id')
                    ->orderByDesc('beginn');
    }

    // (Optional) Komfort: $teilnehmer->full_name
    public function getFullNameAttribute(): string
    {
        return trim(($this->Vorname ?? '').' '.($this->Nachname ?? ''));
    }




// App/Models/Teilnehmer.php

// Kompetenzstände (Eintritt/Austritt)
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
    // für Buchungen (Pivot mit Feldern)
    return $this->belongsToMany(\App\Models\Pruefungstermin::class, 'Pruefungsteilnahme',
        'teilnehmer_id', 'termin_id')->withPivot(['bestanden','selbstzahler']);
}


}
