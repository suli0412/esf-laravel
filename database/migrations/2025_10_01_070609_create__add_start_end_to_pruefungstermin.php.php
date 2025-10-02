<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('pruefungstermin', function (Blueprint $t) {
            $t->dateTime('start_at')->nullable()->after('datum')->index();
            $t->dateTime('end_at')->nullable()->after('start_at')->index();
        });

        // Bestehende Einträge übernehmen: start_at = datum 09:00, end_at = +2h
        $rows = DB::table('pruefungstermin')->whereNull('start_at')->get(['termin_id','datum']);
        foreach ($rows as $r) {
            if (!$r->datum) continue;
            $start = Carbon::parse($r->datum.' 09:00:00'); // Default-Startzeit
            $end   = (clone $start)->addHours(2);          // Default-Dauer 2h
            DB::table('pruefungstermin')->where('termin_id', $r->termin_id)
                ->update([
                    'start_at' => $start->toDateTimeString(),
                    'end_at'   => $end->toDateTimeString(),
                ]);
        }
    }

    public function down(): void
    {
        Schema::table('pruefungstermin', function (Blueprint $t) {
            $t->dropColumn(['start_at','end_at']);
        });
    }
};
