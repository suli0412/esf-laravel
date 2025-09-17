<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Tabelle fehlt? Nichts tun.
        if (!Schema::hasTable('anwesenheiten')) return;

        // Diese Migration sollte nur einen Unique-Index ergänzen,
        // den wir bereits in der Create-Migration gesetzt haben.
        // Also bewusst: No-Op.
    }

    public function down(): void
    {
        // bewusst leer
    }
};
