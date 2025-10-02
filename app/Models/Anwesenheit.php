<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\Blameable;
class TeilnehmerAnwesenheit extends Model
{
    use Blameable;
    protected $table = 'teilnehmer_anwesenheit';
    protected $primaryKey = 'anwesenheit_id';   // falls so in DB
    public $timestamps = false;                 // falls keine timestamps
    protected $fillable = ['teilnehmer_id','datum','status','fehlminuten'];
    protected $casts = ['datum' => 'date'];

    public const STATI = [
        'anwesend','anwesend_verspaetet','abwesend','entschuldigt','religiöser_feiertag'
    ];

    public function teilnehmer()
    {
        return $this->belongsTo(Teilnehmer::class, 'teilnehmer_id', 'Teilnehmer_id');
    }
}
