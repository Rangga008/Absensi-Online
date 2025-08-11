<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMissingColumnsToConcessionTable extends Migration
{
    public function up()
    {
        Schema::table('concession', function (Blueprint $table) {
            $table->date('start_date')->after('reason');
            $table->date('end_date')->after('start_date');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending')->after('end_date');
            $table->dropColumn(['created_at', 'updated_at', 'deleted_at']); // Remove old date columns
        });

        Schema::table('concession', function (Blueprint $table) {
            $table->timestamps(); // Adds created_at and updated_at as timestamps
            $table->softDeletes(); // Adds deleted_at for soft deletes
        });
    }

    public function down()
    {
        Schema::table('concession', function (Blueprint $table) {
            $table->dropColumn(['start_date', 'end_date', 'status']);
            $table->dropTimestamps();
            $table->dropSoftDeletes();
            $table->date('created_at');
            $table->date('updated_at')->nullable();
            $table->date('deleted_at')->nullable();
        });
    }
}