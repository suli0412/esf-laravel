<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\LogOptions;

class TeilnehmerDokument extends Model
{
    use LogsActivity;

    protected $table = 'teilnehmer_dokumente';
    protected $primaryKey = 'dokument_id';
    public $timestamps = true;

    protected $fillable = [
        'teilnehmer_id',
        'dokument_pfad',
        'typ',
        'titel',
        'original_name',
        'hochgeladen_am',   // <- wichtig: wird im Controller gesetzt
    ];

    protected $casts = [
        'hochgeladen_am' => 'datetime',
    ];

    /**
     * Für Blade: Fallback, falls keine Konfig vorhanden.
     * Empfohlen: config('dokumente.teilnehmer_types') = ['PDF','Foto','Sonstiges']
     */
    public const TYPEN = ['PDF', 'Foto', 'Sonstiges'];

    public static function types(): array
    {
        return config('dokumente.teilnehmer_types', self::TYPEN);
    }

    /* =======================
     * Relationships
     * ======================= */
    public function teilnehmer()
    {
        return $this->belongsTo(Teilnehmer::class, 'teilnehmer_id', 'Teilnehmer_id');
    }

    /* =======================
     * Accessors / Helper
     * ======================= */

    /** Anzeige-Titel (Titel > Originalname > Dateiname) */
    public function getDisplayTitelAttribute(): string
    {
        return $this->titel
            ?: ($this->original_name ?: basename((string)$this->dokument_pfad));
    }

    /** Öffentliche URL (erfordert: php artisan storage:link) */
    public function getUrlAttribute(): string
    {
        return Storage::disk('public')->url((string)$this->dokument_pfad);
    }

    /** Einfache Bild-Erkennung anhand der Endung (ohne MIME in DB) */
    public function getIsImageAttribute(): bool
    {
        $ext = strtolower(pathinfo((string)$this->dokument_pfad, PATHINFO_EXTENSION));
        return in_array($ext, ['jpg','jpeg','png','gif','webp','bmp'], true);
    }

    /* =======================
     * Scopes
     * ======================= */
    public function scopeOfTeilnehmer($q, int $teilnehmerId)
    {
        return $q->where('teilnehmer_id', $teilnehmerId);
    }

    /* =======================
     * Storage-Hygiene: Datei mitlöschen
     * ======================= */
    protected static function booted(): void
    {
        static::deleting(function (self $doc) {
            if ($doc->dokument_pfad && Storage::disk('public')->exists($doc->dokument_pfad)) {
                Storage::disk('public')->delete($doc->dokument_pfad);
            }
        });
    }

    /* =======================
     * Activity Log
     * ======================= */

    // Nur echte Änderungen loggen, leere Logs vermeiden
    protected static $recordEvents = ['created', 'updated', 'deleted'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('teilnehmer_dokument') // konsistenter Log-Name
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function getDescriptionForEvent(string $eventName): string
    {
        return "Teilnehmer-Dokument {$eventName}";
    }

    public function tapActivity(Activity $activity, string $eventName): void
    {
        if (app()->runningInConsole()) return;

        $activity->properties = $activity->properties->merge([
            'ip'         => request()->ip(),
            'url'        => request()->fullUrl(),
            'route'      => optional(request()->route())->getName(),
            'user_agent' => mb_substr((string)request()->userAgent(), 0, 255),
        ]);
    }
}
