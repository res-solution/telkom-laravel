<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Mahasiswa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #ffffff;
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            padding-top: 35px;
        }
        .container-custom {
            max-width: 1250px;
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
        .floating-toast .close-btn:hover {
            color: #ffffff;
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
        <h2 class="mb-3 fw-normal" style="color: #212529;">Data Mahasiswa</h2>

        <!-- KARTU STATISTIK RINGKASAN (HASIL FUNGSI AGREGAT SQL) -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="card border-primary h-100 shadow-sm">
                    <div class="card-body py-3">
                        <div class="text-muted small">Total Mahasiswa</div>
                        <div class="fs-4 fw-bold text-primary">{{ $statistik['total_mahasiswa'] }} <span class="fs-6 fw-normal text-muted">Orang</span></div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-info h-100 shadow-sm">
                    <div class="card-body py-3">
                        <div class="text-muted small">Total Jurusan</div>
                        <div class="fs-4 fw-bold text-info">{{ $statistik['total_jurusan'] }} <span class="fs-6 fw-normal text-muted">Prodi</span></div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-success h-100 shadow-sm">
                    <div class="card-body py-3">
                        <div class="text-muted small">Total Pengajar / Wali</div>
                        <div class="fs-4 fw-bold text-success">{{ $statistik['total_pengajar'] }} <span class="fs-6 fw-normal text-muted">Dosen</span></div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-warning h-100 shadow-sm">
                    <div class="card-body py-3">
                        <div class="text-muted small">Rata-rata Semester</div>
                        <div class="fs-4 fw-bold text-dark">{{ $statistik['rata_semester'] }} <span class="fs-6 fw-normal text-muted">(Max: {{ $statistik['semester_max'] }})</span></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-2 align-items-center justify-content-between mb-3">
            <div class="col-md-auto d-flex flex-wrap gap-2">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambah">
                    Tambah Mahasiswa
                </button>
                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalJurusan">
                    Kelola Jurusan ({{ $jurusans->count() }})
                </button>
                <button type="button" class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#modalPengajar">
                    Kelola Pengajar ({{ $pengajars->count() }})
                </button>
                <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#modalSampah">
                    Tong Sampah ({{ $mahasiswasTerhapus->count() }})
                </button>
            </div>

            <div class="col-md-6">
                <form action="{{ route('mahasiswa.index') }}" method="GET" class="row g-2 justify-content-md-end">
                    <div class="col-sm-5">
                        <input type="text" name="search" class="form-control" placeholder="Cari NRP, Nama, Pengajar..." value="{{ request('search') }}">
                    </div>

                    <div class="col-sm-4">
                        <select name="filter_jurusan" class="form-select" onchange="this.form.submit()">
                            <option value="">-- Semua Jurusan --</option>
                            @foreach($jurusans as $j)
                                <option value="{{ $j->kode_jurusan }}" {{ request('filter_jurusan') == $j->kode_jurusan ? 'selected' : '' }}>
                                    {{ $j->nama_jurusan }} ({{ $j->mahasiswas_count }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-sm-auto d-flex gap-1">
                        <button type="submit" class="btn btn-dark">Cari</button>
                        @if(request('search') || request('filter_jurusan'))
                            <a href="{{ route('mahasiswa.index') }}" class="btn btn-outline-secondary">Reset</a>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th style="width: 50px;">No</th>
                        <th>NRP</th>
                        <th>Jurusan</th>
                        <th>Nama</th>
                        <th>Alamat</th>
                        <th style="width: 85px;">Semester</th>
                        <th>Wali / Pengajar</th>
                        <th>No. Telepon</th>
                        <th style="width: 155px;" class="text-center">Pelanggaran</th>
                        <th style="width: 135px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($mahasiswas as $index => $mhs)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $mhs->nrp }}</td>
                        <td>{{ $mhs->jurusan ? $mhs->jurusan->nama_jurusan : '-' }}</td>
                        <td>{{ $mhs->nama }}</td>
                        <td>{{ $mhs->alamat }}</td>
                        <td>{{ $mhs->semester }}</td>
                        <td>{{ $mhs->pengajar ? $mhs->pengajar->nama_pengajar : '-' }}</td>
                        <td>{{ $mhs->no_telp ?? '-' }}</td>
                        <td class="text-center">
                            <a href="{{ route('pelanggaran.show', $mhs->id) }}"
                               class="btn btn-sm fw-bold px-3 {{ $mhs->pelanggarans_count > 0 ? 'btn-danger' : 'btn-outline-secondary' }}"
                               title="Klik untuk melihat & mengelola detail pelanggaran">
                                {{ $mhs->pelanggarans_count }}
                            </a>
                        </td>
                        <td>
                            <button type="button"
                                class="btn btn-warning btn-sm"
                                onclick="bukaModalEdit(
                                    '{{ route('mahasiswa.update', $mhs->id) }}',
                                    '{{ addslashes($mhs->nrp) }}',
                                    '{{ $mhs->kode_jurusan }}',
                                    '{{ $mhs->id_pengajar ?? '' }}',
                                    '{{ addslashes($mhs->nama) }}',
                                    `{{ addslashes($mhs->alamat) }}`,
                                    '{{ $mhs->semester }}',
                                    '{{ addslashes($mhs->no_telp ?? '') }}'
                                )">
                                Edit
                            </button>

                            <form action="{{ route('mahasiswa.destroy', $mhs->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus data ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="text-center text-muted py-3">
                            Data mahasiswa tidak ditemukan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- MODAL KELOLA PENGAJAR / WALI -->
    <div class="modal fade" id="modalPengajar" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Kelola Data Pengajar / Wali Murid</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('pengajar.store') }}" method="POST" class="row g-2 mb-4 align-items-end">
                        @csrf
                        <div class="col-sm-5">
                            <label class="form-label">Nama Pengajar / Wali</label>
                            <input type="text" name="nama_pengajar" class="form-control" placeholder="Contoh: Dr. Hendra, M.Kom." required>
                        </div>
                        <div class="col-sm-4">
                            <label class="form-label">No. HP Pengajar</label>
                            <input type="text" name="no_hp_pengajar" class="form-control" placeholder="Contoh: 081298765432">
                        </div>
                        <div class="col-sm-3">
                            <button type="submit" class="btn btn-success w-100">+ Tambah Pengajar</button>
                        </div>
                    </form>

                    <table class="table table-bordered table-sm">
                        <thead class="table-light">
                            <tr>
                                <th>Nama Pengajar / Wali</th>
                                <th>No. HP</th>
                                <th style="width: 170px;" class="text-center">Mahasiswa Bimbingan</th>
                                <th style="width: 100px;" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pengajars as $p)
                            <tr>
                                <td class="fw-semibold">{{ $p->nama_pengajar }}</td>
                                <td>{{ $p->no_hp_pengajar ?? '-' }}</td>
                                <td class="text-center">
                                    <span class="badge bg-success">{{ $p->mahasiswas_count }} Mahasiswa</span>
                                </td>
                                <td class="text-center">
                                    <form action="{{ route('pengajar.destroy', $p->id_pengajar) }}" method="POST" onsubmit="return confirm('Hapus pengajar {{ $p->nama_pengajar }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL KELOLA JURUSAN -->
    <div class="modal fade" id="modalJurusan" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Kelola Data Jurusan & Jumlah Mahasiswa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('jurusan.store') }}" method="POST" class="row g-2 mb-4 align-items-end">
                        @csrf
                        <div class="col-sm-3">
                            <label class="form-label">Kode Jurusan</label>
                            <input type="text" name="kode_jurusan" class="form-control" placeholder="Contoh: DKV" maxlength="10" required>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label">Nama Jurusan</label>
                            <input type="text" name="nama_jurusan" class="form-control" placeholder="Contoh: Desain Komunikasi Visual" required>
                        </div>
                        <div class="col-sm-3">
                            <button type="submit" class="btn btn-success w-100">+ Tambah Jurusan</button>
                        </div>
                    </form>

                    <table class="table table-bordered table-sm">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 120px;">Kode</th>
                                <th>Nama Jurusan</th>
                                <th style="width: 170px;" class="text-center">Jumlah Mahasiswa</th>
                                <th style="width: 100px;" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($jurusans as $j)
                            <tr>
                                <td class="fw-bold">{{ $j->kode_jurusan }}</td>
                                <td>{{ $j->nama_jurusan }}</td>
                                <td class="text-center">
                                    <span class="badge bg-primary">{{ $j->mahasiswas_count }} Mahasiswa</span>
                                </td>
                                <td class="text-center">
                                    <form action="{{ route('jurusan.destroy', $j->kode_jurusan) }}" method="POST" onsubmit="return confirm('Hapus jurusan {{ $j->nama_jurusan }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL TAMBAH MAHASISWA -->
    <div class="modal fade" id="modalTambah" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Mahasiswa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('mahasiswa.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">NRP</label>
                            <input type="text" name="nrp" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Jurusan</label>
                            <select name="kode_jurusan" class="form-select" required>
                                <option value="">-- Pilih Jurusan --</option>
                                @foreach($jurusans as $j)
                                    <option value="{{ $j->kode_jurusan }}">{{ $j->nama_jurusan }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Wali Murid / Pengajar</label>
                            <select name="id_pengajar" class="form-select" required>
                                <option value="">-- Pilih Pengajar / Wali --</option>
                                @foreach($pengajars as $p)
                                    <option value="{{ $p->id_pengajar }}">{{ $p->nama_pengajar }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nama</label>
                            <input type="text" name="nama" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Alamat</label>
                            <textarea name="alamat" class="form-control" rows="2" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Semester</label>
                            <input type="number" name="semester" class="form-control" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">No. Telepon Mahasiswa</label>
                            <input type="text" name="no_telp" class="form-control" placeholder="Contoh: 08123456789" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">Simpan</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL EDIT MAHASISWA -->
    <div class="modal fade" id="modalEdit" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Mahasiswa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formEditMahasiswa" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">NRP</label>
                            <input type="text" name="nrp" id="edit_nrp" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Jurusan</label>
                            <select name="kode_jurusan" id="edit_kode_jurusan" class="form-select" required>
                                <option value="">-- Pilih Jurusan --</option>
                                @foreach($jurusans as $j)
                                    <option value="{{ $j->kode_jurusan }}">{{ $j->nama_jurusan }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Wali Murid / Pengajar</label>
                            <select name="id_pengajar" id="edit_id_pengajar" class="form-select" required>
                                <option value="">-- Pilih Pengajar / Wali --</option>
                                @foreach($pengajars as $p)
                                    <option value="{{ $p->id_pengajar }}">{{ $p->nama_pengajar }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nama</label>
                            <input type="text" name="nama" id="edit_nama" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Alamat</label>
                            <textarea name="alamat" id="edit_alamat" class="form-control" rows="2" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Semester</label>
                            <input type="number" name="semester" id="edit_semester" class="form-control" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">No. Telepon Mahasiswa</label>
                            <input type="text" name="no_telp" id="edit_no_telp" class="form-control" required>
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

    <!-- MODAL TONG SAMPAH (SOFT DELETES) -->
    <div class="modal fade" id="modalSampah" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tong Sampah Data Mahasiswa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small mb-3">
                        Data mahasiswa yang dihapus akan tersimpan sementara di sini. Kamu bisa memulihkannya kembali atau menghapusnya secara permanen.
                    </p>
                    <table class="table table-bordered table-sm">
                        <thead class="table-light">
                            <tr>
                                <th>NRP</th>
                                <th>Nama</th>
                                <th>Jurusan</th>
                                <th style="width: 210px;" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($mahasiswasTerhapus as $sampah)
                            <tr>
                                <td>{{ $sampah->nrp }}</td>
                                <td>{{ $sampah->nama }}</td>
                                <td>{{ $sampah->jurusan ? $sampah->jurusan->nama_jurusan : '-' }}</td>
                                <td class="text-center">
                                    <form action="{{ route('mahasiswa.restore', $sampah->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-success btn-sm">Pulihkan</button>
                                    </form>
                                    <form action="{{ route('mahasiswa.forceDelete', $sampah->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus permanen data ini? Data tidak bisa dikembalikan lagi!')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">Hapus Permanen</button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-3">
                                    Tong sampah kosong.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function bukaModalEdit(updateUrl, nrp, kodeJurusan, idPengajar, nama, alamat, semester, noTelp) {
            document.getElementById('formEditMahasiswa').action = updateUrl;
            document.getElementById('edit_nrp').value = nrp;
            document.getElementById('edit_kode_jurusan').value = kodeJurusan;
            document.getElementById('edit_id_pengajar').value = idPengajar;
            document.getElementById('edit_nama').value = nama;
            document.getElementById('edit_alamat').value = alamat;
            document.getElementById('edit_semester').value = semester;
            document.getElementById('edit_no_telp').value = noTelp;

            var myModal = new bootstrap.Modal(document.getElementById('modalEdit'));
            myModal.show();
        }

        setTimeout(function() {
            var toast = document.getElementById('toastNotif');
            if (toast) toast.style.display = 'none';
        }, 4000);
    </script>
</body>
</html>