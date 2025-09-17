<?php

// database/migrations/xxxx_xx_xx_xxxxxx_make_bool_flags_nullable_with_default.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('teilnehmer', function (Blueprint $table) {
            // benötigt doctrine/dbal für change():
            // composer require doctrine/dbal
            $table->boolean('Minderheit')->default(0)->nullable()->change();
            $table->boolean('Behinderung')->default(0)->nullable()->change();
            $table->boolean('Obdachlos')->default(0)->nullable()->change();
            $table->boolean('LändlicheGebiete')->default(0)->nullable()->change();
            $table->boolean('ElternImAuslandGeboren')->default(0)->nullable()->change();
            $table->boolean('Armutsbetroffen')->default(0)->nullable()->change();
            $table->boolean('Armutsgefährdet')->default(0)->nullable()->change();
            $table->boolean('IDEA_Stammdatenblatt')->default(0)->nullable()->change();
            $table->boolean('IDEA_Dokumente')->default(0)->nullable()->change();
            $table->boolean('Clearing_gruppe')->default(0)->nullable()->change();
        });
    }

    public function down(): void
    {
        // ggf. zurücksetzen falls nötig
    }
};
