<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// Admin-Controller
use App\Http\Controllers\Admin\KompetenzController as AdminKompetenzController;
use App\Http\Controllers\Admin\NiveauController as AdminNiveauController;
use App\Http\Controllers\Admin\PruefungsterminController as AdminPruefungsterminController;
use App\Http\Controllers\Admin\UserAdminController;

// App-Controller
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TeilnehmerController;
use App\Http\Controllers\MitarbeiterController;
use App\Http\Controllers\ChecklisteNerminController;
use App\Http\Controllers\BeratungController;
use App\Http\Controllers\GruppenBeratungController;
use App\Http\Controllers\BeratungsartController;
use App\Http\Controllers\BeratungsthemaController;
use App\Http\Controllers\ProjektController;
use App\Http\Controllers\AnwesenheitController;
use App\Http\Controllers\PruefungsterminController;
use App\Http\Controllers\DokumentController;
use App\Http\Controllers\TeilnehmerDokumentController;
use App\Http\Controllers\GruppenController;
use App\Http\Controllers\TeilnehmerPraktikumController;
use App\Http\Controllers\KompetenzController;
use App\Http\Controllers\NiveauController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\RoleController;
use App\Models\User;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\Admin\UserRoleController;
/*
|--------------------------------------------------------------------------
| Dev-Login (nur lokal)
|--------------------------------------------------------------------------
*/
if (app()->environment('local')) {
    Route::get('/login', function () {
        $user = User::first();
        if (!$user) {
            $user = User::forceCreate([
                'name'     => 'Dev',
                'email'    => 'dev@example.com',
                'password' => bcrypt('password'), // nur lokal!
            ]);
        }
        Auth::login($user, remember: true);
        return redirect()->intended(route('dashboard'));
    })->name('login');

    Route::post('/logout', function () {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect('/');
    })->name('logout');
}

/*
|--------------------------------------------------------------------------
| Dashboard (Startseite)
|--------------------------------------------------------------------------
*/
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

/*
|--------------------------------------------------------------------------
| Admin-Bereich (mit Rollen-Check)
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->middleware(['auth','role:admin'])->group(function () {
    // Benutzerverwaltung
    Route::resource('users', UserAdminController::class);

    // Stammdaten
    Route::resource('kompetenzen', AdminKompetenzController::class)->except(['show']);
    Route::resource('niveaus',     AdminNiveauController::class)->except(['show']);

    // Prüfungstermine (Admin)
    Route::resource('pruefungstermine', AdminPruefungsterminController::class);
    Route::post('pruefungstermine/{termin}/buchen', [AdminPruefungsterminController::class, 'buchen'])
        ->name('pruefungstermine.buchen');
});

/*
|--------------------------------------------------------------------------
| Auth-geschützte App-Routen
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    // Teilnehmer & Mitarbeiter
    Route::resource('teilnehmer',  TeilnehmerController::class);
    Route::resource('mitarbeiter', MitarbeiterController::class);

    // Checkliste je Teilnehmer
    Route::get ('/teilnehmer/{teilnehmer:Teilnehmer_id}/checkliste', [ChecklisteNerminController::class, 'edit'])->name('checkliste.edit');
    Route::post('/teilnehmer/{teilnehmer:Teilnehmer_id}/checkliste', [ChecklisteNerminController::class, 'save'])->name('checkliste.save');

    // Stammdaten (nur nutzen, wenn NICHT der Admin-Block oben verwendet wird)
    // Route::resource('kompetenzen', KompetenzController::class)->except(['show']);
    // Route::resource('niveaus',     NiveauController::class)->except(['show']);

    // Beratungen
    Route::get ('/beratungen',          [BeratungController::class, 'index'])->name('beratungen.index');
    Route::post('/beratungen',          [BeratungController::class, 'store'])->name('beratungen.store');
    Route::post('/gruppen-beratungen',  [GruppenBeratungController::class, 'store'])->name('gruppen_beratungen.store');

    // Anwesenheit (Fix: create ist vorhanden – except(['show']) statt only([...]))
    Route::resource('anwesenheit', AnwesenheitController::class)->except(['show']);

    // Prüfungstermine (App-Ansicht)
    Route::get   ('pruefungstermine',               [PruefungsterminController::class, 'index'])->name('pruefungstermine.index');
    Route::get   ('pruefungstermine/create',        [PruefungsterminController::class, 'create'])->name('pruefungstermine.create');
    Route::post  ('pruefungstermine',               [PruefungsterminController::class, 'store'])->name('pruefungstermine.store');
    Route::get   ('pruefungstermine/{termin}',      [PruefungsterminController::class, 'show'])->name('pruefungstermine.show');
    Route::get   ('pruefungstermine/{termin}/edit', [PruefungsterminController::class, 'edit'])->name('pruefungstermine.edit');
    Route::put   ('pruefungstermine/{termin}',      [PruefungsterminController::class, 'update'])->name('pruefungstermine.update');
    Route::delete('pruefungstermine/{termin}',      [PruefungsterminController::class, 'destroy'])->name('pruefungstermine.destroy');
    Route::post  ('pruefungstermine/import',        [PruefungsterminController::class, 'import'])->name('pruefungstermine.import');
    Route::post  ('pruefungstermine/{termin}/buchen',                  [PruefungsterminController::class, 'buchen'])->name('pruefungstermine.buchen');
    Route::delete('pruefungstermine/{termin}/storno/{teilnehmer}',     [PruefungsterminController::class, 'storno'])->name('pruefungstermine.storno');
    Route::patch ('pruefungstermine/{termin}/teilnahme/{teilnehmer}',  [PruefungsterminController::class, 'status'])->name('pruefungstermine.status');

    // Dokumente (Vorlagen + Generator)
    Route::resource('dokumente', DokumentController::class)
        ->parameters(['dokumente' => 'dokument']);

    Route::get ('/teilnehmer/{teilnehmer:Teilnehmer_id}/dokumente/{dokument}/prepare',  [DokumentController::class, 'prepare'])->name('dokumente.prepare');
    Route::post('/teilnehmer/{teilnehmer:Teilnehmer_id}/dokumente/{dokument}/generate', [DokumentController::class, 'generatePdf'])->name('dokumente.generate');
    Route::get ('/teilnehmer/{teilnehmer:Teilnehmer_id}/dokumente/{slug}',              [DokumentController::class, 'render'])->name('dokumente.render');
    Route::post('/dokumente/go', [DokumentController::class, 'go'])->name('dokumente.go');

    // Teilnehmer-Dokumente (Uploads)
    Route::post  ('/teilnehmer/{teilnehmer:Teilnehmer_id}/dokumente',        [TeilnehmerDokumentController::class, 'store'])->name('teilnehmer_dokumente.store');
    Route::get   ('/teilnehmer-dokumente/{doc}',                              [TeilnehmerDokumentController::class, 'show'])->name('teilnehmer_dokumente.show');
    Route::get   ('/teilnehmer-dokumente/{doc}/download',                     [TeilnehmerDokumentController::class, 'download'])->name('teilnehmer_dokumente.download');
    Route::delete('/teilnehmer-dokumente/{doc}',                              [TeilnehmerDokumentController::class, 'destroy'])->name('teilnehmer_dokumente.destroy');

    // Gruppen (CRUD) + Mitglieder
    Route::resource('gruppen', GruppenController::class)
        ->parameters(['gruppen' => 'gruppe'])
        ->scoped(['gruppe' => 'gruppe_id']);

    Route::post  ('gruppen/{gruppe}/mitglieder',                    [GruppenController::class, 'attachTeilnehmer'])->middleware('can:updateMembers,gruppe')->name('gruppen.mitglieder.attach');
    Route::delete('gruppen/{gruppe}/mitglieder/{teilnehmer}',       [GruppenController::class, 'detachTeilnehmer'])->middleware('can:updateMembers,gruppe')->name('gruppen.mitglieder.detach');

    // Praktika (nested unter Teilnehmer)
    Route::post  ('/teilnehmer/{teilnehmer:Teilnehmer_id}/praktika',             [TeilnehmerPraktikumController::class, 'store'])->name('praktika.store');
    Route::put   ('/teilnehmer/{teilnehmer:Teilnehmer_id}/praktika/{praktikum}', [TeilnehmerPraktikumController::class, 'update'])->name('praktika.update');
    Route::delete('/teilnehmer/{teilnehmer:Teilnehmer_id}/praktika/{praktikum}', [TeilnehmerPraktikumController::class, 'destroy'])->name('praktika.destroy');

    // Profil (Breeze/Jetstream-kompatibel)
    Route::get   ('/profile',  [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch ('/profile',  [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile',  [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| Auth-Scaffolding (falls vorhanden)
|--------------------------------------------------------------------------
*/
require __DIR__.'/auth.php';

