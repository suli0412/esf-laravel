<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        foreach (['gruppen','pruefungstermin'] as $table) {
            Schema::table($table, function (Blueprint $t) use ($table) {
                if (!Schema::hasColumn($table, 'created_by')) $t->unsignedBigInteger('created_by')->nullable()->after('updated_at');
                if (!Schema::hasColumn($table, 'updated_by')) $t->unsignedBigInteger('updated_by')->nullable()->after('created_by');
            });
        }
    }

    public function down(): void
    {
        foreach (['gruppen','pruefungstermin'] as $table) {
            Schema::table($table, function (Blueprint $t) use ($table) {
                if (Schema::hasColumn($table, 'updated_by')) $t->dropColumn('updated_by');
                if (Schema::hasColumn($table, 'created_by')) $t->dropColumn('created_by');
            });
        }
    }
};
