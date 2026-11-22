<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengajuan Trainer Baru</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #ffffff;
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            padding-top: 35px;
        }
        .container-custom {
            max-width: 1150px;
            margin: 0 auto;
        }
        .floating-toast {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            background-color: #0d6efd;
            color: #ffffff;
            padding: 14px 20px;
            border-radius: 8px;
            min-width: 310px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 12px rgba(13, 110, 253, 0.35);
            z-index: 2000;
            font-size: 15px;
        }
        .floating-toast.error-toast {
            background-color: #dc3545;
            box-shadow: 0 4px 12px rgba(220, 53, 69, 0.35);
        }
        .floating-toast .close-btn {
            background: transparent;
            border: none;
            color: rgba(255, 255, 255, 0.85);
            font-size: 22px;
            line-height: 1;
            cursor: pointer;
            margin-left: 20px;
        }
        .table th, .table td {
            vertical-align: middle;
            padding: 10px 12px;
        }
    </style>
</head>
<body>

    @if(session('sukses'))
    <div class="floating-toast" id="toastNotif">
        <span>{{ session('sukses') }}</span>
        <button type="button" class="close-btn" onclick="document.getElementById('toastNotif').style.display='none'">&times;</button>
    </div>
    @endif

    @if($errors->any())
    <div class="floating-toast error-toast" id="toastError">
        <span>{{ $errors->first() }}</span>
        <button type="button" class="close-btn" onclick="document.getElementById('toastError').style.display='none'">&times;</button>
    </div>
    @endif

    <div class="container-custom px-3">
        <!-- HEADER HALAMAN -->
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 pb-2 border-bottom">
            <div>
                <h3 class="mb-1 fw-normal">Halaman Pengajuan Trainer</h3>
                <p class="text-muted mb-0 small">
                    Admin mengajukan kandidat trainer baru di sini. Data trainer baru akan aktif setelah diverifikasi oleh Atasan Admin.
                </p>
            </div>
            <a href="{{ route('mahasiswa.index') }}" class="btn btn-outline-secondary">
                &larr; Kembali ke Data Mahasiswa
            </a>
        </div>

        <!-- TOMBOL UTAMA AJUKAN TRAINER -->
        <div class="mb-3">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAjukanTrainer">
                + Ajukan Trainer
            </button>
        </div>

        <!-- TABEL DAFTAR PENGAJUAN TRAINER -->
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50px;">No</th>
                        <th>Nama Trainer</th>
                        <th>Email Login & No. HP</th>
                        <th>Alasan Pengajuan</th>
                        <th style="width: 140px;" class="text-center">Status Verifikasi</th>
                        <th>Catatan / Verifikator</th>
                        <th style="width: 130px;" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pengajuans as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>
                            <div class="fw-semibold">{{ $item->nama_pengajar }}</div>
                            <div class="text-muted small">Diajukan: {{ $item->created_at ? $item->created_at->format('d M Y, H:i') : '-' }}</div>
                        </td>
                        <td>
                            <div><code>{{ $item->email }}</code></div>
                            <div class="text-muted small">HP: {{ $item->no_hp_pengajar ?? '-' }}</div>
                        </td>
                        <td>{{ $item->alasan_pengajuan ?? '-' }}</td>
                        <td class="text-center">
                            @if($item->status === 'pending')
                                <span class="badge bg-warning text-dark">Menunggu Verifikasi</span>
                            @elseif($item->status === 'disetujui')
                                <span class="badge bg-success">Disetujui</span>
                            @else
                                <span class="badge bg-danger">Ditolak</span>
                            @endif
                        </td>
                        <td class="small">
                            @if($item->status === 'pending')
                                <span class="text-muted">Menunggu persetujuan Atasan Admin</span>
                            @else
                                <div>Oleh: <strong>{{ $item->verifikator ? $item->verifikator->name : '-' }}</strong></div>
                                @if($item->catatan_atasan)
                                    <div class="text-danger">Catatan: {{ $item->catatan_atasan }}</div>
                                @endif
                            @endif
                        </td>
                        <td class="text-center">
                            @if($item->status === 'pending')
                                <form action="{{ route('pengajuan-trainer.destroy', $item->id_pengajuan) }}" method="POST" onsubmit="return confirm('Batalkan pengajuan trainer {{ $item->nama_pengajar }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm">Batalkan</button>
                                </form>
                            @else
                                <span class="text-muted small">Selesai</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">
                            Belum ada pengajuan trainer baru. Klik tombol <strong>+ Ajukan Trainer</strong> untuk mengirim pengajuan ke Atasan Admin.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- MODAL FORM AJUKAN TRAINER (TOMBOL KIRIM & BATAL) -->
    <div class="modal fade" id="modalAjukanTrainer" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Form Pengajuan Trainer Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('pengajuan-trainer.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nama Lengkap Trainer <span class="text-danger">*</span></label>
                            <input type="text" name="nama_pengajar" class="form-control" placeholder="Contoh: Ir. Budi Santoso, M.T." required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">No. HP Trainer</label>
                            <input type="text" name="no_hp_pengajar" class="form-control" placeholder="Contoh: 081234567890">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email Akun Login Trainer <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" placeholder="Contoh: budi.trainer@kampus.com" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password Akun Login <span class="text-danger">*</span></label>
                            <input type="text" name="password" class="form-control" value="password" placeholder="Minimal 6 karakter" required>
                            <div class="form-text">Password ini akan aktif otomatis setelah disetujui Atasan Admin.</div>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Alasan / Kebutuhan Penarikan Trainer</label>
                            <textarea name="alasan_pengajuan" class="form-control" rows="3" placeholder="Contoh: Dibutuhkan tambahan wali dosen untuk jurusan Teknik Informatika"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Kirim</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        setTimeout(function() {
            var toast = document.getElementById('toastNotif');
            if (toast) toast.style.display = 'none';
        }, 4000);
    </script>
</body>
</html>