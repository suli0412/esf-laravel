<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class ReportsController extends Controller
{
    public function index(Request $request)
    {
        // Zeitraum: Default letzte 7 Tage inkl. heute
        $to   = $request->date_to   ? Carbon::parse($request->date_to)->endOfDay() : Carbon::today()->endOfDay();
        $from = $request->date_from ? Carbon::parse($request->date_from)->startOfDay() : Carbon::today()->subDays(6)->startOfDay();

        // KPIs (Beispiele — an deine Tabellen angepasst)
        $totalTeilnehmer = DB::table('teilnehmer')->count();
        $totalGruppen    = DB::table('gruppen')->count();
        $totalProjekte   = DB::table('Projekte')->count();

        // Neue Teilnehmer pro Tag
        $teilnehmerNeu = DB::table('teilnehmer')
            ->selectRaw('DATE(created_at) as d, COUNT(*) as cnt')
            ->whereBetween('created_at', [$from, $to])
            ->groupBy('d')->orderBy('d')->get()->keyBy('d');

        // Beratungen pro Tag
        $beratungen = DB::table('beratungen')
            ->selectRaw('DATE(datum) as d, COUNT(*) as cnt, COALESCE(SUM(dauer_h),0) as sumh')
            ->whereBetween('datum', [$from->toDateString(), $to->toDateString()])
            ->groupBy('d')->orderBy('d')->get()->keyBy('d');

        // Dokumente pro Tag
        $docs = DB::table('teilnehmer_dokumente')
            ->selectRaw('DATE(hochgeladen_am) as d, COUNT(*) as cnt')
            ->whereBetween('hochgeladen_am', [$from->toDateString(), $to->toDateString()])
            ->groupBy('d')->orderBy('d')->get()->keyBy('d');

        // Anwesenheit pro Tag
        $anw = DB::table('teilnehmer_anwesenheit')
            ->selectRaw('DATE(datum) as d,
                         SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) as present,
                         COUNT(*) as total')
            ->whereBetween('datum', [$from->toDateString(), $to->toDateString()])
            ->groupBy('d')->orderBy('d')->get()->keyBy('d');

        // Tage bauen
        $period = CarbonPeriod::create($from, $to);
        $rows = [];
        $sumPresent = 0; $sumTotal = 0;

        foreach ($period as $day) {
            $key = $day->toDateString();
            $newTn   = (int)($teilnehmerNeu[$key]->cnt ?? 0);
            $bCnt    = (int)($beratungen[$key]->cnt ?? 0);
            $bH      = (float)($beratungen[$key]->sumh ?? 0);
            $dCnt    = (int)($docs[$key]->cnt ?? 0);
            $present = (int)($anw[$key]->present ?? 0);
            $total   = (int)($anw[$key]->total ?? 0);
            $rate    = $total > 0 ? round($present / max(1,$total) * 100, 1) : null;

            $sumPresent += $present;
            $sumTotal   += $total;

            $rows[] = [
                'date' => $key,
                'new_participants' => $newTn,
                'consultations'    => $bCnt,
                'consultation_hours' => $bH,
                'docs_uploaded'    => $dCnt,
                'present'          => $present,
                'attendance_total' => $total,
                'attendance_rate'  => $rate,
            ];
        }

        $attendanceRateOverall = $sumTotal > 0 ? round($sumPresent / $sumTotal * 100, 1) : null;

        return view('reports.index', [
            'from' => $from->toDateString(),
            'to'   => $to->toDateString(),
            'kpis' => [
                'teilnehmer_total' => $totalTeilnehmer,
                'gruppen_total'    => $totalGruppen,
                'projekte_total'   => $totalProjekte,
                'anwesenheit_rate' => $attendanceRateOverall,
            ],
            'rows' => $rows,
        ]);
    }

    // (deine export()-Methode kann so bleiben)
}
