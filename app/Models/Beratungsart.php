<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Beratungsart extends Model
{
    protected $table = 'beratungsarten';
    protected $primaryKey = 'Art_id';
    public $timestamps = false;

    protected $fillable = ['Code','Bezeichnung'];

    public function beratungen() { return $this->hasMany(Beratung::class, 'art_id', 'Art_id'); }
}
