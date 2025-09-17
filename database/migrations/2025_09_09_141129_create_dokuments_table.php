<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('dokumente', function (Blueprint $table) {
            $table->id();
            $table->string('name');              // Anzeigename
            $table->string('slug')->unique();    // Kurzname/Technik
            $table->text('body');                // HTML mit Platzhaltern
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('dokumente');
    }
};
