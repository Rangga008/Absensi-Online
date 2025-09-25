<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWorkTimesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('work_times', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., "Morning Shift", "Afternoon Shift"
            $table->time('start_time'); // Work start time
            $table->time('end_time'); // Work end time
            $table->time('late_threshold')->default('08:00'); // Time after which attendance is marked as late
            $table->boolean('is_active')->default(true); // Whether this shift is active
            $table->text('description')->nullable(); // Optional description
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('work_times');
    }
}