/*
|--------------------------------------------------------------------------
| Safety-Fallback: nur wenn keine 'login'-Route existiert
|--------------------------------------------------------------------------
*/
if (!Route::has('login')) {
    Route::get('/login', function () {
        $user = User::first();
        if (!$user) {
            $user = User::forceCreate([
                'name'     => 'Dev',
                'email'    => 'dev@example.com',
                'password' => bcrypt('password'), // nur lokal!
            ]);
        }
        Auth::login($user, remember: true);
        return redirect()->intended(route('dashboard'));
    })->name('login');

    Route::post('/logout', function () {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect('/');
    })->name('logout');
}

// Alias-Routen, damit Views mit kompetenzen.* / niveaus.* weiter funktionieren
Route::middleware(['auth','role:admin'])->group(function () {
    Route::get('/kompetenzen', fn() => redirect()->route('admin.kompetenzen.index'))
        ->name('kompetenzen.index');

    Route::get('/niveaus', fn() => redirect()->route('admin.niveaus.index'))
        ->name('niveaus.index');
});

// Nur Nutzer mit diesem Recht:
Route::get('/teilnehmer', [TeilnehmerController::class, 'index'])
    ->middleware('permission:teilnehmer.view');

Route::post('/teilnehmer', [TeilnehmerController::class, 'store'])
    ->middleware('permission:teilnehmer.create');

Route::delete('/teilnehmer/{id}', [TeilnehmerController::class, 'destroy'])
    ->middleware('permission:teilnehmer.delete');



Route::middleware(['web','permission:teilnehmer.view'])->group(function () {
    Route::resource('teilnehmer', TeilnehmerController::class)->names('teilnehmer');
});

// Admin-Bereich
Route::middleware(['web','role:Admin'])->group(function () {
    // Falls du NUR User-Rollen-UI hast (aus unserem Beispiel):
    Route::get('/admin/users', [UserRoleController::class, 'index'])->name('admin.users.index');
    Route::post('/admin/users/{user}/roles', [UserRoleController::class, 'updateRoles'])->name('admin.users.roles.update');
});


Route::get('/reports', [ReportsController::class, 'index'])
    ->middleware(['web','permission:reports.view'])
    ->name('reports.index');

// Admin Rechte
Route::middleware(['web','permission:users.manage'])->group(function () {
    Route::get('/admin/users', [UserRoleController::class, 'index'])->name('admin.users.index');
    Route::post('/admin/users', [UserRoleController::class, 'store'])->name('admin.users.store');
    Route::post('/admin/users/{user}/roles', [UserRoleController::class, 'updateRoles'])->name('admin.users.roles.update');
});
