<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Jurusan extends Model
{
    protected $table = 'tb_jurusan';
    protected $primaryKey = 'kode_jurusan';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = ['kode_jurusan', 'nama_jurusan'];

    // Relasi One-to-Many ke tabel tb_mahasiswa (Wajib untuk fungsi withCount)
    public function mahasiswas()
    {
        return $this->hasMany(Mahasiswa::class, 'kode_jurusan', 'kode_jurusan');
    }
}