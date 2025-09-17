<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Beratungsthema extends Model
{
    use HasFactory;

    protected $table = 'beratungsthemen';
    protected $primaryKey = 'Thema_id';
    public $incrementing = true;
    protected $keyType = 'int';
    protected $fillable = ['Bezeichnung','Beschreibung'];

    public function beratungen()
    {
        return $this->hasMany(Beratung::class, 'thema_id', 'Thema_id');
    }
    public function gruppenBeratungen()
    {
        return $this->hasMany(GruppenBeratung::class, 'thema_id', 'Thema_id');
    }
}
