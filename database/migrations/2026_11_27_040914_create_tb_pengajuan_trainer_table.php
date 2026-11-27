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
        Schema::create('tb_pengajuan_trainer', function (Blueprint $table) {
            $table->id('id_pengajuan');
            $table->string('nama_pengajar', 100);
            $table->string('no_hp_pengajar', 20)->nullable();
            $table->string('email', 100);
            $table->string('password');
            $table->string('alasan_pengajuan', 500)->nullable();
            $table->string('status', 20)->default('pending');
            $table->string('catatan_atasan', 500)->nullable();
            $table->unsignedBigInteger('diajukan_oleh')->nullable();
            $table->unsignedBigInteger('diverifikasi_oleh')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            $table->foreign('diajukan_oleh')->references('id')->on('users')->nullOnDelete();
            $table->foreign('diverifikasi_oleh')->references('id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_pengajuan_trainer');
    }
};
