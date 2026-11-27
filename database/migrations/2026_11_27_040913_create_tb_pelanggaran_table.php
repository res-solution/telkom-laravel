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
        Schema::create('tb_pelanggaran', function (Blueprint $table) {
            $table->id('id_pelanggaran');
            $table->unsignedBigInteger('id_mahasiswa');
            $table->unsignedBigInteger('id_jenis');
            $table->text('keterangan');
            $table->date('tanggal_pelanggaran');

            $table->foreign('id_mahasiswa')->references('id')->on('tb_mahasiswa')->cascadeOnDelete();
            $table->foreign('id_jenis')->references('id_jenis')->on('tb_jenis_pelanggaran');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_pelanggaran');
    }
};
