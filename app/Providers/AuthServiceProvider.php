<?php

namespace App\Providers;

use App\Models\Gruppe;
use App\Policies\GruppePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Gruppe::class => GruppePolicy::class,
    ];

    public function boot(): void
    {
        // optional: nothing else needed
    }
}
