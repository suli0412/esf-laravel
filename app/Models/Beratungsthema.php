<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Beratungsthema extends Model
{
    protected $table = 'beratungsthemen';
    protected $primaryKey = 'Thema_id';
    public $timestamps = false;

    protected $fillable = ['Bezeichnung'];

    public function beratungen() { return $this->hasMany(Beratung::class, 'thema_id', 'Thema_id'); }
}
