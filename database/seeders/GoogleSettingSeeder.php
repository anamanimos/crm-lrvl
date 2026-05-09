<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class GoogleSettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            'google_client_id' => env('GOOGLE_CLIENT_ID', ''),
            'google_client_secret' => env('GOOGLE_CLIENT_SECRET', ''),
            'google_sync_enabled' => '0', // Default off
        ];

        foreach ($settings as $key => $value) {
            Setting::set($key, $value);
        }
    }
}
