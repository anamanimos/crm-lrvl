<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class StorageSettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            'cloudinary_enabled' => '1',
            'cloudinary_cloud_name' => 'ddafcd9cb',
            'cloudinary_api_key' => '198778272498738',
            'cloudinary_api_secret' => 'YKuDPPrBMyb3pL5uI3FM-lJZvIg',
            'cloudinary_folder' => 'crm-wachat',
            'minio_enabled' => '1',
            'minio_endpoint' => 'https://storage.damaijaya.my.id/',
            'minio_access_key' => '9YG6RuClOKyAQsyQGzST',
            'minio_secret_key' => 'xzWeOUlssNOJSx54ZNr93rG8JAjz6uZoMdrXoRYa',
            'minio_bucket' => 'crm-wa',
            'minio_region' => 'mlg',
        ];

        foreach ($settings as $key => $value) {
            Setting::set($key, $value);
        }
    }
}
