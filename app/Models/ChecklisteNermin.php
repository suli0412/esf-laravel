<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ChecklisteNermin extends Model
{
    use HasFactory;

    protected $table = 'checkliste_nermin';
    protected $primaryKey = 'Checkliste_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'Teilnehmer_id',
        'AMS_Bericht',
        'AMS_Lebenslauf',
        'Erwerbsstatus',
        'VorzeitigerAustritt',
        'IDEA',
    ];

    public function teilnehmer()
    {
        return $this->belongsTo(Teilnehmer::class, 'Teilnehmer_id', 'Teilnehmer_id');
    }
}
