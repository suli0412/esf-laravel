<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

// Admin-Controller
use App\Http\Controllers\Admin\KompetenzController as AdminKompetenzController;
use App\Http\Controllers\Admin\NiveauController as AdminNiveauController;
use App\Http\Controllers\Admin\PruefungsterminController as AdminPruefungsterminController;
use App\Http\Controllers\Admin\UserAdminController;
use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\KompetenzstandController;
// App-Controller
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TeilnehmerController;
use App\Http\Controllers\MitarbeiterController;
use App\Http\Controllers\ChecklisteNerminController;
use App\Http\Controllers\BeratungController;
use App\Http\Controllers\GruppenBeratungController;
use App\Http\Controllers\ProjektController;
use App\Http\Controllers\AnwesenheitController;
use App\Http\Controllers\PruefungsterminController;
use App\Http\Controllers\DokumentController;
use App\Http\Controllers\TeilnehmerDokumentController;
use App\Http\Controllers\GruppenController;
use App\Http\Controllers\TeilnehmerPraktikumController;
use App\Http\Controllers\ProfileController;

/* -----------------------------------
|  Home → /dashboard oder /login
|------------------------------------*/
Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
})->name('home');

/* Auth scaffolding (Breeze/Jetstream/etc.) */
require __DIR__.'/auth.php';

