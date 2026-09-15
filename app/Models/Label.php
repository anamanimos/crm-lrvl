<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Label extends Model
{
    protected $fillable = [
        'wa_label_id',
        'name',
        'color',
        'is_active',
        'order_index',
        'predefined_id',
    ];

    /**
     * Map WhatsApp Business color index (0-19) to HEX color code.
     */
    public static function colorFromIndex($index): string
    {
        $palette = [
            0 => '#ff8e8e',  // coral / red
            1 => '#ffb878',  // orange
            2 => '#fcc934',  // yellow
            3 => '#78d672',  // light green
            4 => '#57b8ff',  // light blue
            5 => '#9b8cff',  // purple
            6 => '#e072ff',  // pink / magenta
            7 => '#00a884',  // whatsapp teal
            8 => '#1fa855',  // dark green
            9 => '#00a5f4',  // sky blue
            10 => '#54656f', // gray
            11 => '#aebac1', // light gray
            12 => '#d980fa', // lavender
            13 => '#fda7df', // soft pink
            14 => '#ff7979', // soft red
            15 => '#f6e58d', // soft yellow
            16 => '#badc58', // lime
            17 => '#7ed6df', // cyan
            18 => '#e056fd', // violet
            19 => '#686de0', // indigo
        ];

        if (is_numeric($index)) {
            $intIndex = (int) $index;
            return $palette[$intIndex] ?? '#00a884';
        }

        if (is_string($index) && str_starts_with($index, '#')) {
            return $index;
        }

        return '#00a884';
    }

    public function customers()
    {
        return $this->belongsToMany(Customer::class, 'customer_labels');
    }
}

