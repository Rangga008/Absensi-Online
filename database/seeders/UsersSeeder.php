<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            [
                'id' => 1, 
                'name' => 'Rangga Kosasih', 
                'email' => 'rizk.rangga09@gmail.com', 
                'password' => '$2y$10$GybjFHcVz2cMlNeICI./eOmxNg525kFRBwCoBkF1ljsgkyb1sB5.2', // Hash password yang benar
                'phone' => '081220670820', 
                'address' => 'Jl pahlawan GG senang rahayu 1 no 8 rt 4 rw 15', 
                'role_id' => 1, 
                'created_at' => '2021-08-24', 
                'updated_at' => '2021-08-28', 
                'deleted_at' => null
            ],
            [
                'id' => 2, 
                'name' => 'aldiansyah ibrahim', 
                'email' => 'aldi@gmail.com', 
                'password' => Hash::make('123456'), // Hash password yang benar
                'phone' => '0892928271232', 
                'address' => 'cimareme bandung barat', 
                'role_id' => 1, 
                'created_at' => '2021-08-24', 
                'updated_at' => '2021-08-28', 
                'deleted_at' => null
            ],
            [
                'id' => 3, 
                'name' => 'andrean', 
                'email' => 'andrean@gmail.com', // Ubah email yang duplikat
                'password' => '$2y$10$dNLD2BTWJCSk.1lpYFtTFuKULneOGiU83U9890z5b0yLB2NVUfofm', 
                'phone' => '0892928271232', 
                'address' => 'cimareme', 
                'role_id' => 1, 
                'created_at' => '2021-08-24', 
                'updated_at' => '2021-08-24', 
                'deleted_at' => '2021-08-24'
            ],
            [
                'id' => 4, 
                'name' => 'alfian', 
                'email' => 'alfian@gmail.com', // Ubah email
                'password' => '$2y$10$NxytEdg0SXQORCVuSdGJfuK1O8B1dFh3lQ78cV3guFdqOzEqoPJtq', 
                'phone' => '089128931212', 
                'address' => 'bogor', 
                'role_id' => 1, 
                'created_at' => '2021-08-24', 
                'updated_at' => '2021-08-24', 
                'deleted_at' => null
            ],
            [
                'id' => 5, 
                'name' => 'ahmad', 
                'email' => 'ahmad@gmail.com', 
                'password' => '$2y$10$EMz5FH2L4hQWu7gIAQydWegTqmhT7kPGwx7Zrey7jGICs.ODCwsLK', 
                'phone' => '089123123123', 
                'address' => 'bandung', 
                'role_id' => 1, 
                'created_at' => '2021-08-24', 
                'updated_at' => '2021-08-24', 
                'deleted_at' => null
            ],
            [
                'id' => 6, 
                'name' => 'ahmad oriza', 
                'email' => 'ahmad.oriza@gmail.com', // Ubah email yang duplikat
                'password' => '$2y$10$ywSpMZyWCiwCdLH1f3qMxu4KCM.AciLeKrsncaZ5QU75Jf8v64hgK', 
                'phone' => '089123123123', 
                'address' => 'bandung', 
                'role_id' => 1, 
                'created_at' => '2021-08-24', 
                'updated_at' => '2021-08-24', 
                'deleted_at' => null
            ],
            [
                'id' => 7, 
                'name' => 'fajar', 
                'email' => 'fajar@gmail.com', 
                'password' => '$2y$10$VuzHPA6h4XdUpevzs3h9N.vxRctgaXJXgGKmmR4aoo6AwRuH1AgFS', 
                'phone' => '0892189832132', 
                'address' => 'Bandung', 
                'role_id' => 4, 
                'created_at' => '2021-08-24', 
                'updated_at' => '2021-08-25', 
                'deleted_at' => '2021-08-25'
            ],
            [
                'id' => 8, 
                'name' => 'oriza ahmad', 
                'email' => 'oriza@gmail.com', 
                'password' => '$2y$10$k.SlAooBhzO8HoDpmez4xehle9fL3D3zmW4uWQuFt2uJo2025s4n6', 
                'phone' => '089123123123', 
                'address' => 'Carinign', 
                'role_id' => 3, 
                'created_at' => '2021-08-24', 
                'updated_at' => '2021-08-24', 
                'deleted_at' => null
            ],
            [
                'id' => 9, 
                'name' => 'Fajar', 
                'email' => 'fajar.kedua@gmail.com', // Ubah email yang duplikat dengan fajar
                'password' => '$2y$10$zop71UcFKD0sbkvff.naiOA2Ak7Qo4Xr/9kxMryEMQ7gwsBLXyEhO', 
                'phone' => '081293898123', 
                'address' => 'Bandung', 
                'role_id' => 4, 
                'created_at' => '2021-08-25', 
                'updated_at' => '2021-08-25', 
                'deleted_at' => null
            ],
            [
                'id' => 10, 
                'name' => 'asep nughraha', 
                'email' => 'asep@gmail.com', // Ubah email
                'password' => '$2y$10$aaEk75UOBC1RzIKnCOuIzugyMo9RwPL5j836H.AyyTY2O3udQQfMm', 
                'phone' => '089879789789', 
                'address' => 'Bandung', 
                'role_id' => 1, 
                'created_at' => '2021-08-25', 
                'updated_at' => '2021-08-25', 
                'deleted_at' => '2021-08-25'
            ],
            [
                'id' => 11, 
                'name' => 'Binza', 
                'email' => 'binza@gmail.com', 
                'password' => '$2y$10$4hYwdwDFiUezMNCJ5fFxrONOqycKPWVs3GPkVzYrm2UhDv1M3uqh.', 
                'phone' => '089123123123', 
                'address' => 'Cimareme', 
                'role_id' => 3, 
                'created_at' => '2021-08-26', 
                'updated_at' => '2021-08-26', 
                'deleted_at' => null
            ],
            [
                'id' => 12, 
                'name' => 'aldian', 
                'email' => 'aldian@gmail.com', 
                'password' => '$2y$10$FYsIRlpnUSSXqpwy/B0mVecDMcTnUttWf5WgzTmQSuqTrGGGbjs6S', 
                'phone' => '089123123123', 
                'address' => 'Cimareme', 
                'role_id' => 3, 
                'created_at' => '2021-08-26', 
                'updated_at' => '2021-08-26', 
                'deleted_at' => null
            ],
            [
                'id' => 13, 
                'name' => 'igo', 
                'email' => 'igo@gmail.com', 
                'password' => '$2y$10$Wrt8CTSBMzqqCwIKtKiDZu/KIAEpPN2PvnMS/EK041O72a20JsMxS', 
                'phone' => '08929282712', 
                'address' => 'Cimareme', 
                'role_id' => 1, 
                'created_at' => '2021-08-26', 
                'updated_at' => '2021-08-26', 
                'deleted_at' => null
            ],
            [
                'id' => 14, 
                'name' => 'Ukuyy', 
                'email' => 'ukuy@gmail.com', 
                'password' => '$2y$10$n5rRKIZR8aV3vr2TezZa9uwhE3mN3PkLGeALq8ZCm3PPmMlhkdGFa', 
                'phone' => '089123123123', 
                'address' => 'Cikole', 
                'role_id' => 1, 
                'created_at' => '2021-08-28', 
                'updated_at' => '2021-08-28', 
                'deleted_at' => null
            ],
        ]);
    }
}