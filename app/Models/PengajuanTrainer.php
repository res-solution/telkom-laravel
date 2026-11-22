<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengajuanTrainer extends Model
{
    protected $table = 'tb_pengajuan_trainer';
    protected $primaryKey = 'id_pengajuan';

    protected $fillable = [
        'nama_pengajar',
        'no_hp_pengajar',
        'email',
        'password',
        'alasan_pengajuan',
        'status',
        'catatan_atasan',
        'diajukan_oleh',
        'diverifikasi_oleh',
        'verified_at',
    ];

    public function pengaju()
    {
        return $this->belongsTo(User::class, 'diajukan_oleh', 'id');
    }

    public function verifikator()
    {
        return $this->belongsTo(User::class, 'diverifikasi_oleh', 'id');
    }
}