<?php

namespace App\Models\Traits;

use Illuminate\Support\Facades\Auth;

trait Blameable
{
    protected static function bootBlameable(): void
    {
        static::creating(function ($model) {
            $uid = Auth::id();
            if ($uid) {
                if (empty($model->created_by)) $model->created_by = $uid;
                $model->updated_by = $uid;
            }
        });

        static::updating(function ($model) {
            $uid = Auth::id();
            if ($uid) $model->updated_by = $uid;
        });
    }

    // Relationen für dein Blade-Partial:
    public function createdBy() { return $this->belongsTo(\App\Models\User::class, 'created_by'); }
    public function updatedBy() { return $this->belongsTo(\App\Models\User::class, 'updated_by'); }

    // Alte Alias-Namen, falls dein Partial „creator/updater“ nutzt:
    public function creator() { return $this->createdBy(); }
    public function updater() { return $this->updatedBy(); }
}
