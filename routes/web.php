<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MahasiswaController;

Route::get('/', [MahasiswaController::class, 'index']);

// Route Tong Sampah
Route::post('tong-sampah/pulihkan/{id}', [MahasiswaController::class, 'restore'])->name('mahasiswa.restore');
Route::delete('tong-sampah/hapus-permanen/{id}', [MahasiswaController::class, 'forceDelete'])->name('mahasiswa.forceDelete');

// Route Resource Utama Mahasiswa
Route::resource('mahasiswa', MahasiswaController::class);

// Route untuk Kelola Jurusan
Route::post('jurusan', [MahasiswaController::class, 'storeJurusan'])->name('jurusan.store');
Route::delete('jurusan/{kode_jurusan}', [MahasiswaController::class, 'destroyJurusan'])->name('jurusan.destroy');

// Route untuk Kelola Pengajar / Wali
Route::post('pengajar', [MahasiswaController::class, 'storePengajar'])->name('pengajar.store');
Route::delete('pengajar/{id_pengajar}', [MahasiswaController::class, 'destroyPengajar'])->name('pengajar.destroy');

// Route Detail & CRUD Pelanggaran per Mahasiswa
Route::get('mahasiswa/{id}/pelanggaran', [MahasiswaController::class, 'showPelanggaran'])->name('pelanggaran.show');
Route::post('mahasiswa/{id}/pelanggaran', [MahasiswaController::class, 'storePelanggaran'])->name('pelanggaran.store');
Route::put('pelanggaran/{id_pelanggaran}', [MahasiswaController::class, 'updatePelanggaran'])->name('pelanggaran.update');
Route::delete('pelanggaran/{id_pelanggaran}', [MahasiswaController::class, 'destroyPelanggaran'])->name('pelanggaran.destroy');

// Route Baru: Halaman Kelola & Edit Jenis Pelanggaran
Route::get('jenis-pelanggaran', [MahasiswaController::class, 'indexJenisPelanggaran'])->name('jenis-pelanggaran.index');
Route::post('jenis-pelanggaran', [MahasiswaController::class, 'storeJenisPelanggaran'])->name('jenis-pelanggaran.store');
Route::put('jenis-pelanggaran/{id_jenis}', [MahasiswaController::class, 'updateJenisPelanggaran'])->name('jenis-pelanggaran.update');
Route::delete('jenis-pelanggaran/{id_jenis}', [MahasiswaController::class, 'destroyJenisPelanggaran'])->name('jenis-pelanggaran.destroy');