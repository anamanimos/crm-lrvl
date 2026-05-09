<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DealStage extends Model
{
    protected $fillable = [
        'name',
        'color',
        'stage_type', // pipeline, won, lost
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function deals()
    {
        return $this->hasMany(Deal::class)->where('is_archived', false);
    }
}
