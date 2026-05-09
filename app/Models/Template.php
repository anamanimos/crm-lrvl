<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Template extends Model
{
    protected $fillable = [
        'title',
        'content',
        'media_path',
        'media_type',
        'shortcut',
        'category',
        'is_active',
    ];
}
