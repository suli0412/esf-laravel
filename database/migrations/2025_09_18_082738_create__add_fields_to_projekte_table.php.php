<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('projekte', function (Blueprint $table) {
            // Nur anlegen, wenn noch nicht vorhanden
            if (!Schema::hasColumn('projekte', 'beschreibung')) {
                $table->text('beschreibung')->nullable()->after('bezeichnung');
            }
            if (!Schema::hasColumn('projekte', 'inhalte')) {
                $table->text('inhalte')->nullable()->after('beschreibung');
            }
            if (!Schema::hasColumn('projekte', 'verantwortlicher_id')) {
                // nur die Spalte anlegen – KEIN FK hier!
                $table->unsignedBigInteger('verantwortlicher_id')->nullable()->after('inhalte');
                // Optional: Index (performanter für spätere FK/Joins)
                // $table->index('verantwortlicher_id');
            }
        });

        // FK wird absichtlich NICHT hier gesetzt.
        // Das macht die separate Fix-Migration (Typ -> BIGINT UNSIGNED + FK setzen).
    }

    public function down(): void
    {
        // defensiv: erst FK (falls vorhanden) droppen,
        // dann Spalten in umgekehrter Reihenfolge entfernen
        try {
            Schema::table('projekte', function (Blueprint $table) {
                $table->dropForeign(['verantwortlicher_id']);
            });
        } catch (\Throwable $e) {
            // ignorieren (wenn es keinen FK gibt)
        }

        Schema::table('projekte', function (Blueprint $table) {
            if (Schema::hasColumn('projekte', 'verantwortlicher_id')) {
                $table->dropColumn('verantwortlicher_id');
            }
            if (Schema::hasColumn('projekte', 'inhalte')) {
                $table->dropColumn('inhalte');
            }
            if (Schema::hasColumn('projekte', 'beschreibung')) {
                $table->dropColumn('beschreibung');
            }
        });
    }
};
