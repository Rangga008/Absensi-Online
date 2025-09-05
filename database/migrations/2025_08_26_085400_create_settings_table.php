<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSettingsTable extends Migration
{
    public function up()
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        // Insert default settings
        DB::table('settings')->insert([
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
            ['created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('settings');
    }
}