<?php

// database/migrations/2025_09_15_130000_add_bezeichnung_to_pruefungstermin.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up() {
    Schema::table('Pruefungstermin', function (Blueprint $t) {
      if (!Schema::hasColumn('Pruefungstermin', 'bezeichnung')) {
        $t->string('bezeichnung', 100)->nullable()->after('niveau_id');
      }
    });
  }
  public function down() {
    Schema::table('Pruefungstermin', function (Blueprint $t) {
      if (Schema::hasColumn('Pruefungstermin', 'bezeichnung')) {
        $t->dropColumn('bezeichnung');
      }
    });
  }
};
