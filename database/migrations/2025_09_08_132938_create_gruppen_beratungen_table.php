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
    Schema::create('gruppen_beratungen', function (Blueprint $table) {
        $table->increments('gruppen_beratung_id');

        $table->unsignedTinyInteger('art_id');
        $table->foreign('art_id')
              ->references('Art_id')->on('beratungsarten');

        $table->unsignedInteger('thema_id')->nullable();
        $table->foreign('thema_id')
              ->references('Thema_id')->on('beratungsthemen');

        $table->unsignedBigInteger('mitarbeiter_id')->nullable();
        $table->foreign('mitarbeiter_id')
              ->references('Mitarbeiter_id')->on('mitarbeiter')
              ->onDelete('set null');

        $table->date('datum');
        $table->decimal('dauer_h', 4, 2)->nullable();
        $table->string('thema', 255)->nullable();
        $table->text('inhalt')->nullable();
        $table->boolean('TNUnterlagen')->default(false);

        $table->index('datum', 'idx_gb_datum');
        $table->index(['mitarbeiter_id','datum'], 'idx_gb_ma_datum');

        $table->timestamps();
    });

    DB::statement("ALTER TABLE gruppen_beratungen
        ADD CONSTRAINT chk_gb_dauer CHECK (dauer_h IS NULL OR (dauer_h >= 0 AND dauer_h <= 24))");
}
public function down(): void
{
    Schema::dropIfExists('gruppen_beratungen');
}

};
