<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('pruefungstermin', function (Blueprint $table) {
            $table->integer('termin_id', true);
            $table->integer('niveau_id');
            $table->date('datum');
            $table->string('institut', 100)->nullable();

            $table->index('datum', 'idx_pruefungstermin_datum');

            $table->foreign('niveau_id')
                  ->references('niveau_id')->on('niveau');
        });
    }

    public function down(): void {
        Schema::dropIfExists('pruefungstermin');
    }
};