/* -----------------------------------
|  Dashboard + App-Routen (auth)
|------------------------------------*/
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    /* Projekte */
    Route::resource('projekte', ProjektController::class)
        ->parameters(['projekte' => 'projekt'])
        ->scoped(['projekt' => 'projekt_id']);

    /* Teilnehmer CRUD */
    Route::resource('teilnehmer', TeilnehmerController::class)
        ->parameters(['teilnehmer' => 'teilnehmer'])
        ->scoped(['teilnehmer' => 'Teilnehmer_id']);

    /* Teilnehmer-Dokumente */
    Route::prefix('teilnehmer/{teilnehmer:Teilnehmer_id}')->name('teilnehmer.')->group(function () {
        Route::post('dokumente', [TeilnehmerDokumentController::class, 'store'])->name('dokumente.store');
        Route::get('dokumente/{dokument}',          [TeilnehmerDokumentController::class, 'show'])->whereNumber('dokument')->name('dokumente.show');
        Route::get('dokumente/{dokument}/download', [TeilnehmerDokumentController::class, 'download'])->whereNumber('dokument')->name('dokumente.download');
        Route::delete('dokumente/{dokument}',       [TeilnehmerDokumentController::class, 'destroy'])->whereNumber('dokument')->name('dokumente.destroy');

        // Vorlagen/Renderer (Slug — nicht nur Ziffern)
        Route::get('dokumente/{slug}', [DokumentController::class, 'render'])
            ->where('slug', '(?!\\d+$)[A-Za-z0-9\\-]+')
            ->name('dokumente.render');

        Route::get ('dokumente/{dokument}/prepare',  [DokumentController::class, 'prepare'])->whereNumber('dokument')->name('dokumente.prepare');
        Route::post('dokumente/{dokument}/generate', [DokumentController::class, 'generatePdf'])->whereNumber('dokument')->name('dokumente.generate');
    });

    /* Gruppen-Beratungen (Screens + Store) */
    Route::get   ('gruppen-beratungen',                 [GruppenBeratungController::class, 'index'])->name('gruppen_beratungen.index');
    Route::get   ('gruppen-beratungen/create',          [GruppenBeratungController::class, 'create'])->name('gruppen_beratungen.create');
    Route::post  ('gruppen-beratungen',                 [GruppenBeratungController::class, 'store'])->name('gruppen_beratungen.store');
    Route::get   ('gruppen-beratungen/{session}/edit',  [GruppenBeratungController::class, 'edit'])->name('gruppen_beratungen.edit');
    Route::put   ('gruppen-beratungen/{session}',       [GruppenBeratungController::class, 'update'])->name('gruppen_beratungen.update');
    Route::delete('gruppen-beratungen/{session}',       [GruppenBeratungController::class, 'destroy'])->name('gruppen_beratungen.destroy');

    /* Dokumente (Bibliothek) */
    Route::resource('dokumente', DokumentController::class)->parameters(['dokumente' => 'dokument']);
    Route::post('dokumente/go', [DokumentController::class, 'go'])->name('dokumente.go');

    /* Checkliste je Teilnehmer */
    Route::get ('teilnehmer/{teilnehmer:Teilnehmer_id}/checkliste', [ChecklisteNerminController::class, 'edit'])->name('checkliste.edit');
    Route::post('teilnehmer/{teilnehmer:Teilnehmer_id}/checkliste', [ChecklisteNerminController::class, 'save'])->name('checkliste.save');

    /* Beratungen (Einzel-Ressource; permissions) */
    Route::resource('beratungen', BeratungController::class)
        ->parameters(['beratungen' => 'beratung'])
        ->middleware(['permission:beratung.view|beratung.manage']);

    /* Anwesenheit (Einzelliste) */
    Route::resource('anwesenheit', AnwesenheitController::class)->except(['show']);

    /* Gruppen + Wochenansicht */
    Route::resource('gruppen', GruppenController::class)
        ->parameters(['gruppen' => 'gruppe'])
        ->scoped(['gruppe' => 'gruppe_id']);
    Route::get ('gruppen/{gruppe}/anwesenheit', [GruppenController::class, 'weekAlias'])->name('gruppen.anwesenheit');
    Route::post('gruppen/{gruppe}/anwesenheit', [GruppenController::class, 'saveWeek'])->name('gruppen.anwesenheit.save');
    Route::post  ('gruppen/{gruppe}/mitglieder',              [GruppenController::class, 'attachTeilnehmer'])->middleware('can:updateMembers,gruppe')->name('gruppen.mitglieder.attach');
    Route::delete('gruppen/{gruppe}/mitglieder/{teilnehmer}', [GruppenController::class, 'detachTeilnehmer'])->middleware('can:updateMembers,gruppe')->name('gruppen.mitglieder.detach');

    /* Praktika (unter Teilnehmer) */
    Route::post  ('teilnehmer/{teilnehmer:Teilnehmer_id}/praktika',             [TeilnehmerPraktikumController::class, 'store'])->name('praktika.store');
    Route::put   ('teilnehmer/{teilnehmer:Teilnehmer_id}/praktika/{praktikum}', [TeilnehmerPraktikumController::class, 'update'])->name('praktika.update');
    Route::delete('teilnehmer/{teilnehmer:Teilnehmer_id}/praktika/{praktikum}', [TeilnehmerPraktikumController::class, 'destroy'])->name('praktika.destroy');

    /* Mitarbeiter */
    Route::resource('mitarbeiter', MitarbeiterController::class);

    /* Prüfungstermine */
    Route::get   ('pruefungstermine',               [PruefungsterminController::class, 'index'])->name('pruefungstermine.index');
    Route::get   ('pruefungstermine/create',        [PruefungsterminController::class, 'create'])->name('pruefungstermine.create');
    Route::post  ('pruefungstermine',               [PruefungsterminController::class, 'store'])->name('pruefungstermine.store');
    Route::get   ('pruefungstermine/{termin}',      [PruefungsterminController::class, 'show'])->name('pruefungstermine.show');
    Route::get   ('pruefungstermine/{termin}/edit', [PruefungsterminController::class, 'edit'])->name('pruefungstermine.edit');
    Route::put   ('pruefungstermine/{termin}',      [PruefungsterminController::class, 'update'])->name('pruefungstermine.update');
    Route::delete('pruefungstermine/{termin}',      [PruefungsterminController::class, 'destroy'])->name('pruefungstermine.destroy');
    Route::post  ('pruefungstermine/import',        [PruefungsterminController::class, 'import'])->name('pruefungstermine.import');
    Route::post  ('pruefungstermine/{termin}/buchen',                 [PruefungsterminController::class, 'buchen'])->name('pruefungstermine.buchen');
    Route::delete('pruefungstermine/{termin}/storno/{teilnehmer}',    [PruefungsterminController::class, 'storno'])->name('pruefungstermine.storno');
    Route::patch ('pruefungstermine/{termin}/teilnahme/{teilnehmer}', [PruefungsterminController::class, 'status'])->name('pruefungstermine.status');

    /* Profil */
    Route::get   ('profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch ('profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
/* -----------------------------------
|  Admin (nur Rolle "admin")
|------------------------------------*/
Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'role:admin']) // exakte Schreibweise des Rollennamens!
    ->group(function () {

        // =========================
        // Benutzer & Rollen
        // =========================

        // Nur anzeigen (Index/Show) -> users.view
        Route::resource('users', \App\Http\Controllers\Admin\UserAdminController::class)
            ->only(['index', 'show'])
            ->middleware('permission:users.view');

        // Verwalten (Create/Store/Edit/Update/Destroy) -> users.manage
        Route::resource('users', \App\Http\Controllers\Admin\UserAdminController::class)
            ->only(['create', 'store', 'edit', 'update', 'destroy'])
            ->middleware('permission:users.manage');

        // Passwort-Reset-Link verschicken
        // Route-Name bleibt kompatibel mit Blade: admin.users.sendReset
        Route::post('users/{user}/send-reset', [\App\Http\Controllers\Admin\UserAdminController::class, 'sendReset'])
            ->name('users.sendReset')
            ->middleware('permission:users.manage');


        // =========================
        // Protokolle / Logs
        // =========================
        // Achtung: Controller umgestellt auf Admin\LogController (neue Implementation)
        // Logs-Controller konsistent zum Import
        Route::get('logs', [ActivityLogController::class, 'index'])
            ->name('logs.index')
            ->middleware('permission:audit.view');

        // Stammdaten
        Route::resource('kompetenzen', AdminKompetenzController::class)
        ->parameters(['kompetenzen' => 'kompetenz']);


        Route::resource('niveaus', AdminNiveauController::class)
            ->except(['show'])
            ->middleware('permission:settings.manage');

        // Prüfungstermine (Admin)
        Route::resource('pruefungstermine', AdminPruefungsterminController::class);

        Route::post('pruefungstermine/{termin}/buchen', [AdminPruefungsterminController::class, 'buchen'])
            ->name('pruefungstermine.buchen');

            });




