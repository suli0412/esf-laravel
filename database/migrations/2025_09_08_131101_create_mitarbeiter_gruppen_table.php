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
    Schema::create('mitarbeiter_gruppen', function (Blueprint $table) {
        $table->id('gruppe_id');
        $table->string('bezeichnung', 100)->unique();
        $table->timestamps();
    });
}
public function down(): void
{
    Schema::dropIfExists('mitarbeiter_gruppen');
}

};
