<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // --- Altlasten mit falschem Namen "1" defensiv entfernen ---
        foreach (['beratungen','pruefungstermin','gruppen'] as $tbl) {
            try { DB::statement("ALTER TABLE `{$tbl}` DROP FOREIGN KEY `1`"); } catch (\Throwable $e) {}
            try { DB::statement("ALTER TABLE `{$tbl}` DROP INDEX `1`"); } catch (\Throwable $e) {}
        }

        // ===== BERATUNGEN =====
        Schema::table('beratungen', function (Blueprint $t) {
            if (!Schema::hasColumn('beratungen', 'created_by')) {
                $t->unsignedBigInteger('created_by')->nullable()->after('updated_at');
            }
            if (!Schema::hasColumn('beratungen', 'updated_by')) {
                $t->unsignedBigInteger('updated_by')->nullable()->after('created_by');
            }
        });
        $this->addFkIfMissing('beratungen', 'created_by', 'beratungen_created_by_fk');
        $this->addFkIfMissing('beratungen', 'updated_by', 'beratungen_updated_by_fk');

        // ===== PRUEFUNGSTERMIN =====
        if (Schema::hasTable('pruefungstermin')) {
            Schema::table('pruefungstermin', function (Blueprint $t) {
                if (!Schema::hasColumn('pruefungstermin', 'created_by')) {
                    $t->unsignedBigInteger('created_by')->nullable()->after('updated_at');
                }
                if (!Schema::hasColumn('pruefungstermin', 'updated_by')) {
                    $t->unsignedBigInteger('updated_by')->nullable()->after('created_by');
                }
            });
            $this->addFkIfMissing('pruefungstermin', 'created_by', 'pruefungstermin_created_by_fk');
            $this->addFkIfMissing('pruefungstermin', 'updated_by', 'pruefungstermin_updated_by_fk');
        }

        // ===== GRUPPEN (falls vorhanden) =====
        if (Schema::hasTable('gruppen')) {
            Schema::table('gruppen', function (Blueprint $t) {
                if (!Schema::hasColumn('gruppen', 'created_by')) {
                    $t->unsignedBigInteger('created_by')->nullable()->after('updated_at');
                }
                if (!Schema::hasColumn('gruppen', 'updated_by')) {
                    $t->unsignedBigInteger('updated_by')->nullable()->after('created_by');
                }
            });
            $this->addFkIfMissing('gruppen', 'created_by', 'gruppen_created_by_fk');
            $this->addFkIfMissing('gruppen', 'updated_by', 'gruppen_updated_by_fk');
        }
    }

    public function down(): void
    {
        foreach (['beratungen','pruefungstermin','gruppen'] as $table) {
            foreach (["{$table}_created_by_fk","{$table}_updated_by_fk"] as $fk) {
                try { DB::statement("ALTER TABLE `{$table}` DROP FOREIGN KEY `{$fk}`"); } catch (\Throwable $e) {}
            }
            Schema::table($table, function (Blueprint $t) use ($table) {
                if (Schema::hasColumn($table, 'updated_by')) $t->dropColumn('updated_by');
                if (Schema::hasColumn($table, 'created_by')) $t->dropColumn('created_by');
            });
        }
    }

    private function addFkIfMissing(string $table, string $column, string $fkName): void
    {
        // Index anlegen (falls nicht da). Wenn schon vorhanden, ignorieren wir die Exception.
        try { DB::statement("ALTER TABLE `{$table}` ADD INDEX `{$fkName}_idx` (`{$column}`)"); } catch (\Throwable $e) {}

        $exists = DB::table('information_schema.REFERENTIAL_CONSTRAINTS')
            ->where('CONSTRAINT_SCHEMA', DB::raw('DATABASE()'))
            ->where('TABLE_NAME', $table)
            ->where('CONSTRAINT_NAME', $fkName)
            ->exists();

        if (!$exists) {
            DB::statement("
                ALTER TABLE `{$table}`
                ADD CONSTRAINT `{$fkName}`
                FOREIGN KEY (`{$column}`) REFERENCES `users`(`id`)
                ON DELETE SET NULL
            ");
        }
    }
};
