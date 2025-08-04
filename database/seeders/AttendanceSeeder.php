<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AttendanceSeeder extends Seeder
{
    public function run()
    {
        DB::table('attendance')->insert([
            [
                'id' => 1,
                'user_id' => 12,
                'present_date' => '2021-08-25',
                'present_at' => '2021-08-25 00:00:00',
                'description' => 'Terlambat', // ✅ diganti sesuai ENUM
                'created_at' => '2021-08-25 00:00:00',
                'updated_at' => '2021-08-25 00:00:00',
                'deleted_at' => null
            ],
            [
                'id' => 3,
                'user_id' => 9,
                'present_date' => '2021-08-25',
                'present_at' => '2021-08-26 00:00:00',
                'description' => 'Hadir', // ✅ huruf besar sesuai ENUM
                'created_at' => '2021-08-26 00:00:00',
                'updated_at' => '2021-08-26 00:00:00',
                'deleted_at' => '2021-08-26 00:00:00'
            ],
            [
                'id' => 4,
                'user_id' => 11,
                'present_date' => '2021-08-25',
                'present_at' => '2021-06-06 00:00:00',
                'description' => 'Izin', // ✅ diganti ke ENUM valid
                'created_at' => '2021-08-28 00:00:00',
                'updated_at' => '2021-08-28 00:00:00',
                'deleted_at' => null
            ],
            [
                'id' => 5,
                'user_id' => 12,
                'present_date' => '2021-08-25',
                'present_at' => '2021-08-29 00:00:00',
                'description' => 'Hadir',
                'created_at' => '2021-08-29 00:00:00',
                'updated_at' => '2021-08-29 00:00:00',
                'deleted_at' => null
            ],
            [
                'id' => 6,
                'user_id' => 12,
                'present_date' => '2021-08-25',
                'present_at' => '2021-08-29 07:59:56',
                'description' => 'Hadir',
                'created_at' => '2021-08-29 07:59:56',
                'updated_at' => '2021-08-29 07:59:56',
                'deleted_at' => null
            ],
            [
                'id' => 7,
                'user_id' => 12,
                'present_date' => '2021-08-25',
                'present_at' => '2021-08-29 08:00:23',
                'description' => 'Hadir',
                'created_at' => '2021-08-29 08:00:23',
                'updated_at' => '2021-08-29 08:00:23',
                'deleted_at' => null
            ],
        ]);
    }
}