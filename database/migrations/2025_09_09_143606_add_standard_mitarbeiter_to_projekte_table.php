<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('projekte', function (Blueprint $table) {
            $table->unsignedBigInteger('standard_mitarbeiter_id')->nullable()->after('aktiv');
            $table->foreign('standard_mitarbeiter_id')
                  ->references('Mitarbeiter_id')->on('mitarbeiter')
                  ->onDelete('set null');
        });
    }
    public function down(): void {
        Schema::table('projekte', function (Blueprint $table) {
            $table->dropForeign(['standard_mitarbeiter_id']);
            $table->dropColumn('standard_mitarbeiter_id');
        });
    }
};


