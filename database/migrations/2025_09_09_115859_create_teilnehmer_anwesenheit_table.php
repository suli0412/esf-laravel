<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('teilnehmer_anwesenheit', function (Blueprint $table) {
            $table->id('anwesenheit_id'); // BIGINT UNSIGNED AI
            // было: $table->unsignedInteger('teilnehmer_id');
            // нужно:
            // Вариант 1 (короткий, правильный):
            $table->foreignId('teilnehmer_id')
                  ->constrained(table: 'teilnehmer', column: 'Teilnehmer_id')
                  ->cascadeOnDelete();

            $table->date('datum');
            $table->enum('status', [
                'Anwesend',
                'Abwesend',
                'Abwesend Entschuldigt',
                'religiöser Feiertag',
            ]);
            $table->unsignedInteger('fehlminuten')->default(0);

            $table->unique(['teilnehmer_id','datum'], 'uq_anw_tn_datum');
            $table->index(['teilnehmer_id','datum'], 'idx_anwesenheit_tn_datum');

            // если используешь foreignId, отдельный $table->foreign(...) не нужен
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teilnehmer_anwesenheit');
    }
};
