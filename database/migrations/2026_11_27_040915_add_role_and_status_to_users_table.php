<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 20)->default('user');
            $table->string('status_akun', 20)->default('aktif');
            $table->unsignedBigInteger('id_pengajar')->nullable();
            $table->unsignedBigInteger('id_mahasiswa')->nullable();

            $table->foreign('id_pengajar')->references('id_pengajar')->on('tb_pengajar')->nullOnDelete();
            $table->foreign('id_mahasiswa')->references('id')->on('tb_mahasiswa')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['id_pengajar']);
            $table->dropForeign(['id_mahasiswa']);
            $table->dropColumn(['role', 'status_akun', 'id_pengajar', 'id_mahasiswa']);
        });
    }
};
