<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Niveau extends Model
{
    protected $table = 'niveau';
    protected $primaryKey = 'niveau_id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;      // <- keine created_at/updated_at Spalten

    protected $fillable = ['code','label','sort_order'];
}
