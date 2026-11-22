<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pengajar extends Model
{
    protected $table = 'tb_pengajar';
    protected $primaryKey = 'id_pengajar';
    public $timestamps = false;

    protected $fillable = ['nama_pengajar', 'no_hp_pengajar'];

    public function mahasiswas()
    {
        return $this->hasMany(Mahasiswa::class, 'id_pengajar', 'id_pengajar');
    }
}