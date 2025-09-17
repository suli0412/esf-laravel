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
    Schema::create('teilnehmer_projekt', function (Blueprint $table) {
        $table->increments('Teilnehmer_Projekt_id');
        // FK на teilnehmer (BIGINT в вашей миграции)
        $table->unsignedBigInteger('teilnehmer_id');
        $table->foreign('teilnehmer_id')
              ->references('Teilnehmer_id')->on('teilnehmer')
              ->onDelete('cascade');

        // FK на projekte (INT)
        $table->unsignedInteger('projekt_id');
        $table->foreign('projekt_id')
              ->references('projekt_id')->on('projekte')
              ->onDelete('restrict');

        $table->date('beginn');
        $table->date('ende')->nullable();
        $table->enum('status', ['aktiv','abgeschlossen','abgebrochen','pausiert'])->default('aktiv');
        $table->text('anmerkung')->nullable();

        $table->index(['teilnehmer_id','beginn','ende'], 'idx_tp_tn_datum');
        $table->index(['projekt_id','beginn','ende'], 'idx_tp_proj_datum');
        $table->timestamps();
    });
}
public function down(): void
{
    Schema::dropIfExists('teilnehmer_projekt');
}

};
