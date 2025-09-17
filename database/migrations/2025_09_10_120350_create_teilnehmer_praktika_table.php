<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('teilnehmer_praktika', function (Blueprint $table) {
            $table->bigIncrements('praktikum_id');

            // ВАЖНО: тип и unsigned должны совпадать с teilnehmer.Teilnehmer_id (bigint unsigned)
            $table->unsignedBigInteger('teilnehmer_id');

            $table->string('bereich', 120);
            $table->string('land', 100)->nullable();
            $table->string('firma', 150)->nullable();
            $table->decimal('stunden_ausmass', 5, 2)->nullable(); // часы (например, в неделю)
            $table->text('anmerkung')->nullable();
            $table->date('beginn')->nullable();
            $table->date('ende')->nullable();
            $table->timestamps();

            $table->foreign('teilnehmer_id')
                  ->references('Teilnehmer_id')->on('teilnehmer')
                  ->onDelete('cascade');

            $table->index(['teilnehmer_id', 'beginn', 'ende']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teilnehmer_praktika');
    }
};
