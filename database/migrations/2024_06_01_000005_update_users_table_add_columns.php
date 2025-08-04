<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateUsersTableAddColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('password');
            $table->text('address')->nullable()->after('phone');
            $table->unsignedBigInteger('role_id')->default(1)->after('address');
            // Hapus baris ->change() yang menyebabkan error
            // $table->date('updated_at')->nullable()->change();
            $table->softDeletes(); // Ini akan menambah deleted_at dengan cara yang benar
        });

        // Tambah foreign key setelah tabel roles sudah ada
        // Kita perlu memastikan ini dijalankan setelah migration roles
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->dropColumn(['phone', 'address', 'role_id']);
            $table->dropSoftDeletes(); // Menghapus deleted_at
        });
    }
}