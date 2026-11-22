<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pelanggaran extends Model
{
    protected $table = 'tb_pelanggaran';
    protected $primaryKey = 'id_pelanggaran';
    public $timestamps = false;

    protected $fillable = [
        'id_mahasiswa',
        'id_jenis',
        'keterangan',
        'tanggal_pelanggaran'
    ];

    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class, 'id_mahasiswa', 'id');
    }

    public function jenisPelanggaran()
    {
        return $this->belongsTo(JenisPelanggaran::class, 'id_jenis', 'id_jenis');
    }
}