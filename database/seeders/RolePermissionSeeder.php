<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use App\Models\User;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Cache leeren (wichtig bei wiederholtem Seeden)
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // --- Permissions (nach Modulen gruppiert) ---
        $permissions = [
            // Teilnehmer
            'teilnehmer.view', 'teilnehmer.create', 'teilnehmer.update', 'teilnehmer.delete',

            // Dokumente
            'dokumente.view', 'dokumente.upload', 'dokumente.delete',

            // Gruppen
            'gruppen.view', 'gruppen.manage',

            // Beratung
            'beratung.view', 'beratung.manage',

            // Praktikum
            'praktikum.view', 'praktikum.manage',

            // Anwesenheit
            'anwesenheit.view', 'anwesenheit.manage',

            // Reports / Einstellungen
            'reports.view', 'settings.manage',

            // User/Rollen-Admin
            'users.view', 'users.manage', 'roles.manage',

            // Aktivitäts- / Audit-Log (für den Logs-Button)
            'activity.view',
        ];

        foreach ($permissions as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }

        // --- Rollen  ---
        $admin       = Role::firstOrCreate(['name' => 'admin',       'guard_name' => 'web']);
        $mitarbeiter = Role::firstOrCreate(['name' => 'mitarbeiter', 'guard_name' => 'web']);
        $coach       = Role::firstOrCreate(['name' => 'coach',       'guard_name' => 'web']);
        $gast        = Role::firstOrCreate(['name' => 'gast',        'guard_name' => 'web']);

        // Admin bekommt ALLE Berechtigungen
        $admin->syncPermissions(Permission::all());

        // Mitarbeiter – typische Schreibrechte im Tagesgeschäft
        $mitarbeiter->syncPermissions([
            'teilnehmer.view','teilnehmer.create','teilnehmer.update',
            'dokumente.view','dokumente.upload','dokumente.delete',
            'anwesenheit.view','anwesenheit.manage',
            'praktikum.view','praktikum.manage',
            'beratung.view','beratung.manage',
            'reports.view',
        ]);

        // Coach – eher lesend + Beratung/Praktikum verwalten
        $coach->syncPermissions([
            'teilnehmer.view',
            'dokumente.view',
            'praktikum.view','praktikum.manage',
            'beratung.view','beratung.manage',
            'reports.view',
        ]);

        // Gast – nur lesend
        $gast->syncPermissions([
            'teilnehmer.view',
            'dokumente.view',
            'reports.view',
        ]);

        //  User zum Admin machen
        if ($firstUser = User::query()->orderBy('id')->first()) {
            if (!$firstUser->hasRole('admin')) {
                $firstUser->assignRole('admin');
            }
        }

        // Cache erneut leeren
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
