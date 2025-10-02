<?php



use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // --- beschreibung
        if (!Schema::hasColumn('projekte', 'beschreibung')) {
            Schema::table('projekte', function (Blueprint $table) {
                $table->text('beschreibung')->nullable()->after('bezeichnung');
            });
        }

        // --- inhalte
        if (!Schema::hasColumn('projekte', 'inhalte')) {
            Schema::table('projekte', function (Blueprint $table) {
                $table->text('inhalte')->nullable()->after('beschreibung');
            });
        }

        // --- verantwortlicher_id (passend zum Typ von mitarbeiter.Mitarbeiter_id)
        if (!Schema::hasColumn('projekte', 'verantwortlicher_id')) {
            Schema::table('projekte', function (Blueprint $table) {
                // Falls Mitarbeiter_id BIGINT UNSIGNED ist:
                $table->unsignedBigInteger('verantwortlicher_id')->nullable()->after('inhalte');
                // Wenn bei dir Mitarbeiter_id INT UNSIGNED ist, ändere die Zeile auf:
                // $table->unsignedInteger('verantwortlicher_id')->nullable()->after('inhalte');
            });
        }

        // --- FK nur setzen, wenn noch keiner existiert
        $fkExists = DB::table('information_schema.KEY_COLUMN_USAGE')
            ->where('TABLE_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_NAME', 'projekte')
            ->where('COLUMN_NAME', 'verantwortlicher_id')
            ->whereNotNull('CONSTRAINT_NAME')
            ->exists();

        if (Schema::hasColumn('projekte', 'verantwortlicher_id') && !$fkExists) {
            Schema::table('projekte', function (Blueprint $table) {

            });
        }
    }

    public function down(): void
    {
        // FK defensiv droppen
        try {
            Schema::table('projekte', function (Blueprint $table) {
                $table->dropForeign(['verantwortlicher_id']);
            });
        } catch (\Throwable $e) {
            // ignorieren
        }

        // Spalten nur droppen, wenn vorhanden
        Schema::table('projekte', function (Blueprint $table) {
            if (Schema::hasColumn('projekte', 'verantwortlicher_id')) {
                $table->dropColumn('verantwortlicher_id');
            }
            if (Schema::hasColumn('projekte', 'inhalte')) {
                $table->dropColumn('inhalte');
            }
            if (Schema::hasColumn('projekte', 'beschreibung')) {
                $table->dropColumn('beschreibung');
            }
        });
    }
};
