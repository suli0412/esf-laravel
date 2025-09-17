<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TeilnehmerProjekt extends Model
{
    use HasFactory;

    protected $table = 'teilnehmer_projekt';
    protected $primaryKey = 'Teilnehmer_Projekt_id';
    protected $fillable = ['teilnehmer_id','projekt_id','beginn','ende','status','anmerkung'];
    protected $casts = ['beginn'=>'date','ende'=>'date'];

    public function teilnehmer()
    {
        return $this->belongsTo(Teilnehmer::class, 'teilnehmer_id', 'Teilnehmer_id');
    }

    public function projekt()
    {
        return $this->belongsTo(Projekt::class, 'projekt_id', 'projekt_id');
    }
}
