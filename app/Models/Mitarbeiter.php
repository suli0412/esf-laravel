<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mitarbeiter extends Model
{
    protected $table = 'mitarbeiter';
    protected $primaryKey = 'Mitarbeiter_id';
    public $timestamps = false;

    protected $fillable = ['Nachname','Vorname','Taetigkeit','Email','Telefon'];

    public function beratungen() { return $this->hasMany(Beratung::class, 'mitarbeiter_id', 'Mitarbeiter_id'); }
}
