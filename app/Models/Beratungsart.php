<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Beratungsart extends Model
{
    use HasFactory;

    protected $table = 'beratungsarten';
    protected $primaryKey = 'Art_id';
    public $incrementing = true;
    protected $keyType = 'int';
    protected $fillable = ['Code','Bezeichnung'];

    public function beratungen()
    {
        return $this->hasMany(Beratung::class, 'art_id', 'Art_id');
    }
    public function gruppenBeratungen()
    {
        return $this->hasMany(GruppenBeratung::class, 'art_id', 'Art_id');
    }
}
