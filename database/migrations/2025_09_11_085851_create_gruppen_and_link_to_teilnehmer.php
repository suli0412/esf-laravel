<?php

// database/migrations/2025_09_12_100000_create_gruppen_and_link_to_teilnehmer.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('gruppen', function (Blueprint $t) {
            $t->bigIncrements('gruppe_id');
            $t->string('name', 150);
            $t->string('code', 50)->nullable()->unique();
            $t->boolean('aktiv')->default(true);
            $t->timestamps();
        });

        Schema::table('teilnehmer', function (Blueprint $t) {
            $t->unsignedBigInteger('gruppe_id')->nullable()->after('Clearing_gruppe');
            $t->foreign('gruppe_id')->references('gruppe_id')->on('gruppen')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('teilnehmer', function (Blueprint $t) {
            $t->dropForeign(['gruppe_id']);
            $t->dropColumn('gruppe_id');
        });
        Schema::dropIfExists('gruppen');
    }
};
