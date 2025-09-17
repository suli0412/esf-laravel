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
    Schema::create('beratungen', function (Blueprint $table) {
        $table->increments('beratung_id');

        $table->unsignedTinyInteger('art_id');
        $table->foreign('art_id')
              ->references('Art_id')->on('beratungsarten');

        $table->unsignedInteger('thema_id');
        $table->foreign('thema_id')
              ->references('Thema_id')->on('beratungsthemen');

        $table->unsignedBigInteger('teilnehmer_id');
        $table->foreign('teilnehmer_id')
              ->references('Teilnehmer_id')->on('teilnehmer')
              ->onDelete('cascade');

        $table->unsignedBigInteger('mitarbeiter_id')->nullable();
        $table->foreign('mitarbeiter_id')
              ->references('Mitarbeiter_id')->on('mitarbeiter')
              ->onDelete('set null');

        $table->date('datum');
        $table->decimal('dauer_h', 4, 2)->nullable();
        $table->text('notizen')->nullable();

        $table->index(['teilnehmer_id','datum'], 'idx_beratungen_tn_datum');
        $table->index(['mitarbeiter_id','datum'], 'idx_beratungen_ma_datum');
        $table->index(['art_id','thema_id'], 'idx_beratungen_art_thema');

        $table->timestamps();
    });

    // Необязательная CHECK-валидация для MySQL 8+:
    DB::statement("ALTER TABLE beratungen
        ADD CONSTRAINT chk_ber_dauer CHECK (dauer_h IS NULL OR (dauer_h >= 0 AND dauer_h <= 24))");
}
public function down(): void
{
    Schema::dropIfExists('beratungen');
}

};
