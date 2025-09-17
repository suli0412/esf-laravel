<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('pruefungstermin') && !Schema::hasColumn('pruefungstermin', 'bezeichnung')) {
            Schema::table('pruefungstermin', function (Blueprint $table) {
                $table->string('bezeichnung', 150)->nullable()->after('niveau_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('pruefungstermin') && Schema::hasColumn('pruefungstermin', 'bezeichnung')) {
            Schema::table('pruefungstermin', function (Blueprint $table) {
                $table->dropColumn('bezeichnung');
            });
        }
    }
};
