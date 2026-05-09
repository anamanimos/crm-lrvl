<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
        'id',
        'uuid',
        'company_id',
        'wa_number',
        'lid',
        'name',
        'avatar',
        'avatar_last_updated',
        'email',
        'address',
        'dob',
        'gender',
        'assigned_user_id',
        'notes',
        'last_chat_at',
        'is_archived',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'last_chat_at' => 'datetime',
    ];


    public function labels()
    {
        return $this->belongsToMany(Label::class, 'customer_labels');
    }

    public function deals()
    {
        return $this->hasMany(Deal::class);
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
