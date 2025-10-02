<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\LogOptions;

use App\Models\Traits\Blameable;


class TeilnehmerAnwesenheit extends Model
{
    use LogsActivity;
    use Blameable;

    protected $table = 'teilnehmer_anwesenheit';
    protected $primaryKey = 'anwesenheit_id';
    public $timestamps = true;

    protected $fillable = ['teilnehmer_id','datum','status','fehlminuten'];

    protected $casts = [
        'datum'       => 'date',
        'fehlminuten' => 'integer',
        // Wenn 'status' numerisch gespeichert wird, optional aktivieren:
        // 'status'      => 'integer',
    ];




    public const STATI = [
        'anwesend',
        'anwesend_verspaetet',
        'abwesend',
        'entschuldigt',
        'religioeser_feiertag',
    ];

    /** ---------- Activitylog-Konfiguration ---------- */
    protected static $recordEvents = ['created','updated','deleted'];
    protected static $logName = 'anwesenheit';
    protected static $logAttributes = ['*'];     // alle Felder loggen
    protected static $logOnlyDirty = true;       // nur tatsächliche Änderungen
    protected static $submitEmptyLogs = false;
    protected static $logAttributesToIgnore = [
        // ggf. Felder ausschließen
    ];

    public function getActivitylogOptions(): LogOptions
    {
    return LogOptions::defaults()
        ->useLogName('beratung')
        ->logAll()
        ->logOnlyDirty()
        ->dontSubmitEmptyLogs();
    }

    public function getDescriptionForEvent(string $eventName): string
    {
        return "Anwesenheit {$eventName}";
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
    /** ---------- /Activitylog ---------- */

    public function teilnehmer()
    {
        return $this->belongsTo(Teilnehmer::class, 'teilnehmer_id', 'Teilnehmer_id');
    }

    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
    public function updater() { return $this->belongsTo(User::class, 'updated_by'); }
}
