<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Deal extends Model
{
    protected $fillable = [
        'uuid',
        'title',
        'customer_id',
        'deal_stage_id',
        'expected_value',
        'source',
        'assigned_user_id',
        'next_followup_date',
        'expected_close_date',
        'contact_start_date',
        'lost_reason',
        'is_archived',
    ];

    protected $casts = [
        'expected_value' => 'decimal:2',
        'is_archived' => 'boolean',
        'next_followup_date' => 'datetime',
        'expected_close_date' => 'date',
        'contact_start_date' => 'date',
    ];

    protected static function booted()
    {
        static::creating(function ($deal) {
            if (empty($deal->uuid)) {
                $deal->uuid = (string) Str::uuid();
            }
        });
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function stage()
    {
        return $this->belongsTo(DealStage::class, 'deal_stage_id');
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function activities()
    {
        return $this->hasMany(DealActivity::class)->latest();
    }
}
