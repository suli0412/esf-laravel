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
    Schema::create('beratungsthemen', function (Blueprint $table) {
        $table->increments('Thema_id');                   // UNSIGNED INT AUTO_INCREMENT
        $table->string('Bezeichnung', 120)->unique();
        $table->text('Beschreibung')->nullable();
        $table->timestamps();
    });
}
public function down(): void
{
    Schema::dropIfExists('beratungsthemen');
}

};
