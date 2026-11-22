<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JenisPelanggaran extends Model
{
    protected $table = 'tb_jenis_pelanggaran';
    protected $primaryKey = 'id_jenis';
    public $timestamps = false;

    protected $fillable = ['nama_jenis', 'poin'];

    public function pelanggarans()
    {
        return $this->hasMany(Pelanggaran::class, 'id_jenis', 'id_jenis');
    }
}