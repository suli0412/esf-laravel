<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('teilnehmer_kenntnisse', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('teilnehmer_id');
            // GER уровни
            $t->string('de_lesen',     10)->nullable();
            $t->string('de_hoeren',    10)->nullable();
            $t->string('de_schreiben', 10)->nullable();
            $t->string('de_sprechen',  10)->nullable();
            $t->string('en_gesamt',    10)->nullable();
            $t->string('mathe',        10)->nullable();
            $t->string('ikt',          10)->nullable(); // цифровые компетенции (по желанию)
            $t->timestamps();

            $t->foreign('teilnehmer_id')
              ->references('Teilnehmer_id')->on('teilnehmer')
              ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teilnehmer_kenntnisse');
    }
};
