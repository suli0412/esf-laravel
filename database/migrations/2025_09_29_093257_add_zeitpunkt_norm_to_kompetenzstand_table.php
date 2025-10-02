<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kompetenzstand', function (Blueprint $t) {
            if (!Schema::hasColumn('kompetenzstand', 'zeitpunkt_norm')) {
                $t->string('zeitpunkt_norm', 16)
                  ->virtualAs("LOWER(TRIM(`zeitpunkt`))")
                  ->after('zeitpunkt');
                // Index separat setzen (ist am zuverlässigsten)
                $t->index('zeitpunkt_norm');
            }
        });
    }

    public function down(): void
    {
        Schema::table('kompetenzstand', function (Blueprint $t) {
            if (Schema::hasColumn('kompetenzstand', 'zeitpunkt_norm')) {
                $t->dropIndex(['zeitpunkt_norm']); // Index entfernen (falls vorhanden)
                $t->dropColumn('zeitpunkt_norm');  // Spalte entfernen
            }
        });
    }
};

