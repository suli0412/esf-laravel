<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule; // ← NEU

use Spatie\Permission\Middlewares\RoleMiddleware;
use Spatie\Permission\Middlewares\PermissionMiddleware;
use Spatie\Permission\Middlewares\RoleOrPermissionMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // robust gegen unterschiedliche Namespaces in Spatie-Versionen
        $roleClass = class_exists(\Spatie\Permission\Middlewares\RoleMiddleware::class)
            ? \Spatie\Permission\Middlewares\RoleMiddleware::class
            : \Spatie\Permission\Middleware\RoleMiddleware::class;

        $permissionClass = class_exists(\Spatie\Permission\Middlewares\PermissionMiddleware::class)
            ? \Spatie\Permission\Middlewares\PermissionMiddleware::class
            : \Spatie\Permission\Middleware\PermissionMiddleware::class;

        $roleOrPermissionClass = class_exists(\Spatie\Permission\Middlewares\RoleOrPermissionMiddleware::class)
            ? \Spatie\Permission\Middlewares\RoleOrPermissionMiddleware::class
            : \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class;

        $middleware->alias([
            'role' => $roleClass,
            'permission' => $permissionClass,
            'role_or_permission' => $roleOrPermissionClass,
        ]);
    })
    ->withSchedule(function (Schedule $schedule) {
        // Löscht alte Activitylog-Einträge gemäß config/activitylog.php
        $schedule->command('activitylog:clean')->dailyAt('03:00');

        // Optional (wenn gewünscht): Permission-Cache regelmäßig zurücksetzen
        // $schedule->command('permission:cache-reset')->dailyAt('03:05');
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
