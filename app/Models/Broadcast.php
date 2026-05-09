<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Broadcast extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'message_template',
        'media_path',
        'media_type',
        'target_type',
        'target_filters',
        'delay_min',
        'delay_max',
        'max_per_hour',
        'max_per_day',
        'status',
        'scheduled_at',
        'started_at',
        'completed_at',
        'created_by',
    ];

    protected $casts = [
        'target_filters' => 'array',
        'scheduled_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function recipients()
    {
        return $this->hasMany(BroadcastRecipient::class);
    }

    public function getStatusBadgeAttribute()
    {
        $badges = [
            'draft' => 'badge-light-dark',
            'scheduled' => 'badge-light-primary',
            'running' => 'badge-light-info',
            'paused' => 'badge-light-warning',
            'completed' => 'badge-light-success',
            'cancelled' => 'badge-light-danger',
        ];

        return $badges[$this->status] ?? 'badge-light-secondary';
    }
}
