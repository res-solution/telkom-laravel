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
        Schema::create('tb_mahasiswa', function (Blueprint $table) {
            $table->id();
            $table->string('nrp', 20)->unique();
            $table->string('nama', 100);
            $table->text('alamat');
            $table->integer('semester');
            $table->string('kode_jurusan', 10)->nullable();
            $table->unsignedBigInteger('id_pengajar')->nullable();
            $table->string('wali_murid', 100)->nullable();
            $table->string('no_telp', 20);
            $table->softDeletes();

            $table->foreign('kode_jurusan')->references('kode_jurusan')->on('tb_jurusan')->nullOnDelete();
            $table->foreign('id_pengajar')->references('id_pengajar')->on('tb_pengajar')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_mahasiswa');
    }
};
