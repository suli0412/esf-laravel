<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // 1) Spalte sicherstellen
        if (!Schema::hasColumn('projekte', 'verantwortlicher_id')) {
            Schema::table('projekte', function (Blueprint $table) {
                $table->unsignedBigInteger('verantwortlicher_id')->nullable()->after('inhalte');
            });
        }

        // 2) vorhandene FKs auf der Spalte entfernen (falls vorhanden)
        $this->dropFkIfExists('projekte', 'verantwortlicher_id');

        // 3) Spaltentyp KORRIGIEREN → BIGINT UNSIGNED NULL (ohne doctrine/dbal via raw SQL)
        DB::statement('ALTER TABLE `projekte` MODIFY `verantwortlicher_id` BIGINT UNSIGNED NULL');

        // 4) FK neu anlegen (SET NULL bei Löschung)
        Schema::table('projekte', function (Blueprint $table) {
            $table->foreign('verantwortlicher_id')
                  ->references('Mitarbeiter_id')
                  ->on('mitarbeiter')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        $this->dropFkIfExists('projekte', 'verantwortlicher_id');

        // Spalte optional behalten; falls du sie zurückbauen willst, auskommentieren:
        // Schema::table('projekte', function (Blueprint $table) {
        //     $table->dropColumn('verantwortlicher_id');
        // });
    }

    private function dropFkIfExists(string $table, string $column): void
    {
        $db = DB::getDatabaseName();
        $fks = DB::table('information_schema.KEY_COLUMN_USAGE')
            ->select('CONSTRAINT_NAME')
            ->where('TABLE_SCHEMA', $db)
            ->where('TABLE_NAME', $table)
            ->where('COLUMN_NAME', $column)
            ->whereNotNull('REFERENCED_TABLE_NAME')
            ->pluck('CONSTRAINT_NAME');

        foreach ($fks as $name) {
            try {
                DB::statement("ALTER TABLE `{$table}` DROP FOREIGN KEY `{$name}`");
            } catch (\Throwable $e) {
                // ignorieren
            }
        }
    }
};
