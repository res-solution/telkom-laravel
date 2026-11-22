<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Trainer (Atasan Admin)</title>
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
                <h3 class="mb-1 fw-normal">Halaman Verifikasi Trainer</h3>
                <p class="text-muted mb-0 small">
                    Tinjau pengajuan penarikan trainer baru dari Admin. Menyetujui pengajuan akan otomatis mengaktifkan data pengajar dan akun login trainer.
                </p>
            </div>
            <a href="{{ route('mahasiswa.index') }}" class="btn btn-outline-secondary">
                &larr; Kembali ke Data Mahasiswa
            </a>
        </div>

        <!-- TABEL VERIFIKASI PENGAJUAN -->
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50px;">No</th>
                        <th>Kandidat Trainer</th>
                        <th>Email & No. HP</th>
                        <th>Diajukan Oleh</th>
                        <th>Alasan Pengajuan</th>
                        <th style="width: 140px;" class="text-center">Status</th>
                        <th style="width: 190px;" class="text-center">Keputusan Atasan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pengajuans as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>
                            <div class="fw-semibold">{{ $item->nama_pengajar }}</div>
                            <div class="text-muted small">{{ $item->created_at ? $item->created_at->format('d M Y, H:i') : '-' }}</div>
                        </td>
                        <td>
                            <div><code>{{ $item->email }}</code></div>
                            <div class="text-muted small">HP: {{ $item->no_hp_pengajar ?? '-' }}</div>
                        </td>
                        <td>{{ $item->pengaju ? $item->pengaju->name : 'Admin' }}</td>
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
                        <td class="text-center">
                            @if($item->status === 'pending')
                                <form action="{{ route('verifikasi-trainer.approve', $item->id_pengajuan) }}" method="POST" class="d-inline" onsubmit="return confirm('Setujui pengajuan trainer {{ $item->nama_pengajar }}? Akun login trainer akan langsung aktif.')">
                                    @csrf
                                    <button type="submit" class="btn btn-success btn-sm">Setujui</button>
                                </form>
                                <button type="button"
                                    class="btn btn-danger btn-sm"
                                    onclick="bukaModalTolak('{{ route('verifikasi-trainer.reject', $item->id_pengajuan) }}', '{{ addslashes($item->nama_pengajar) }}')">
                                    Tolak
                                </button>
                            @else
                                <div class="small text-muted">
                                    Diverifikasi {{ $item->verified_at ? \Carbon\Carbon::parse($item->verified_at)->format('d/m/Y') : '' }}
                                    @if($item->catatan_atasan)
                                        <div class="text-danger">Ket: {{ $item->catatan_atasan }}</div>
                                    @endif
                                </div>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">
                            Belum ada pengajuan trainer yang masuk dari Admin.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- MODAL TOLAK PENGAJUAN TRAINER -->
    <div class="modal fade" id="modalTolakTrainer" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tolak Pengajuan Trainer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formTolakTrainer" method="POST">
                    @csrf
                    <div class="modal-body">
                        <p class="small text-muted">Anda akan menolak pengajuan untuk kandidat: <strong id="namaKandidatTolak"></strong></p>
                        <div class="mb-2">
                            <label class="form-label">Alasan Penolakan (Opsional)</label>
                            <textarea name="catatan_atasan" class="form-control" rows="3" placeholder="Contoh: Kuota trainer semester ini sudah penuh"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-danger">Tolak Pengajuan</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function bukaModalTolak(actionUrl, namaKandidat) {
            document.getElementById('formTolakTrainer').action = actionUrl;
            document.getElementById('namaKandidatTolak').innerText = namaKandidat;

            var myModal = new bootstrap.Modal(document.getElementById('modalTolakTrainer'));
            myModal.show();
        }

        setTimeout(function() {
            var toast = document.getElementById('toastNotif');
            if (toast) toast.style.display = 'none';
        }, 4000);
    </script>
</body>
</html>