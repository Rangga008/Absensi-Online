<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ConcessionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('concession')->insert([
            ['id' => 1, 'user_id' => 2, 'reason' => 'sakit', 'description' => 'Gak enak badan', 'created_at' => '2021-08-25', 'updated_at' => '2021-08-25', 'deleted_at' => null],
            ['id' => 2, 'user_id' => 4, 'reason' => 'izin', 'description' => 'Izin ke bogor pulang', 'created_at' => '2021-08-25', 'updated_at' => '2021-08-25', 'deleted_at' => null],
            ['id' => 5, 'user_id' => 12, 'reason' => 'sakit', 'description' => 'lagi demam tinggi', 'created_at' => '2021-08-27', 'updated_at' => '2021-08-27', 'deleted_at' => null],
            ['id' => 6, 'user_id' => 12, 'reason' => 'cuti', 'description' => 'Mau ke kondangan sodara', 'created_at' => '2021-08-27', 'updated_at' => '2021-08-27', 'deleted_at' => null],
            ['id' => 7, 'user_id' => 4, 'reason' => 'sakit', 'description' => 'TIPES', 'created_at' => '2021-08-28', 'updated_at' => '2021-08-28', 'deleted_at' => null],
            ['id' => 8, 'user_id' => 12, 'reason' => 'sakit', 'description' => 'Test', 'created_at' => '2021-08-28', 'updated_at' => '2021-08-28', 'deleted_at' => null],
            ['id' => 9, 'user_id' => 12, 'reason' => 'sakit', 'description' => 'Lagi kena kopit', 'created_at' => '2021-08-28', 'updated_at' => '2021-08-28', 'deleted_at' => null],
        ]);
    }
}