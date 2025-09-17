<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('kompetenzen', function (Blueprint $table) {
            $table->integer('kompetenz_id', true);
            $table->string('code', 20)->unique();
            $table->string('bezeichnung', 100);
        });
    }

    public function down(): void {
        Schema::dropIfExists('kompetenzen');
    }
};
