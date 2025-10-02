<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Teilnehmer
        Schema::table('teilnehmer', function (Blueprint $t) {
            if (!Schema::hasColumn('teilnehmer', 'created_by')) {
                $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('teilnehmer', 'updated_by')) {
                $t->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            }
        });

        // TeilnehmerAnwesenheit
        Schema::table('teilnehmer_anwesenheit', function (Blueprint $t) {
            if (!Schema::hasColumn('teilnehmer_anwesenheit', 'created_by')) {
                $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('teilnehmer_anwesenheit', 'updated_by')) {
                $t->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('teilnehmer', function (Blueprint $t) {
            if (Schema::hasColumn('teilnehmer', 'created_by')) {
                $t->dropConstrainedForeignId('created_by');
            }
            if (Schema::hasColumn('teilnehmer', 'updated_by')) {
                $t->dropConstrainedForeignId('updated_by');
            }
        });

        Schema::table('teilnehmer_anwesenheit', function (Blueprint $t) {
            if (Schema::hasColumn('teilnehmer_anwesenheit', 'created_by')) {
                $t->dropConstrainedForeignId('created_by');
            }
            if (Schema::hasColumn('teilnehmer_anwesenheit', 'updated_by')) {
                $t->dropConstrainedForeignId('updated_by');
            }
        });
    }
};
