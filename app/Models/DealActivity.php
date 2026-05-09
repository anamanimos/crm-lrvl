<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DealActivity extends Model
{
    protected $fillable = [
        'deal_id',
        'activity_type',
        'description',
        'file_data',
        'created_by',
    ];

    protected $casts = [
        'file_data' => 'array',
    ];

    public function deal()
    {
        return $this->belongsTo(Deal::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
