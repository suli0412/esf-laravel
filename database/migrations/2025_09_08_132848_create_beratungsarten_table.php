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
    Schema::create('beratungsarten', function (Blueprint $table) {
        $table->tinyIncrements('Art_id');                 // UNSIGNED TINYINT AUTO_INCREMENT
        $table->char('Code', 3)->unique();
        $table->string('Bezeichnung', 50);
        $table->timestamps();
    });
}
public function down(): void
{
    Schema::dropIfExists('beratungsarten');
}

};
