<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('teilnehmer_dokumente', function (Blueprint $table) {
            $table->bigIncrements('dokument_id');
            $table->unsignedBigInteger('teilnehmer_id');
            $table->text('dokument_pfad'); // путь в storage/app/public/…
            $table->enum('typ', ['PDF','Foto','Sonstiges'])->default('Sonstiges');
            $table->timestamp('hochgeladen_am')->useCurrent();
            $table->timestamps();

            $table->foreign('teilnehmer_id')
                ->references('Teilnehmer_id')->on('teilnehmer')
                ->onDelete('cascade');

            $table->index(['teilnehmer_id', 'hochgeladen_am'], 'idx_dok_tn_time');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teilnehmer_dokumente');
    }
};
