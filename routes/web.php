<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MahasiswaController;
use App\Http\Controllers\AuthController;

// Route Login (Hanya untuk tamu yang belum login)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

// Semua Route di bawah ini WAJIB Login
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/', [MahasiswaController::class, 'index']);

    // Route Tong Sampah
    Route::post('tong-sampah/pulihkan/{id}', [MahasiswaController::class, 'restore'])->name('mahasiswa.restore');
    Route::delete('tong-sampah/hapus-permanen/{id}', [MahasiswaController::class, 'forceDelete'])->name('mahasiswa.forceDelete');

    // Route Resource Utama Mahasiswa
    Route::resource('mahasiswa', MahasiswaController::class);

    // Route untuk Kelola Jurusan
    Route::post('jurusan', [MahasiswaController::class, 'storeJurusan'])->name('jurusan.store');
    Route::delete('jurusan/{kode_jurusan}', [MahasiswaController::class, 'destroyJurusan'])->name('jurusan.destroy');

    // Route untuk Kelola Pengajar / Wali (Langsung oleh Atasan Admin)
    Route::post('pengajar', [MahasiswaController::class, 'storePengajar'])->name('pengajar.store');
    Route::delete('pengajar/{id_pengajar}', [MahasiswaController::class, 'destroyPengajar'])->name('pengajar.destroy');

    // Route Detail & CRUD Pelanggaran per Mahasiswa
    Route::get('mahasiswa/{id}/pelanggaran', [MahasiswaController::class, 'showPelanggaran'])->name('pelanggaran.show');
    Route::post('mahasiswa/{id}/pelanggaran', [MahasiswaController::class, 'storePelanggaran'])->name('pelanggaran.store');
    Route::put('pelanggaran/{id_pelanggaran}', [MahasiswaController::class, 'updatePelanggaran'])->name('pelanggaran.update');
    Route::delete('pelanggaran/{id_pelanggaran}', [MahasiswaController::class, 'destroyPelanggaran'])->name('pelanggaran.destroy');

    // Route Kelola & Edit Jenis Pelanggaran
    Route::get('jenis-pelanggaran', [MahasiswaController::class, 'indexJenisPelanggaran'])->name('jenis-pelanggaran.index');
    Route::post('jenis-pelanggaran', [MahasiswaController::class, 'storeJenisPelanggaran'])->name('jenis-pelanggaran.store');
    Route::put('jenis-pelanggaran/{id_jenis}', [MahasiswaController::class, 'updateJenisPelanggaran'])->name('jenis-pelanggaran.update');
    Route::delete('jenis-pelanggaran/{id_jenis}', [MahasiswaController::class, 'destroyJenisPelanggaran'])->name('jenis-pelanggaran.destroy');

    // Route Halaman Pengajuan Trainer (Sisi Admin)
    Route::get('pengajuan-trainer', [MahasiswaController::class, 'indexPengajuanTrainer'])->name('pengajuan-trainer.index');
    Route::post('pengajuan-trainer', [MahasiswaController::class, 'storePengajuanTrainer'])->name('pengajuan-trainer.store');
    Route::delete('pengajuan-trainer/{id_pengajuan}', [MahasiswaController::class, 'destroyPengajuanTrainer'])->name('pengajuan-trainer.destroy');

    // Route Halaman Verifikasi Trainer (Sisi Atasan Admin)
    Route::get('verifikasi-trainer', [MahasiswaController::class, 'indexVerifikasiTrainer'])->name('verifikasi-trainer.index');
    Route::post('verifikasi-trainer/{id_pengajuan}/setujui', [MahasiswaController::class, 'approveTrainer'])->name('verifikasi-trainer.approve');
    Route::post('verifikasi-trainer/{id_pengajuan}/tolak', [MahasiswaController::class, 'rejectTrainer'])->name('verifikasi-trainer.reject');
});