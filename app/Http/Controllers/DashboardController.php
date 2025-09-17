<?php


// app/Http/Controllers/DashboardController.php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Carbon;

use App\Models\Teilnehmer;
use App\Models\Gruppe;
use App\Models\Projekt;
use App\Models\Pruefungstermin;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();
        $weekStart = Carbon::now()->startOfWeek();
        $weekEnd   = Carbon::now()->endOfWeek();

        // Basic counts
        $counts = [
            'teilnehmer' => Teilnehmer::count(),
            'gruppen'    => Gruppe::count(),
            'projekte'   => Projekt::count(),
        ];

        // Nächste Prüfungstermine (max 4)
        $nextPruef = Pruefungstermin::query()
            ->whereDate('datum', '>=', $today)
            ->orderBy('datum')
            ->limit(4)
            ->get(['termin_id','datum','institut','bezeichnung','titel','niveau_id']);

        // Letzte Aktivitäten
        $recent = [
            'teilnehmer' => Teilnehmer::orderByDesc('created_at')->limit(5)->get(['Teilnehmer_id','Nachname','Vorname','created_at']),

            'beratungen' => Schema::hasTable('beratungen')
                ? DB::table('beratungen')
                    ->orderByDesc('datum')->limit(5)
                    ->get(['beratung_id','teilnehmer_id','mitarbeiter_id','datum','dauer_h'])
                : collect(),

            // <-- hier ersetzen:
            'uploads' => Schema::hasTable('teilnehmer_dokumente')
                ? (function () {
                    // Dynamische Sortierspalte wählen
                    $orderCol = Schema::hasColumn('teilnehmer_dokumente','hochgeladen_am')
                        ? 'hochgeladen_am'
                        : (Schema::hasColumn('teilnehmer_dokumente','created_at') ? 'created_at' : 'dokument_id');

                    // Keine harte Spaltenliste -> vermeidet "Unknown column"
                    return DB::table('teilnehmer_dokumente')
                        ->orderByDesc($orderCol)
                        ->limit(5)
                        ->get();
                })()
                : collect(),
        ];
        // Anwesenheit: heute + Wochenverlauf (optional, falls Tabelle existiert)
        $anwToday = ['present' => 0, 'absent' => 0];
        $anwWeek  = [];

        if (Schema::hasTable('teilnehmer_anwesenheit')) {
            $todayRow = DB::table('teilnehmer_anwesenheit')
                ->selectRaw("SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) as present")
                ->selectRaw("SUM(CASE WHEN status = 0 THEN 1 ELSE 0 END) as absent")
                ->whereDate('datum', $today)
                ->first();
            if ($todayRow) {
                $anwToday['present'] = (int) $todayRow->present;
                $anwToday['absent']  = (int) $todayRow->absent;
            }

            $weekRows = DB::table('teilnehmer_anwesenheit')
                ->selectRaw("DATE(datum) as d")
                ->selectRaw("SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) as present")
                ->selectRaw("COUNT(*) as total")
                ->whereBetween('datum', [$weekStart->toDateString(), $weekEnd->toDateString()])
                ->groupBy('d')
                ->orderBy('d')
                ->get();

            $days = collect(range(0,6))->map(fn($i) => $weekStart->copy()->addDays($i)->toDateString());
            $map  = $weekRows->keyBy('d');
            $anwWeek = $days->map(function ($d) use ($map) {
                $row = $map->get($d);
                $present = $row ? (int)$row->present : 0;
                $total   = $row ? (int)$row->total   : 0;
                $pct     = $total > 0 ? round($present * 100 / $total) : 0;
                return ['date' => $d, 'present' => $present, 'total' => $total, 'pct' => $pct];
            })->all();
        }

        // Für View / Rollen-Checks (funktioniert auch ohne Spatie)
        $user = auth()->user();
        $isAdmin = $user && method_exists($user, 'hasRole') ? $user->hasRole('admin') : false;

        return view('dashboard', compact('counts','nextPruef','recent','anwToday','anwWeek','isAdmin'));
    }
}
