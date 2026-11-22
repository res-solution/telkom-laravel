<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Mahasiswa extends Model
{
    use SoftDeletes;

    protected $table = 'tb_mahasiswa';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'nrp', 
        'nama', 
        'alamat', 
        'semester', 
        'kode_jurusan', 
        'id_pengajar',
        'wali_murid', 
        'no_telp'
    ];

    public function jurusan()
    {
        return $this->belongsTo(Jurusan::class, 'kode_jurusan', 'kode_jurusan');
    }

    public function pengajar()
    {
        return $this->belongsTo(Pengajar::class, 'id_pengajar', 'id_pengajar');
    }

    public function pelanggarans()
    {
        return $this->hasMany(Pelanggaran::class, 'id_mahasiswa', 'id');
    }
}