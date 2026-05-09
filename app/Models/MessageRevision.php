<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MessageRevision extends Model
{
    public $timestamps = false;
    
    protected $fillable = [
        'message_id', 'old_content', 'new_content'
    ];

    public function message()
    {
        return $this->belongsTo(Message::class);
    }
}
