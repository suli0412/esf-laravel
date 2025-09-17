<?php

// database/migrations/xxxx_xx_xx_xxxxxx_create_gruppe_mitarbeiter_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('gruppe_mitarbeiter', function (Blueprint $table) {
            $table->unsignedBigInteger('gruppe_id');
            $table->unsignedBigInteger('mitarbeiter_id');
            $table->enum('rolle', ['Leitung','Trainer','Sozialpädagogik','Andere'])->nullable();

            $table->primary(['gruppe_id','mitarbeiter_id']);
            $table->foreign('gruppe_id')->references('gruppe_id')->on('gruppen')->cascadeOnDelete();
            $table->foreign('mitarbeiter_id')->references('Mitarbeiter_id')->on('mitarbeiter')->cascadeOnDelete();
        });
    }
    public function down(): void {
        Schema::dropIfExists('gruppe_mitarbeiter');
    }
};

