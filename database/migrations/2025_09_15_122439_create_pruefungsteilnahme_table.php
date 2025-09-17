<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('pruefungsteilnahme')) {
            Schema::drop('pruefungsteilnahme');
        }

        $col = DB::selectOne("
            SELECT DATA_TYPE, COLUMN_TYPE
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'teilnehmer'
              AND COLUMN_NAME = 'Teilnehmer_id'
            LIMIT 1
        ");

        $dataType   = $col->DATA_TYPE   ?? 'int';
        $columnType = strtolower($col->COLUMN_TYPE ?? 'int');

        $isBig      = str_contains($dataType, 'big');
        $isUnsigned = str_contains($columnType, 'unsigned');

        Schema::create('pruefungsteilnahme', function (Blueprint $table) use ($isBig, $isUnsigned) {
            $table->integer('termin_id');

            if ($isBig && $isUnsigned)       $table->unsignedBigInteger('teilnehmer_id');
            elseif ($isBig && !$isUnsigned)  $table->bigInteger('teilnehmer_id');
            elseif (!$isBig && $isUnsigned)  $table->unsignedInteger('teilnehmer_id');
            else                              $table->integer('teilnehmer_id');

            $table->boolean('bestanden')->nullable();
            $table->boolean('selbstzahler')->default(false);

            $table->primary(['termin_id','teilnehmer_id']);

            $table->foreign('termin_id')
                  ->references('termin_id')->on('pruefungstermin')
                  ->cascadeOnDelete();

            $table->foreign('teilnehmer_id')
                  ->references('Teilnehmer_id')->on('teilnehmer')
                  ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pruefungsteilnahme');
    }
};
