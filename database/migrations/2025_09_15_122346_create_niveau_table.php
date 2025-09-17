<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('niveau', function (Blueprint $table) {
            $table->integer('niveau_id', true);
            $table->string('code', 20)->unique();
            $table->string('label', 50);
            $table->integer('sort_order');
        });
    }

    public function down(): void {
        Schema::dropIfExists('niveau');
    }
};
