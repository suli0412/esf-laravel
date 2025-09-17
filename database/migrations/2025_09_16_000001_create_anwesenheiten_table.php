<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('anwesenheiten')) return;

        // fehlende Spalten defensiv ergänzen
        Schema::table('anwesenheiten', function (Blueprint $table) {
            if (!Schema::hasColumn('anwesenheiten', 'teilnehmer_id')) {
                $table->unsignedInteger('teilnehmer_id')->after('id');
            }
            if (!Schema::hasColumn('anwesenheiten', 'gruppe_id')) {
                $table->unsignedInteger('gruppe_id')->after('teilnehmer_id');
            }
            if (!Schema::hasColumn('anwesenheiten', 'datum')) {
                $table->date('datum')->after('gruppe_id');
            }
            if (!Schema::hasColumn('anwesenheiten', 'status')) {
                $table->boolean('status')->default(false)->after('datum');
            }
            if (!Schema::hasColumn('anwesenheiten', 'bemerkung')) {
                $table->string('bemerkung', 500)->nullable()->after('status');
            }
        });

        // Unique-Index setzen – falls schon vorhanden, Fehler ignorieren
        try {
            Schema::table('anwesenheiten', function (Blueprint $table) {
                $table->unique(['teilnehmer_id','gruppe_id','datum'], 'anwesenheit_unique_tgd');
            });
        } catch (\Throwable $e) {
            // Index existiert bereits → ignorieren
        }

        // FKs anlegen – falls schon vorhanden, Fehler ignorieren
        try {
            Schema::table('anwesenheiten', function (Blueprint $table) {
                $table->foreign('teilnehmer_id')
                    ->references('Teilnehmer_id')->on('teilnehmer')
                    ->cascadeOnDelete();
            });
        } catch (\Throwable $e) { /* foreign key existiert bereits */ }

        try {
            Schema::table('anwesenheiten', function (Blueprint $table) {
                $table->foreign('gruppe_id')
                    ->references('gruppe_id')->on('gruppen')
                    ->cascadeOnDelete();
            });
        } catch (\Throwable $e) { /* foreign key existiert bereits */ }
    }

    public function down(): void
    {
        if (!Schema::hasTable('anwesenheiten')) return;

        // defensiv zurückbauen (Namen können variieren → try/catch)
        try {
            Schema::table('anwesenheiten', function (Blueprint $table) {
                $table->dropUnique('anwesenheit_unique_tgd');
            });
        } catch (\Throwable $e) {}

        try {
            Schema::table('anwesenheiten', function (Blueprint $table) {
                $table->dropForeign('anwesenheiten_teilnehmer_id_foreign');
            });
        } catch (\Throwable $e) {}

        try {
            Schema::table('anwesenheiten', function (Blueprint $table) {
                $table->dropForeign('anwesenheiten_gruppe_id_foreign');
            });
        } catch (\Throwable $e) {}
    }
};
