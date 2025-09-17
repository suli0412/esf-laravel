<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('gruppen', function (Blueprint $table) {
            if (!Schema::hasColumn('gruppen', 'projekt_id')) {
                $table->unsignedBigInteger('projekt_id')->nullable()->after('code');
                $table->foreign('projekt_id')
                      ->references('projekt_id')->on('projekte')
                      ->nullOnDelete();
            }
            if (!Schema::hasColumn('gruppen', 'standard_mitarbeiter_id')) {
                $table->unsignedBigInteger('standard_mitarbeiter_id')->nullable()->after('projekt_id');
                $table->foreign('standard_mitarbeiter_id')
                      ->references('Mitarbeiter_id')->on('mitarbeiter')
                      ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('gruppen', function (Blueprint $table) {
            if (Schema::hasColumn('gruppen', 'standard_mitarbeiter_id')) {
                $table->dropForeign(['standard_mitarbeiter_id']);
                $table->dropColumn('standard_mitarbeiter_id');
            }
            if (Schema::hasColumn('gruppen', 'projekt_id')) {
                $table->dropForeign(['projekt_id']);
                $table->dropColumn('projekt_id');
            }
        });
    }
};
