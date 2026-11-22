<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pelanggaran - {{ $mahasiswa->nama }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #ffffff;
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            padding-top: 35px;
        }
        .container-custom {
            max-width: 1100px;
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
        <!-- Header & Tombol Kembali -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h3 class="mb-1 fw-normal">Catatan Pelanggaran Mahasiswa</h3>
                <p class="text-muted mb-0">
                    <strong>{{ $mahasiswa->nama }}</strong> (NRP: {{ $mahasiswa->nrp }}) &bull;
                    Jurusan: {{ $mahasiswa->jurusan ? $mahasiswa->jurusan->nama_jurusan : '-' }}
                </p>
            </div>
            <a href="{{ route('mahasiswa.index') }}" class="btn btn-outline-secondary">
                &larr; Kembali ke Data Mahasiswa
            </a>
        </div>

        <!-- Ringkasan Jumlah per Jenis Pelanggaran -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="card border-dark h-100">
                    <div class="card-body py-2 px-3">
                        <div class="text-muted small">Total Semua Pelanggaran</div>
                        <div class="fs-4 fw-bold">{{ $mahasiswa->pelanggarans->count() }} <span class="fs-6 fw-normal text-muted">Kali</span></div>
                    </div>
                </div>
            </div>

            @foreach($rekapJenis as $rj)
                @php
                    $warnaBorder = $rj->nama_jenis == 'Berat' ? 'border-danger text-danger' : ($rj->nama_jenis == 'Menengah' ? 'border-warning text-dark' : 'border-info text-info');
                @endphp
                <div class="col-6 col-md-3">
                    <div class="card {{ $warnaBorder }} h-100">
                        <div class="card-body py-2 px-3">
                            <div class="text-muted small">Pelanggaran {{ $rj->nama_jenis }}</div>
                            <div class="fs-4 fw-bold">{{ $rj->pelanggarans_count }} <span class="fs-6 fw-normal text-muted">Kali</span></div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Tombol Tambah Pelanggaran -->
        <div class="mb-3">
            <div class="d-flex flex-wrap gap-2 mb-3">
                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#modalTambahPelanggaran">
                        + Catat Pelanggaran Baru
                    </button>
                <a href="{{ route('jenis-pelanggaran.index', ['mahasiswa_id' => $mahasiswa->id]) }}" class="btn btn-outline-primary">
                    Setting Jenis Pelanggaran
                </a>
            </div>
        </div>

        <!-- Tabel Detail Pelanggaran -->
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50px;">No</th>
                        <th style="width: 210px;">Hari & Tanggal</th>
                        <th style="width: 150px;">Jenis Pelanggaran</th>
                        <th>Keterangan Pelanggaran</th>
                        <th style="width: 140px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($mahasiswa->pelanggarans->sortByDesc('tanggal_pelanggaran') as $index => $plg)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>
                            {{ \Carbon\Carbon::parse($plg->tanggal_pelanggaran)->locale('id')->translatedFormat('l, d F Y') }}
                        </td>
                        <td>
                            @php
                                $jenis = $plg->jenisPelanggaran->nama_jenis ?? '-';
                                $badge = $jenis == 'Berat' ? 'bg-danger' : ($jenis == 'Menengah' ? 'bg-warning text-dark' : 'bg-info text-dark');
                            @endphp
                            <span class="badge {{ $badge }}">{{ $jenis }}</span>
                        </td>
                        <td>{{ $plg->keterangan }}</td>
                        <td>
                            <button type="button"
                                class="btn btn-warning btn-sm"
                                onclick="bukaModalEditPelanggaran(
                                    '{{ route('pelanggaran.update', $plg->id_pelanggaran) }}',
                                    '{{ $plg->id_jenis }}',
                                    '{{ $plg->tanggal_pelanggaran }}',
                                    `{{ addslashes($plg->keterangan) }}`
                                )">
                                Edit
                            </button>

                            <form action="{{ route('pelanggaran.destroy', $plg->id_pelanggaran) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus catatan pelanggaran ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">
                            Mahasiswa ini belum memiliki catatan pelanggaran.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- MODAL TAMBAH PELANGGARAN -->
    <div class="modal fade" id="modalTambahPelanggaran" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Catat Pelanggaran Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('pelanggaran.store', $mahasiswa->id) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Jenis Pelanggaran</label>
                            <select name="id_jenis" class="form-select" required>
                                <option value="">-- Pilih Jenis Pelanggaran --</option>
                                @foreach($jenisPelanggarans as $jp)
                                    <option value="{{ $jp->id_jenis }}">{{ $jp->nama_jenis }} (Poin: {{ $jp->poin }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tanggal Kejadian</label>
                            <input type="date" name="tanggal_pelanggaran" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Keterangan Pelanggaran</label>
                            <textarea name="keterangan" class="form-control" rows="3" placeholder="Jelaskan pelanggaran yang dilakukan..." required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-danger">Simpan Pelanggaran</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL EDIT PELANGGARAN -->
    <div class="modal fade" id="modalEditPelanggaran" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Catatan Pelanggaran</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formEditPelanggaran" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Jenis Pelanggaran</label>
                            <select name="id_jenis" id="edit_id_jenis" class="form-select" required>
                                <option value="">-- Pilih Jenis Pelanggaran --</option>
                                @foreach($jenisPelanggarans as $jp)
                                    <option value="{{ $jp->id_jenis }}">{{ $jp->nama_jenis }} (Poin: {{ $jp->poin }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tanggal Kejadian</label>
                            <input type="date" name="tanggal_pelanggaran" id="edit_tanggal_pelanggaran" class="form-control" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Keterangan Pelanggaran</label>
                            <textarea name="keterangan" id="edit_keterangan" class="form-control" rows="3" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">Update</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function bukaModalEditPelanggaran(updateUrl, idJenis, tanggal, keterangan) {
            document.getElementById('formEditPelanggaran').action = updateUrl;
            document.getElementById('edit_id_jenis').value = idJenis;
            document.getElementById('edit_tanggal_pelanggaran').value = tanggal;
            document.getElementById('edit_keterangan').value = keterangan;

            var myModal = new bootstrap.Modal(document.getElementById('modalEditPelanggaran'));
            myModal.show();
        }

        setTimeout(function() {
            var toast = document.getElementById('toastNotif');
            if (toast) toast.style.display = 'none';
        }, 4000);
    </script>
</body>
</html>