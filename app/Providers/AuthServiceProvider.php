<?php



namespace App\Providers;

use App\Models\Gruppe;
use App\Policies\GruppePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate; // ← NEU

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Gruppe::class => GruppePolicy::class,
    ];

    public function boot(): void
    {
        // Falls du Policies manuell registrieren willst:
        // $this->registerPolicies();

        // Berechtigung für Log-Ansicht:
        Gate::define('activity.view', function ($user) {
            // Erlaubt für Admin-Rolle ODER explizite Permission "activity.view"
            return method_exists($user, 'hasRole') && $user->hasRole('admin')
                || method_exists($user, 'can') && $user->can('activity.view');
        });
    }
}
