<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Jenis Pelanggaran</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #ffffff;
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            padding-top: 35px;
        }
        .container-custom {
            max-width: 950px;
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
        <div class="d-flex justify-content-between align-items-center mb-4">
            @if($idMahasiswa)
                <a href="{{ route('pelanggaran.show', $idMahasiswa) }}" class="btn btn-outline-secondary">
                    &larr; Kembali ke Data Pelanggaran Mahasiswa
                </a>
            @else
                <a href="{{ route('mahasiswa.index') }}" class="btn btn-outline-secondary">
                    &larr; Kembali ke Data Mahasiswa
                </a>
            @endif
        </div>

        <!-- Form Tambah Jenis Pelanggaran Baru -->
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-light fw-semibold">Tambah Kategori Pelanggaran Baru</div>
            <div class="card-body">
                <form action="{{ route('jenis-pelanggaran.store') }}" method="POST" class="row g-3 align-items-end">
                    @csrf
                    <div class="col-md-6">
                        <label class="form-label">Nama Jenis Pelanggaran</label>
                        <input type="text" name="nama_jenis" class="form-control" placeholder="Contoh: Sangat Berat / Akademik" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Bobot Poin</label>
                        <input type="number" name="poin" class="form-control" placeholder="Contoh: 100" min="1" required>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary w-100">+ Tambah Jenis</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabel Daftar Jenis Pelanggaran -->
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead class="table-light">
                    <tr>
                        <th style="width: 60px;">No</th>
                        <th style="width: 90px;">ID Jenis</th>
                        <th>Nama Jenis Pelanggaran</th>
                        <th style="width: 130px;" class="text-center">Bobot Poin</th>
                        <th style="width: 190px;" class="text-center">Dipakai di Catatan</th>
                        <th style="width: 150px;" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($jenisPelanggarans as $index => $jp)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td class="text-muted">#{{ $jp->id_jenis }}</td>
                        <td class="fw-semibold">{{ $jp->nama_jenis }}</td>
                        <td class="text-center">
                            <span class="badge bg-dark">{{ $jp->poin }} Poin</span>
                        </td>
                        <td class="text-center">
                            <span class="badge {{ $jp->pelanggarans_count > 0 ? 'bg-info text-dark' : 'bg-secondary' }}">
                                {{ $jp->pelanggarans_count }} Pelanggaran
                            </span>
                        </td>
                        <td class="text-center">
                            <button type="button"
                                class="btn btn-warning btn-sm"
                                onclick="bukaModalEditJenis(
                                    '{{ route('jenis-pelanggaran.update', $jp->id_jenis) }}',
                                    '{{ addslashes($jp->nama_jenis) }}',
                                    '{{ $jp->poin }}'
                                )">
                                Edit
                            </button>

                            <form action="{{ route('jenis-pelanggaran.destroy', $jp->id_jenis) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus jenis pelanggaran {{ $jp->nama_jenis }}?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-3">
                            Belum ada data jenis pelanggaran.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- MODAL EDIT JENIS PELANGGARAN -->
    <div class="modal fade" id="modalEditJenis" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Jenis Pelanggaran</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formEditJenis" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nama Jenis Pelanggaran</label>
                            <input type="text" name="nama_jenis" id="edit_nama_jenis" class="form-control" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Bobot Poin</label>
                            <input type="number" name="poin" id="edit_poin" class="form-control" min="1" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">Update Perubahan</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function bukaModalEditJenis(updateUrl, namaJenis, poin) {
            document.getElementById('formEditJenis').action = updateUrl;
            document.getElementById('edit_nama_jenis').value = namaJenis;
            document.getElementById('edit_poin').value = poin;

            var myModal = new bootstrap.Modal(document.getElementById('modalEditJenis'));
            myModal.show();
        }

        setTimeout(function() {
            var toast = document.getElementById('toastNotif');
            if (toast) toast.style.display = 'none';
        }, 4000);
    </script>
</body>
</html>