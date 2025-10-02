<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('pruefungstermin', function (Blueprint $t) {
            if (!Schema::hasColumn('pruefungstermin','created_by')) {
                $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('pruefungstermin','updated_by')) {
                $t->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            }
        });
    }
    public function down(): void
    {
        Schema::table('pruefungstermin', function (Blueprint $t) {
            if (Schema::hasColumn('pruefungstermin','updated_by')) {
                $t->dropConstrainedForeignId('updated_by');
            }
            if (Schema::hasColumn('pruefungstermin','created_by')) {
                $t->dropConstrainedForeignId('created_by');
            }
        });
    }
};
