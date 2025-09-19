<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFilePathToConcessionTable extends Migration
{
    public function up()
    {
        Schema::table('concession', function (Blueprint $table) {
            $table->string('file_path')->nullable()->after('description'); 
            // `after('description')` opsional, cuma biar posisinya rapi
        });
    }

    public function down()
    {
        Schema::table('concession', function (Blueprint $table) {
            $table->dropColumn('file_path');
        });
    }
}
