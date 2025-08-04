<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('roles')->insert([
            ['id' => 1, 'role_name' => 'Super admin', 'created_at' => '2021-08-24', 'updated_at' => '2021-08-24', 'deleted_at' => null],
            ['id' => 2, 'role_name' => 'Pimpinan', 'created_at' => '2021-08-24', 'updated_at' => '2021-08-24', 'deleted_at' => null],
            ['id' => 3, 'role_name' => 'Staff kantor', 'created_at' => '2021-08-24', 'updated_at' => '2021-08-28', 'deleted_at' => null],
            ['id' => 4, 'role_name' => 'Karyawan', 'created_at' => '2021-08-24', 'updated_at' => '2021-08-24', 'deleted_at' => null],
        ]);
    }
}