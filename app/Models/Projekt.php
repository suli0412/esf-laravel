<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Projekt extends Model
{
    use HasFactory;

    // 1) точное имя таблицы как в БД
    protected $table = 'Projekte';

    protected $primaryKey = 'projekt_id';
    public $incrementing = true;
    protected $keyType = 'int';

    // 2) в таблице нет created_at/updated_at
    public $timestamps = false;

    protected $fillable = ['code','bezeichnung','start','ende','aktiv'];

    protected $casts = [
        'start' => 'date',
        'ende'  => 'date',
        'aktiv' => 'boolean',
    ];

    // 4) удобный биндинг по ключу projekt_id
    public function getRouteKeyName(): string
    {
        return 'projekt_id';
    }

    // связь многие-ко-многим через pivot Teilnehmer_Projekt
    public function teilnehmer()
    {
        return $this->belongsToMany(
            Teilnehmer::class,
            'Teilnehmer_Projekt',   // 3) точное имя как в БД
            'projekt_id',
            'teilnehmer_id'
        )->withPivot('Teilnehmer_Projekt_id','beginn','ende','status','anmerkung');
    }

    // если используешь отдельную модель для pivot
    public function teilnehmerProjekte()
    {
        return $this->hasMany(TeilnehmerProjekt::class, 'projekt_id', 'projekt_id');
    }

    public function standardMitarbeiter() {
    return $this->belongsTo(\App\Models\Mitarbeiter::class, 'standard_mitarbeiter_id', 'Mitarbeiter_id');
    }

}
