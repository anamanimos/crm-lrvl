<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AutoReply extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'trigger_type',
        'keyword',
        'response_messages',
        'delay_seconds',
        'active_days',
        'active_times',
        'media_path',
        'media_type',
        'is_active',
    ];

    protected $casts = [
        'response_messages' => 'array',
        'active_days' => 'array',
        'active_times' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get badge class for status
     */
    public function getStatusBadgeAttribute()
    {
        return $this->is_active ? 'badge-light-success' : 'badge-light-danger';
    }

    /**
     * Get label for trigger type
     */
    public function getTriggerLabelAttribute()
    {
        $labels = [
            'keyword' => 'Kata Kunci',
            'first_chat' => 'Chat Pertama',
            'all' => 'Semua Pesan',
        ];

        return $labels[$this->trigger_type] ?? $this->trigger_type;
    }

    /**
     * Check if this auto reply should trigger for the given content
     */
    public function matches($content)
    {
        if (!$this->is_active) {
            return false;
        }

        // 1. Check Schedule (Days)
        $today = strtolower(date('D')); // mon, tue, etc.
        if (!empty($this->active_days) && !in_array($today, $this->active_days)) {
            return false;
        }

        // 2. Check Schedule (Times)
        if (!empty($this->active_times)) {
            $now = date('H:i');
            $inTimeRange = false;
            foreach ($this->active_times as $range) {
                $start = $range['start'] ?? '00:00';
                $end = $range['end'] ?? '23:59';
                if ($now >= $start && $now <= $end) {
                    $inTimeRange = true;
                    break;
                }
            }
            if (!$inTimeRange) {
                return false;
            }
        }

        // 3. Check Trigger Type & Keyword
        switch ($this->trigger_type) {
            case 'keyword':
                if (empty($this->keyword)) return false;
                // Case-insensitive exact match or contains
                $keywords = explode(',', $this->keyword);
                foreach ($keywords as $kw) {
                    $kw = trim($kw);
                    if (empty($kw)) continue;
                    
                    // Simple case-insensitive exact match
                    if (strtolower(trim($content)) === strtolower($kw)) {
                        return true;
                    }
                }
                return false;

            case 'all':
                return true;

            case 'first_chat':
                // Note: 'first_chat' logic might need customer history check 
                // which is better handled in the controller, 
                // but for now we return true if it's the trigger type
                return true;

            default:
                return false;
        }
    }
}
