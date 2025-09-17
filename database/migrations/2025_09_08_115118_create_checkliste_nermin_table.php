<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('checkliste_nermin', function (Blueprint $table) {
            $table->id('Checkliste_id');

            // FK 1:1 к teilnehmer(Teilnehmer_id)
            $table->unsignedBigInteger('Teilnehmer_id');
            $table->foreign('Teilnehmer_id')
                ->references('Teilnehmer_id')->on('teilnehmer')
                ->onDelete('cascade');

            $table->enum('AMS_Bericht', ['Gesendet','Nicht gesendet'])->nullable();
            $table->enum('AMS_Lebenslauf', ['Gesendet','Nicht gesendet'])->nullable();
            $table->string('Erwerbsstatus', 150)->nullable();
            $table->enum('VorzeitigerAustritt', ['Ja','Nein'])->nullable();
            $table->enum('IDEA', ['Gesendet','Nicht gesendet','k. VD/g. AW','offen'])->nullable();

            $table->timestamps();

            // 1:1 — один чек-лист на Teilnehmer
            $table->unique('Teilnehmer_id', 'uq_checkliste_tn');
            $table->index('Teilnehmer_id', 'idx_checkliste_teilnehmer');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('checkliste_nermin');
    }
};
