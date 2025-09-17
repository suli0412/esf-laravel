<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GruppenBeratungTeilnehmer extends Model
{
    protected $table = 'gruppen_beratung_teilnehmer';
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = null; // составной PK
    protected $fillable = ['gruppen_beratung_id','teilnehmer_id'];
}
