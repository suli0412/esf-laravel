<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Wenn die Tabelle bereits existiert → nichts tun
        if (Schema::hasTable('teilnehmer_praktika')) {
            return;
        }

        Schema::create('teilnehmer_praktika', function (Blueprint $table) {
            $table->integer('praktikum_id', true);
            $table->integer('teilnehmer_id');
            $table->string('bereich', 100)->nullable();
            $table->string('firma', 150)->nullable();
            $table->string('land', 100)->nullable();
            $table->date('beginn')->nullable();
            $table->date('ende')->nullable();
            $table->decimal('stunden_ausmass', 5, 2)->nullable();
            $table->text('anmerkung')->nullable();
            $table->timestamps();

            $table->foreign('teilnehmer_id')
                  ->references('Teilnehmer_id')->on('teilnehmer')
                  ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        // Vorsichtig: Nicht automatisch droppen, wenn die Tabelle evtl. schon vorher existierte
        // -> Down absichtlich leer lassen, damit ein Rollback hier nichts kaputt macht.
        // Wenn du wirklich droppen willst, kommentiere die folgende Zeile ein:
        // Schema::dropIfExists('teilnehmer_praktika');
    }
};
