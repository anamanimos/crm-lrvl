<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class WaGroup extends Model
{
    protected $fillable = [
        'uuid', 'jid', 'name', 'last_chat_at'
    ];

    protected $casts = [
        'last_chat_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }
}
