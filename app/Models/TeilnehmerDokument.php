<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeilnehmerDokument extends Model
{
    protected $table = 'teilnehmer_dokumente';
    protected $primaryKey = 'dokument_id';
    public $timestamps = true;

    protected $fillable = [
        'teilnehmer_id',
        'dokument_pfad',
        'typ',
        'titel',
        'original_name',
    ];

    // <-- ЭТА КОНСТАНТА НУЖНА ДЛЯ ВАШЕГО BLADE
    public const TYPEN = ['PDF', 'Foto', 'Sonstiges'];

    public function teilnehmer()
    {
        return $this->belongsTo(Teilnehmer::class, 'teilnehmer_id', 'Teilnehmer_id');
    }

    // Удобный геттер для отображения подписи файла
    public function getDisplayTitelAttribute(): string
    {
        return $this->titel ?: ($this->original_name ?: basename($this->dokument_pfad));
    }
}
