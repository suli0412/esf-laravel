<?php

namespace App\Models\Concerns;

use Illuminate\Support\Facades\Auth;

trait UsesUserstamps
{
    public static function bootUsesUserstamps(): void
    {
        static::creating(function ($model) {
            if (Auth::check()) {
                if ($model->isFillable('created_by') || \Schema::hasColumn($model->getTable(), 'created_by')) {
                    $model->created_by ??= Auth::id();
                }
                if ($model->isFillable('updated_by') || \Schema::hasColumn($model->getTable(), 'updated_by')) {
                    $model->updated_by ??= Auth::id();
                }
            }
        });

        static::updating(function ($model) {
            if (Auth::check() && (\Schema::hasColumn($model->getTable(), 'updated_by') || $model->isFillable('updated_by'))) {
                $model->updated_by = Auth::id();
            }
        });
    }

    // Relationen zu User
    public function createdBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }
}
