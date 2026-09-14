<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            ['key' => 'facebook', 'value' => 'https://facebook.com/volta'],
            ['key' => 'tiktok', 'value' => 'https://tiktok.com/@volta'],
            ['key' => 'youtube', 'value' => 'https://youtube.com/volta'],
            ['key' => 'twitter', 'value' => 'https://twitter.com/volta'],
            ['key' => 'instagram', 'value' => 'https://instagram.com/volta'],
            ['key' => 'location', 'value' => 'Cairo, Egypt'],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(['key' => $setting['key']], ['value' => $setting['value']]);
        }
    }
}
