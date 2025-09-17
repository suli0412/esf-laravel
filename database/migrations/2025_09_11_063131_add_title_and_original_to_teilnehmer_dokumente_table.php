<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('teilnehmer_dokumente', function (Blueprint $table) {
            $table->string('titel')->nullable()->after('teilnehmer_id');
            $table->string('original_name')->nullable()->after('dokument_pfad');
        });
    }
    public function down(): void {
        Schema::table('teilnehmer_dokumente', function (Blueprint $table) {
            $table->dropColumn(['titel','original_name']);
        });
    }
};
