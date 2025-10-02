<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Tabellen, die Audit bekommen sollen.
     * Du kannst später jederzeit Tabellen hinzufügen – die Checks sind idempotent.
     */
    private array $tables = [
        // 'pruefungstermin', // hat die FKs bei dir schon -> vorerst draußen lassen
        'gruppen',
        'teilnehmer',
        'dokumente',
        // 'anwesenheit',
        // 'beratung',
        // 'pruefungsteilnahme',
    ];

    /**
     * Optional: „nach welcher Spalte“ einfügen.
     * Falls die Referenzspalte fehlt, wird ohne ->after(...) angelegt.
     */
    private array $afterColumn = [
        // 'pruefungstermin' => 'end_at',
        'gruppen'    => 'updated_at',
        'teilnehmer' => 'updated_at',
        'dokumente'  => 'updated_at',
        // 'anwesenheit' => 'updated_at',
    ];

    public function up(): void
    {
        foreach ($this->tables as $t) {
            if (!Schema::hasTable($t)) {
                continue;
            }

            // 1) Spalten anlegen (falls nicht vorhanden)
            Schema::table($t, function (Blueprint $table) use ($t) {
                $after = $this->afterColumn[$t] ?? null;

                if (!Schema::hasColumn($t, 'created_by')) {
                    $col = $table->unsignedBigInteger('created_by')->nullable();
                    if ($after && Schema::hasColumn($t, $after)) {
                        $col->after($after);
                    }
                }

                if (!Schema::hasColumn($t, 'updated_by')) {
                    $afterForUpdated = Schema::hasColumn($t, 'created_by') ? 'created_by' : ($this->afterColumn[$t] ?? null);
                    $col = $table->unsignedBigInteger('updated_by')->nullable();
                    if ($afterForUpdated && Schema::hasColumn($t, $afterForUpdated)) {
                        $col->after($afterForUpdated);
                    }
                }
            });

            // 2) FKs nur anlegen, wenn weder der FK-Name existiert noch bereits ein FK auf der Spalte liegt
            Schema::table($t, function (Blueprint $table) use ($t) {
                $fkCreated = "fk_{$t}_created_by";
                $fkUpdated = "fk_{$t}_updated_by";

                // created_by
                if (
                    Schema::hasColumn($t, 'created_by')
                    && !$this->foreignKeyExists($t, $fkCreated)
                    && !$this->foreignKeyOnColumnExists($t, 'created_by')
                ) {
                    try {
                        $table->foreign('created_by', $fkCreated)
                              ->references('id')->on('users')
                              ->nullOnDelete();
                    } catch (\Throwable $e) {
                        // falls race/edge-case – ignorieren
                    }
                }

                // updated_by
                if (
                    Schema::hasColumn($t, 'updated_by')
                    && !$this->foreignKeyExists($t, $fkUpdated)
                    && !$this->foreignKeyOnColumnExists($t, 'updated_by')
                ) {
                    try {
                        $table->foreign('updated_by', $fkUpdated)
                              ->references('id')->on('users')
                              ->nullOnDelete();
                    } catch (\Throwable $e) {
                        // falls race/edge-case – ignorieren
                    }
                }
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $t) {
            if (!Schema::hasTable($t)) {
                continue;
            }

            Schema::table($t, function (Blueprint $table) use ($t) {
                $fkCreated = "fk_{$t}_created_by";
                $fkUpdated = "fk_{$t}_updated_by";

                // FKs droppen (falls vorhanden)
                try { if ($this->foreignKeyExists($t, $fkCreated)) $table->dropForeign($fkCreated); } catch (\Throwable $e) {}
                try { if ($this->foreignKeyExists($t, $fkUpdated)) $table->dropForeign($fkUpdated); } catch (\Throwable $e) {}

                // Spalten droppen (falls vorhanden)
                if (Schema::hasColumn($t, 'updated_by')) {
                    $table->dropColumn('updated_by');
                }
                if (Schema::hasColumn($t, 'created_by')) {
                    $table->dropColumn('created_by');
                }
            });
        }
    }

    /** Prüft, ob ein FK mit genau diesem Namen auf der Tabelle existiert. */
    private function foreignKeyExists(string $table, string $constraint): bool
    {
        $db = DB::getDatabaseName();
        $row = DB::selectOne("
            SELECT 1
            FROM information_schema.TABLE_CONSTRAINTS
            WHERE CONSTRAINT_SCHEMA = ?
              AND TABLE_NAME = ?
              AND CONSTRAINT_NAME = ?
              AND CONSTRAINT_TYPE = 'FOREIGN KEY'
            LIMIT 1
        ", [$db, $table, $constraint]);

        return (bool) $row;
    }

    /** Prüft, ob auf der Spalte bereits irgendein FK existiert. */
    private function foreignKeyOnColumnExists(string $table, string $column): bool
    {
        $db = DB::getDatabaseName();
        $row = DB::selectOne("
            SELECT 1
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE CONSTRAINT_SCHEMA = ?
              AND TABLE_NAME        = ?
              AND COLUMN_NAME       = ?
              AND REFERENCED_TABLE_NAME IS NOT NULL
            LIMIT 1
        ", [$db, $table, $column]);

        return (bool) $row;
    }
};
