<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // 1) Снимаем возможные старые FK (если они есть)
        try { DB::statement('ALTER TABLE `gruppen` DROP FOREIGN KEY `gruppen_projekt_id_foreign`'); } catch (\Throwable $e) {}
        try { DB::statement('ALTER TABLE `gruppen` DROP FOREIGN KEY `gruppen_standard_mitarbeiter_id_foreign`'); } catch (\Throwable $e) {}
        try { DB::statement('ALTER TABLE `gruppen` DROP FOREIGN KEY `gruppen_std_mitarbeiter_fk`'); } catch (\Throwable $e) {}

        // 2) Приводим тип группового столбца к ТАКОМУ ЖЕ, как в projekte.projekt_id (INT UNSIGNED)
        DB::statement('ALTER TABLE `gruppen` MODIFY COLUMN `projekt_id` INT UNSIGNED NULL');

        // 3) standard_mitarbeiter_id должен быть BIGINT UNSIGNED (под Mitarbeiter_id)
        DB::statement('ALTER TABLE `gruppen` MODIFY COLUMN `standard_mitarbeiter_id` BIGINT UNSIGNED NULL');

        // 4) Навешиваем ключи заново
        Schema::table('gruppen', function (Blueprint $table) {
            $table->foreign('projekt_id')
                  ->references('projekt_id')->on('projekte')
                  ->nullOnDelete();

            $table->foreign('standard_mitarbeiter_id', 'gruppen_std_mitarbeiter_fk')
                  ->references('Mitarbeiter_id')->on('mitarbeiter')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('gruppen', function (Blueprint $table) {
            $table->dropForeign(['projekt_id']);
            $table->dropForeign(['standard_mitarbeiter_id']);
        });

        // Откат типов (необязательно, но аккуратно вернём как было)
        DB::statement('ALTER TABLE `gruppen` MODIFY COLUMN `projekt_id` BIGINT UNSIGNED NULL');
        DB::statement('ALTER TABLE `gruppen` MODIFY COLUMN `standard_mitarbeiter_id` BIGINT UNSIGNED NULL');
    }
};

