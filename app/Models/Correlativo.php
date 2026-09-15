<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Correlativo extends Model
{
    protected $fillable = [
        'clave',
        'valor',
    ];
}
