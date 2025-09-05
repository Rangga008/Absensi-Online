<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $settings = [
            ['key' => 'app_name', 'value' => 'Presensi Online'],
            ['key' => 'company_name', 'value' => 'SMKN 2 Bandung'],
            ['key' => 'office_lat', 'value' => '-6.906000000000'],
            ['key' => 'office_lng', 'value' => '107.623400000000'],
            ['key' => 'max_distance', 'value' => '50000'],
            ['key' => 'timezone', 'value' => 'Asia/Jakarta'],
            ['key' => 'work_start_time', 'value' => '07:00'],
            ['key' => 'work_end_time', 'value' => '16:00'],
            ['key' => 'late_threshold', 'value' => '08:00'],
            ['key' => 'logo', 'value' => null],
        ];
        
        foreach ($settings as $setting) {
            DB::table('settings')->updateOrInsert(
                ['key' => $setting['key']],
                ['value' => $setting['value']]
            );
        }
        
        $this->command->info('Settings seeded successfully!');
    }
}
