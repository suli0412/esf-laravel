<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mitarbeiter extends Model
{
    use HasFactory;

    protected $table = 'mitarbeiter';
    protected $primaryKey = 'Mitarbeiter_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'Nachname', 'Vorname', 'Taetigkeit', 'Email', 'Telefonnummer',
    ];

    /** Группы, в которых состоит сотрудник */
    public function gruppen()
    {
        return $this->belongsToMany(
            \App\Models\Gruppe::class, // <= используем FQN, чтобы не импортировать
            'gruppe_mitarbeiter',      // pivot-таблица
            'mitarbeiter_id',          // FK на эту модель в pivot
            'gruppe_id',               // FK на Gruppe в pivot
            'Mitarbeiter_id',          // локальный PK (нестандартный)
            'gruppe_id'                // PK у Gruppe (нестандартный)
        )->withTimestamps();
    }
}
