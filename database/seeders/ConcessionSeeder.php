<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ConcessionSeeder extends Seeder
{
    public function run()
    {
        DB::table('concession')->insert([
            [
                'user_id' => 2,
                'reason' => 'sakit',
                'description' => 'Gak enak badan',
                'start_date' => '2021-08-25',
                'end_date' => '2021-08-26',
                'status' => 'approved',
                'created_at' => now(),
                'updated_at' => now()
            ],
            // Add other records with the new structure
        ]);
    }
}