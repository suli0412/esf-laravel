<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('niveau')) {
            Schema::create('niveau', function (Blueprint $table) {
                $table->id('niveau_id');
                $table->string('code', 16)->unique();
                $table->string('label', 120)->nullable();
                $table->unsignedSmallInteger('sort_order')->default(0);
            });
            return;
        }

        // Tabelle existiert bereits -> nur fehlende Spalten ergänzen
        Schema::table('niveau', function (Blueprint $table) {
            if (!Schema::hasColumn('niveau', 'code')) {
                $table->string('code', 16)->after('niveau_id');
            }
            if (!Schema::hasColumn('niveau', 'label')) {
                $table->string('label', 120)->nullable()->after('code');
            }
            if (!Schema::hasColumn('niveau', 'sort_order')) {
                $table->unsignedSmallInteger('sort_order')->default(0)->after('label');
            }
        });

        // Optional: unique Index auf code sicherstellen (Best-effort)
        try {
            DB::statement('CREATE UNIQUE INDEX niveau_code_unique ON niveau (code)');
        } catch (\Throwable $e) {
            // already exists -> ignorieren
        }
    }

    public function down(): void
    {
        // Kein Drop der ganzen Tabelle (Daten schützen)
        if (Schema::hasTable('niveau') && Schema::hasColumn('niveau', 'sort_order')) {
            Schema::table('niveau', function (Blueprint $table) {
                $table->dropColumn('sort_order');
            });
        }
    }
};
