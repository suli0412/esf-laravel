<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // --- Permissions (nach Modulen gruppiert) ---
        $perms = [
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
        ];

        foreach ($perms as $p) {
            Permission::firstOrCreate(['name' => $p]);
        }

        // --- Rollen ---
        $admin       = Role::firstOrCreate(['name' => 'Admin']);
        $mitarbeiter = Role::firstOrCreate(['name' => 'Mitarbeiter']);
        $coach       = Role::firstOrCreate(['name' => 'Coach']);
        $gast        = Role::firstOrCreate(['name' => 'Gast']);

        // Admin bekommt alles
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

        // Coach – eher lesend + ggf. Beratung/Praktikum verwalten
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
    }
}
