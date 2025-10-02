<?php

// database/migrations/xxxx_xx_xx_xxxxxx_add_unique_constraints_to_teilnehmer.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('teilnehmer', function (Blueprint $table) {
            // falls noch nicht vorhanden:
            $table->unique('SVN',   'teilnehmer_svn_unique');     // nur wenn SVN wirklich eindeutig sein soll
            $table->unique('Email', 'teilnehmer_email_unique');   // mehrere NULLs erlaubt (MySQL)
        });

        // Robuste, zusammengesetzte Eindeutigkeit (Name+Geburtsdatum) über generierte Spalte:
        Schema::table('teilnehmer', function (Blueprint $table) {
            // virtual column mit normalisierten Werten
            $table->string('unique_key')->virtualAs(
                "concat(lower(trim(Vorname)),'|',lower(trim(Nachname)),'|',ifnull(date_format(Geburtsdatum,'%Y-%m-%d'),'NULL'))"
            );
            $table->unique('unique_key', 'teilnehmer_unique_key_unique');
        });
    }

    public function down(): void
    {
        Schema::table('teilnehmer', function (Blueprint $table) {
            $table->dropUnique('teilnehmer_svn_unique');
            $table->dropUnique('teilnehmer_email_unique');
            $table->dropUnique('teilnehmer_unique_key_unique');
            $table->dropColumn('unique_key');
        });
    }
};
