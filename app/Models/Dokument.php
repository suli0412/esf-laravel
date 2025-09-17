<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dokument extends Model
{
    protected $table = 'dokumente';
    protected $fillable = ['name','slug','body','is_active'];

    // optional: Scope
    public function scopeAktiv($q){ return $q->where('is_active',true); }
}
