<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SalarySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('salary')->insert([
            ['id' => 1, 'user_id' => 12, 'month' => 'June', 'year' => '2021', 'amount' => 2500000, 'created_at' => '2021-08-28', 'updated_at' => '2021-08-28', 'deleted_at' => null],
            ['id' => 3, 'user_id' => 12, 'month' => 'July', 'year' => '2021', 'amount' => 2000000, 'created_at' => '2021-08-27', 'updated_at' => null, 'deleted_at' => null],
            ['id' => 4, 'user_id' => 6, 'month' => 'January', 'year' => '2004', 'amount' => 4000000, 'created_at' => '2021-08-28', 'updated_at' => '2021-08-28', 'deleted_at' => null],
            ['id' => 5, 'user_id' => 12, 'month' => 'August', 'year' => '2021', 'amount' => 3000000, 'created_at' => '2021-08-28', 'updated_at' => '2021-08-28', 'deleted_at' => null],
        ]);
    }
}