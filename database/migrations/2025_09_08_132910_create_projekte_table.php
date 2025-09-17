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
    Schema::create('projekte', function (Blueprint $table) {
        $table->increments('projekt_id');                 // UNSIGNED INT
        $table->string('code', 30)->unique();
        $table->string('bezeichnung', 150);
        $table->date('start')->nullable();
        $table->date('ende')->nullable();
        $table->boolean('aktiv')->default(true);
        $table->timestamps();
    });
}
public function down(): void
{
    Schema::dropIfExists('projekte');
}

};
