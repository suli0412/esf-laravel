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

    // Список полей, которые можно массово заполнять (create/update)
    protected $fillable = [
        'Nachname','Vorname','Geschlecht','SVN','Strasse','Hausnummer','PLZ','Wohnort','Land',
        'Email','Telefonnummer','Geburtsdatum','Geburtsland','Staatszugehörigkeit',
        'Staatszugehörigkeit_Kategorie','Aufenthaltsstatus',
        'Minderheit','Behinderung','Obdachlos','LändlicheGebiete','ElternImAuslandGeboren',
        'Armutsbetroffen','Armutsgefährdet','Bildungshintergrund',
        'IDEA_Stammdatenblatt','IDEA_Dokumente','PAZ',
        'Berufserfahrung_als','Bereich_berufserfahrung','Land_berufserfahrung','Firma_berufserfahrung',
        'Zeit_berufserfahrung','Stundenumfang_берufserfahrung','Zertifikate',
        'Berufswunsch','Berufswunsch_branche','Berufswunsch_branche2',
        'Clearing_gruppe','Unterrichtseinheiten','Anmerkung',
    ];

    // Приведение типов
    protected $casts = [
        'Geburtsdatum' => 'date',
        'IDEA_Stammdatenblatt' => 'boolean',
        'IDEA_Dokumente'       => 'boolean',
        'Clearing_gruppe'      => 'boolean',
        'Unterrichtseinheiten' => 'integer',
        'Stundenumfang_берufserfahrung' => 'decimal:2',
    ];
}
