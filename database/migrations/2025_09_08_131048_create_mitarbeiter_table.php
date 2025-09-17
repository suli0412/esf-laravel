<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('mitarbeiter')) {
            Schema::create('mitarbeiter', function (Blueprint $table) {
                $table->id('Mitarbeiter_id');
                $table->string('Nachname', 100);
                $table->string('Vorname', 100);
                $table->enum('Taetigkeit', ['Leitung','Verwaltung','Beratung','Bildung','Teamleitung','Praktikant','Andere']);
                $table->string('Email', 255)->unique();
                $table->string('Telefonnummer', 50)->nullable()->unique();
                $table->timestamps();
            });
        } else {
            // при необходимости можно добить недостающие поля/индексы
            Schema::table('mitarbeiter', function (Blueprint $table) {
                // примеры:
                if (! Schema::hasColumn('mitarbeiter','Telefonnummer')) {
                    $table->string('Telefonnummer', 50)->nullable()->after('Email');
                }
                // if needed: убедись, что уникальные индексы есть (Email/Telefonnummer)
                // Индексы лучше добавлять отдельной миграцией, чтобы не усложнять.
            });
        }
    }

    public function down(): void
    {
        // Удалять таблицу нельзя, если в проде уже есть данные.
        // Но down() обязан существовать — сделаем безопасно.
        if (Schema::hasTable('mitarbeiter')) {
            // Ничего не делаем, чтобы случайный rollback не снёс боевые данные.
            // Либо, если это ещё dev-этап: Schema::dropIfExists('mitarbeiter');
        }
    }
};



