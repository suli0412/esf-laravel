<?php

// App/Models/Kompetenz.php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Kompetenz extends Model {
    protected $table = 'kompetenzen';
    protected $primaryKey = 'kompetenz_id';
    public $timestamps = false;
    protected $fillable = ['code','bezeichnung'];
}

// App/Models/Niveau.php
class Niveau extends Model {
    protected $table = 'niveau';
    protected $primaryKey = 'niveau_id';
    public $timestamps = false;
    protected $fillable = ['code','label','sort_order'];
    protected $casts = ['sort_order'=>'int'];
}



// App/Models/Pruefungstermin.php
class Pruefungstermin extends Model {
    protected $table = 'Pruefungstermin';
    protected $primaryKey = 'termin_id';
    public $timestamps = false;
    protected $fillable = ['niveau_id','datum','institut','bezeichnung'];

    public function niveau() { return $this->belongsTo(Niveau::class,'niveau_id','niveau_id'); }

    // Teilnehmer über Pivot:
    public function teilnehmer() {
        return $this->belongsToMany(Teilnehmer::class, 'Pruefungsteilnahme', 'termin_id', 'teilnehmer_id')
            ->withPivot(['bestanden','selbstzahler']);
    }
}
