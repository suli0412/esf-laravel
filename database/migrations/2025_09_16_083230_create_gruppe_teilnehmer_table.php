<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('gruppe_teilnehmer', function (Blueprint $table) {
            $table->id();
            // Achtung: abweichende PK-Namen in deinen Tabellen
            $table->unsignedBigInteger('gruppe_id');
            $table->unsignedBigInteger('teilnehmer_id');

            // optionale Felder laut deinem Konzept
            $table->date('beitritt_von')->nullable();
            $table->date('beitritt_bis')->nullable();

            $table->timestamps();

            // FKs (Tabellen + PKs anpassen, falls bei dir INT statt BIGINT)
            $table->foreign('gruppe_id')
                  ->references('gruppe_id')->on('gruppen')
                  ->cascadeOnDelete();

            $table->foreign('teilnehmer_id')
                  ->references('Teilnehmer_id')->on('teilnehmer')
                  ->cascadeOnDelete();

            $table->unique(['gruppe_id','teilnehmer_id'], 'uniq_gruppe_teilnehmer');
        });
    }

    public function down(): void {
        Schema::dropIfExists('gruppe_teilnehmer');
    }
};
