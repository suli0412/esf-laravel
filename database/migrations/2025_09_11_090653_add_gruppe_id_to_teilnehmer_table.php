<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasColumn('teilnehmer', 'gruppe_id')) {
            Schema::table('teilnehmer', function (Blueprint $table) {
                $table->unsignedBigInteger('gruppe_id')->nullable()->after('Teilnehmer_id');
                $table->foreign('gruppe_id')
                      ->references('gruppe_id')->on('gruppen')
                      ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('teilnehmer', 'gruppe_id')) {
            Schema::table('teilnehmer', function (Blueprint $table) {
                // имя внешнего ключа по умолчанию: teilnehmer_gruppe_id_foreign
                $table->dropForeign(['gruppe_id']);
                $table->dropColumn('gruppe_id');
            });
        }
    }
};
