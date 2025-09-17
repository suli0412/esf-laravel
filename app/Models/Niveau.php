<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Niveau extends Model
{
    protected $table = 'niveau';
    protected $primaryKey = 'niveau_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = ['code','label','sort_order'];
}
