<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Label extends Model
{
    protected $fillable = [
        'id',
        'name',
        'color',
        'is_active',
        'created_at',
        'updated_at',
    ];

    public function customers()
    {
        return $this->belongsToMany(Customer::class, 'customer_labels');
    }
}

