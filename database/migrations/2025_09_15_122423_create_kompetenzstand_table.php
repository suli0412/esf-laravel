<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Falls vom Fehlversuch schon vorhanden, aufräumen
        if (Schema::hasTable('kompetenzstand')) {
            Schema::drop('kompetenzstand');
        }

        // Typ der PK in 'teilnehmer.Teilnehmer_id' ermitteln
        $col = DB::selectOne("
            SELECT DATA_TYPE, COLUMN_TYPE
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'teilnehmer'
              AND COLUMN_NAME = 'Teilnehmer_id'
            LIMIT 1
        ");

        // Fallbacks, falls nichts gefunden
        $dataType   = $col->DATA_TYPE   ?? 'int';
        $columnType = strtolower($col->COLUMN_TYPE ?? 'int');

        $isBig      = str_contains($dataType, 'big');
        $isUnsigned = str_contains($columnType, 'unsigned');

        Schema::create('kompetenzstand', function (Blueprint $table) use ($isBig, $isUnsigned) {
            // teilnehmer_id typgleich erzeugen
            if ($isBig && $isUnsigned)       $table->unsignedBigInteger('teilnehmer_id');
            elseif ($isBig && !$isUnsigned)  $table->bigInteger('teilnehmer_id');
            elseif (!$isBig && $isUnsigned)  $table->unsignedInteger('teilnehmer_id');
            else                              $table->integer('teilnehmer_id');

            $table->enum('zeitpunkt', ['Eintritt','Austritt']);
            $table->integer('kompetenz_id');
            $table->integer('niveau_id');
            $table->date('datum')->nullable();
            $table->text('bemerkung')->nullable();

            // Composite PK
            $table->primary(['teilnehmer_id','zeitpunkt','kompetenz_id']);

            // FKs
            $table->foreign('teilnehmer_id')
                  ->references('Teilnehmer_id')->on('teilnehmer')
                  ->cascadeOnDelete();

            $table->foreign('kompetenz_id')
                  ->references('kompetenz_id')->on('kompetenzen');

            $table->foreign('niveau_id')
                  ->references('niveau_id')->on('niveau');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kompetenzstand');
    }
};
