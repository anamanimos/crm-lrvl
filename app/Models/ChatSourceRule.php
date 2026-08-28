<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatSourceRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'keyword',
        'source_name',
        'match_type',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Check if a text matches this rule.
     */
    public function matches(string $text): bool
    {
        if (!$this->is_active || empty($this->keyword)) {
            return false;
        }

        $textLower = mb_strtolower($text, 'UTF-8');
        $keywordLower = mb_strtolower(trim($this->keyword), 'UTF-8');

        return match ($this->match_type) {
            'exact' => $textLower === $keywordLower,
            'starts_with' => str_starts_with($textLower, $keywordLower),
            default => str_contains($textLower, $keywordLower), // 'contains'
        };
    }

    /**
     * Find the first matching active rule for a text.
     */
    public static function findMatch(string $text): ?self
    {
        $rules = self::where('is_active', true)->get();
        foreach ($rules as $rule) {
            if ($rule->matches($text)) {
                return $rule;
            }
        }
        return null;
    }
}
