<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('teilnehmer', function (Blueprint $table) {
            // Eintritt
            $table->string('de_lesen_in', 10)->nullable()->after('SVN');
            $table->string('de_hoeren_in', 10)->nullable()->after('de_lesen_in');
            $table->string('de_schreiben_in', 10)->nullable()->after('de_hoeren_in');
            $table->string('de_sprechen_in', 10)->nullable()->after('de_schreiben_in');
            $table->string('en_in', 15)->nullable()->after('de_sprechen_in');
            $table->string('ma_in', 5)->nullable()->after('en_in');

            // Ausstieg (beim Neuanlegen leer – später editierbar)
            $table->string('de_lesen_out', 10)->nullable()->after('ma_in');
            $table->string('de_hoeren_out', 10)->nullable()->after('de_lesen_out');
            $table->string('de_schreiben_out', 10)->nullable()->after('de_hoeren_out');
            $table->string('de_sprechen_out', 10)->nullable()->after('de_schreiben_out');
            $table->string('en_out', 15)->nullable()->after('de_sprechen_out');
            $table->string('ma_out', 5)->nullable()->after('en_out');
        });
    }

    public function down(): void
    {
        Schema::table('teilnehmer', function (Blueprint $table) {
            $table->dropColumn([
                'de_lesen_in','de_hoeren_in','de_schreiben_in','de_sprechen_in','en_in','ma_in',
                'de_lesen_out','de_hoeren_out','de_schreiben_out','de_sprechen_out','en_out','ma_out',
            ]);
        });
    }
};
