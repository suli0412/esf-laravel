<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{




    public function register(): void
    {
        //
    }
    public function boot(): void
    {
    // Setzt created_by / updated_by automatisch für ALLE Modelle, die die Spalten haben
    Model::creating(function ($model) {
        $table = $model->getTable();
        if (Schema::hasColumn($table,'created_by') && empty($model->created_by) && Auth::check()) {
            $model->created_by = Auth::id();
        }
        if (Schema::hasColumn($table,'updated_by') && Auth::check()) {
            $model->updated_by = Auth::id();
        }
    });

    Model::updating(function ($model) {
        $table = $model->getTable();
        if (Schema::hasColumn($table,'updated_by') && Auth::check()) {
            $model->updated_by = Auth::id();
        }
    });

    }
}