/* Pivot-Routen für Gruppen-Beratungen (Permissions) */
Route::post  ('gruppen-beratungen/{session}/teilnehmer', [GruppenBeratungController::class, 'attachTeilnehmer'])
    ->name('gruppen_beratungen.teilnehmer.attach')
    ->middleware(['auth','permission:beratung.manage']);
Route::delete('gruppen-beratungen/{session}/teilnehmer/{teilnehmer}', [GruppenBeratungController::class, 'detachTeilnehmer'])
    ->name('gruppen_beratungen.teilnehmer.detach')
    ->middleware(['auth','permission:beratung.manage']);

    Route::middleware(['auth']) // ggf. erweitern: 'permission:teilnehmer.edit'
    ->post('/teilnehmer/{teilnehmer}/kompetenzen/demo', [TeilnehmerController::class, 'setDemoKompetenzen'])
    ->name('teilnehmer.demoKompetenzen');


// routes/web.php
Route::post('/teilnehmer/{teilnehmer}/kompetenzstand', [\App\Http\Controllers\KompetenzstandController::class, 'store'])
    ->name('kompetenzstand.store'); // lassen, falls du noch Einzel-Speichern nutzt
Route::post('/teilnehmer/{teilnehmer}/kompetenzen/bulk', [KompetenzstandController::class, 'bulkFromKompetenzForm'])
    ->name('kompetenz.bulkForm');




