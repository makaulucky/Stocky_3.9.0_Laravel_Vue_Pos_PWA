<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    protected $dates = ['deleted_at'];

    protected $fillable = [
        'name', 'description', 'image',
    ];

}
