<?php

if (!function_exists('format_phone')) {
    /**
     * Format phone number to WhatsApp format (with country code)
     */
    function format_phone($phone) {
        if (empty($phone)) {
            return '';
        }

        // Remove all non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', (string)$phone);
        
        if (empty($phone)) {
            return '';
        }

        // If starts with 0, replace with 62
        if (substr($phone, 0, 1) === '0') {
            $phone = '62' . substr($phone, 1);
        }
        
        // If doesn't start with country code, add 62
        if (substr($phone, 0, 2) !== '62') {
            $phone = '62' . $phone;
        }
        
        return $phone;
    }
}

if (!function_exists('format_phone_display')) {
    /**
     * Format phone number for display (readable format)
     */
    function format_phone_display($phone) {
        if (empty($phone)) {
            return '-';
        }

        $phone = format_phone($phone);
        if (empty($phone)) {
            return '-';
        }
        
        // Format: +62 812-3456-7890
        if (strlen($phone) >= 10) {
            return '+' . substr($phone, 0, 2) . ' ' . 
                   substr($phone, 2, 3) . '-' . 
                   substr($phone, 5, 4) . '-' . 
                   substr($phone, 9);
        }
        
        return '+' . $phone;
    }
}

if (!function_exists('generate_initials')) {
    /**
     * Generate initials from name (multibyte & emoji safe)
     */
    function generate_initials($name) {
        if (empty($name)) {
            return '?';
        }
        
        // Strip emojis and non-alphanumeric characters, keeping standard letters & numbers
        $clean = preg_replace('/[^\p{L}\p{N}\s]/u', '', (string)$name);
        $clean = trim((string)$clean);

        // If cleaning resulted in empty (e.g. name was pure emojis or symbols), fallback
        if (empty($clean)) {
            $fallback = trim((string)$name);
            return mb_strtoupper(mb_substr($fallback, 0, 2, 'UTF-8'), 'UTF-8');
        }

        $words = preg_split('/\s+/u', $clean);
        $initials = '';

        foreach ($words as $word) {
            if (!empty($word)) {
                $initials .= mb_strtoupper(mb_substr($word, 0, 1, 'UTF-8'), 'UTF-8');
            }
        }

        return mb_substr($initials, 0, 2, 'UTF-8');
    }
}

if (!function_exists('time_ago')) {
    /**
     * Format timestamp to "time ago" format
     */
    function time_ago($datetime) {
        if (empty($datetime)) {
            return '-';
        }
        
        $timestamp = is_numeric($datetime) ? $datetime : strtotime($datetime);
        $diff = time() - $timestamp;
        
        if ($diff < 60) {
            return 'Baru saja';
        } elseif ($diff < 3600) {
            $mins = floor($diff / 60);
            return $mins . ' menit lalu';
        } elseif ($diff < 86400) {
            $hours = floor($diff / 3600);
            return $hours . ' jam lalu';
        } elseif ($diff < 604800) {
            $days = floor($diff / 86400);
            return $days . ' hari lalu';
        } else {
            return date('d M Y', $timestamp);
        }
    }
}
