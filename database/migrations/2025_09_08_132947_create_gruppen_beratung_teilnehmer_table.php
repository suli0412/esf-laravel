<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up(): void
{
    Schema::create('gruppen_beratung_teilnehmer', function (Blueprint $table) {
        $table->unsignedInteger('gruppen_beratung_id');
        $table->foreign('gruppen_beratung_id')
              ->references('gruppen_beratung_id')->on('gruppen_beratungen')
              ->onDelete('cascade');

        $table->unsignedBigInteger('teilnehmer_id');
        $table->foreign('teilnehmer_id')
              ->references('Teilnehmer_id')->on('teilnehmer')
              ->onDelete('cascade');

        $table->primary(['gruppen_beratung_id','teilnehmer_id']);
        // $table->timestamps(); // если нужны отметки времени участия
    });
}
public function down(): void
{
    Schema::dropIfExists('gruppen_beratung_teilnehmer');
}

};
