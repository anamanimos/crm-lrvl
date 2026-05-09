<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = [
        'uuid', 'customer_id', 'wa_group_id', 'company_id', 'wa_message_id', 'wa_timestamp',
        'reply_message_id', 'reply_content', 'reply_sender_name', 'type',
        'direction', 'sender_type', 'user_id', 'is_external_reply', 'content',
        'media_url', 'media_path', 'media_local_path', 'media_meta',
        'media_status', 'media_attempts', 'media_last_error', 'media_started_at',
        'media_uploaded_at', 'media_log', 'status', 'is_deleted', 'is_edited'
    ];

    protected $appends = ['created_at_ts'];

    public function getCreatedAtTsAttribute()
    {
        return $this->created_at ? $this->created_at->timestamp : 0;
    }

    protected $casts = [
        'media_meta' => 'array',
        'media_log' => 'array',
        'media_uploaded_at' => 'datetime',
        'media_started_at' => 'datetime',
    ];

    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function replyMessage()
    {
        return $this->belongsTo(Message::class, 'reply_message_id', 'wa_message_id');
    }

    public function waGroup()
    {
        return $this->belongsTo(WaGroup::class);
    }

    public function revisions()
    {
        return $this->hasMany(MessageRevision::class)->orderBy('created_at', 'desc');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
