<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendanceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Ensure table doesn't exist
        Schema::dropIfExists('attendance');
        
        // Create fresh attendance table
        Schema::create('attendance', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('user_id');
    $table->enum('description', [
        'Hadir', 
        'Terlambat', 
        'Sakit', 
        'Izin', 
        'Dinas Luar', 
        'WFH'
    ]);
    $table->decimal('latitude', 10, 7)->nullable();
    $table->decimal('longitude', 10, 7)->nullable();
    $table->string('photo_path', 255)->nullable();
    $table->string('ip_address', 45)->nullable();
    $table->text('user_agent')->nullable();
    $table->decimal('distance', 10, 2)->nullable();
    $table->date('present_date')->nullable();
    $table->timestamp('present_at')->nullable();

    // Checkout related fields
    $table->timestamp('checkout_at')->nullable();
    $table->decimal('checkout_latitude', 10, 7)->nullable();
    $table->decimal('checkout_longitude', 10, 7)->nullable();
    $table->string('checkout_photo_path', 255)->nullable();
    $table->decimal('checkout_distance', 10, 2)->nullable();
    $table->text('checkout_user_agent')->nullable();
    $table->string('checkout_ip_address', 45)->nullable();

    // Durasi kerja
    $table->integer('work_duration_minutes')->nullable();

    $table->timestamps();
    $table->softDeletes();

    // Indexes & foreign key
    $table->index('checkout_at');
    $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
    $table->index(['user_id', 'present_date']);
    $table->index('present_date');
    $table->index('user_id');
});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('attendance');
    }
}