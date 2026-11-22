<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\Mahasiswa;
use App\Models\Jurusan;
use App\Models\Pengajar;
use App\Models\Pelanggaran;
use App\Models\JenisPelanggaran;
use App\Models\PengajuanTrainer;
use App\Models\User;

class MahasiswaController extends Controller
{
    public function index(Request $request)
    {
        $query = Mahasiswa::with(['jurusan', 'pengajar'])->withCount('pelanggarans');

        // Pembatasan tampilan data berdasarkan Role yang login
        $user = Auth::user();
        if ($user->role === 'trainer') {
            $query->where('id_pengajar', $user->id_pengajar ?? 0);
        } elseif ($user->role === 'user') {
            $query->where('id', $user->id_mahasiswa ?? 0);
        }

        if ($request->filled('search')) {
            $kataKunci = $request->search;
            $query->where(function ($q) use ($kataKunci) {
                $q->where('nama', 'like', '%' . $kataKunci . '%')
                  ->orWhere('nrp', 'like', '%' . $kataKunci . '%')
                  ->orWhere('alamat', 'like', '%' . $kataKunci . '%')
                  ->orWhereHas('pengajar', function ($qPengajar) use ($kataKunci) {
                      $qPengajar->where('nama_pengajar', 'like', '%' . $kataKunci . '%');
                  });
            });
        }

        if ($request->filled('filter_jurusan')) {
            $query->where('kode_jurusan', $request->filter_jurusan);
        }

        $mahasiswas = $query->orderBy('id', 'asc')->get();
        $jurusans = Jurusan::withCount('mahasiswas')->orderBy('nama_jurusan', 'asc')->get();
        $pengajars = Pengajar::withCount('mahasiswas')->orderBy('nama_pengajar', 'asc')->get();

        // Mengambil data mahasiswa yang ada di Tong Sampah (Soft Deleted)
        $mahasiswasTerhapus = Mahasiswa::onlyTrashed()->with('jurusan')->orderBy('deleted_at', 'desc')->get();

        // Menghitung jumlah pengajuan trainer yang masih pending untuk badge notifikasi
        $jumlahPendingTrainer = PengajuanTrainer::where('status', 'pending')->count();

        $statistik = [
            'total_mahasiswa' => Mahasiswa::count(),
            'total_jurusan'   => Jurusan::count(),
            'total_pengajar'  => Pengajar::count(),
            'rata_semester'   => round(Mahasiswa::avg('semester') ?? 0, 1),
            'semester_max'    => Mahasiswa::max('semester') ?? 0,
        ];

        return view('mahasiswa.index', compact(
            'mahasiswas', 'jurusans', 'pengajars', 'statistik', 'mahasiswasTerhapus', 'jumlahPendingTrainer'
        ));
    }

    public function store(Request $request)
    {
        if (!in_array(Auth::user()->role, ['admin', 'atasan_admin'])) {
            abort(403, 'Akses ditolak. Hanya Admin yang dapat menambah data mahasiswa.');
        }

        $request->validate([
            'nrp'          => 'required|unique:tb_mahasiswa,nrp',
            'kode_jurusan' => 'required',
            'id_pengajar'  => 'required',
            'nama'         => 'required',
            'alamat'       => 'required',
            'semester'     => 'required|numeric',
            'no_telp'      => 'required',
        ], [
            'nrp.unique'   => 'NRP sudah terdaftar, gunakan NRP lain!'
        ]);

        Mahasiswa::create($request->only([
            'nrp', 'kode_jurusan', 'id_pengajar', 'nama', 'alamat', 'semester', 'no_telp'
        ]));

        return redirect()->route('mahasiswa.index')->with('sukses', 'Data berhasil ditambahkan!');
    }

    public function update(Request $request, $id)
    {
        if (!in_array(Auth::user()->role, ['admin', 'atasan_admin'])) {
            abort(403, 'Akses ditolak. Hanya Admin yang dapat mengubah data mahasiswa.');
        }

        $mahasiswa = Mahasiswa::findOrFail($id);

        $request->validate([
            'nrp'          => 'required|unique:tb_mahasiswa,nrp,' . $id . ',id',
            'kode_jurusan' => 'required',
            'id_pengajar'  => 'required',
            'nama'         => 'required',
            'alamat'       => 'required',
            'semester'     => 'required|numeric',
            'no_telp'      => 'required',
        ], [
            'nrp.unique'   => 'NRP sudah dipakai mahasiswa lain!'
        ]);

        $mahasiswa->update($request->only([
            'nrp', 'kode_jurusan', 'id_pengajar', 'nama', 'alamat', 'semester', 'no_telp'
        ]));

        return redirect()->route('mahasiswa.index')->with('sukses', 'Data berhasil diupdate!');
    }

    public function destroy($id)
    {
        if (!in_array(Auth::user()->role, ['admin', 'atasan_admin'])) {
            abort(403, 'Akses ditolak. Hanya Admin yang dapat menghapus data mahasiswa.');
        }

        Mahasiswa::findOrFail($id)->delete();

        return redirect()->route('mahasiswa.index')->with('sukses', 'Data dipindahkan ke Tong Sampah!');
    }

    public function restore($id)
    {
        if (!in_array(Auth::user()->role, ['admin', 'atasan_admin'])) {
            abort(403, 'Akses ditolak. Hanya Admin yang dapat memulihkan data.');
        }

        $mahasiswa = Mahasiswa::withTrashed()->where('id', $id)->firstOrFail();
        $mahasiswa->restore();

        return redirect()->route('mahasiswa.index')->with('sukses', 'Data mahasiswa berhasil dipulihkan!');
    }

    public function forceDelete($id)
    {
        if (!in_array(Auth::user()->role, ['admin', 'atasan_admin'])) {
            abort(403, 'Akses ditolak. Hanya Admin yang dapat menghapus permanen.');
        }

        $mahasiswa = Mahasiswa::withTrashed()->where('id', $id)->firstOrFail();
        $mahasiswa->forceDelete();

        return redirect()->route('mahasiswa.index')->with('sukses', 'Data mahasiswa dihapus permanen!');
    }

    public function storeJurusan(Request $request)
    {
        if (!in_array(Auth::user()->role, ['admin', 'atasan_admin'])) {
            abort(403, 'Akses ditolak. Hanya Admin yang dapat mengelola jurusan.');
        }

        $request->validate([
            'kode_jurusan' => 'required|max:10|unique:tb_jurusan,kode_jurusan',
            'nama_jurusan' => 'required|max:100',
        ], [
            'kode_jurusan.unique' => 'Kode Jurusan tersebut sudah ada!'
        ]);

        Jurusan::create([
            'kode_jurusan' => strtoupper($request->kode_jurusan),
            'nama_jurusan' => $request->nama_jurusan,
        ]);

        return redirect()->route('mahasiswa.index')->with('sukses', 'Jurusan baru berhasil ditambahkan!');
    }

    public function destroyJurusan($kode_jurusan)
    {
        if (!in_array(Auth::user()->role, ['admin', 'atasan_admin'])) {
            abort(403, 'Akses ditolak. Hanya Admin yang dapat menghapus jurusan.');
        }

        Jurusan::findOrFail($kode_jurusan)->delete();

        return redirect()->route('mahasiswa.index')->with('sukses', 'Jurusan berhasil dihapus!');
    }

    public function storePengajar(Request $request)
    {
        // Hanya Atasan Admin yang bisa langsung menambah pengajar tanpa verifikasi
        if (Auth::user()->role !== 'atasan_admin') {
            abort(403, 'Admin wajib menggunakan menu Pengajuan Trainer untuk menambah trainer baru.');
        }

        $request->validate([
            'nama_pengajar'  => 'required|max:100',
            'no_hp_pengajar' => 'nullable|max:20',
        ]);

        Pengajar::create($request->only(['nama_pengajar', 'no_hp_pengajar']));

        return redirect()->route('mahasiswa.index')->with('sukses', 'Pengajar/Wali berhasil ditambahkan langsung oleh Atasan Admin!');
    }

    public function destroyPengajar($id_pengajar)
    {
        if (Auth::user()->role !== 'atasan_admin') {
            abort(403, 'Hanya Atasan Admin yang dapat menghapus data pengajar.');
        }

        Pengajar::findOrFail($id_pengajar)->delete();

        return redirect()->route('mahasiswa.index')->with('sukses', 'Pengajar/Wali berhasil dihapus!');
    }

    // =========================================================================
    // HALAMAN DETAIL & CRUD PELANGGARAN PER MAHASISWA
    // =========================================================================

    public function showPelanggaran($id)
    {
        $user = Auth::user();
        if ($user->role === 'user' && $user->id_mahasiswa != $id) {
            abort(403, 'Anda hanya dapat melihat riwayat pelanggaran Anda sendiri.');
        }

        session(['last_mahasiswa_id' => $id]);

        $mahasiswa = Mahasiswa::with(['jurusan', 'pelanggarans.jenisPelanggaran'])->findOrFail($id);

        if ($user->role === 'trainer' && $mahasiswa->id_pengajar != $user->id_pengajar) {
            abort(403, 'Mahasiswa ini bukan merupakan mahasiswa bimbingan Anda.');
        }

        $jenisPelanggarans = JenisPelanggaran::orderBy('id_jenis', 'asc')->get();

        $rekapJenis = JenisPelanggaran::withCount(['pelanggarans' => function ($query) use ($id) {
            $query->where('id_mahasiswa', $id);
        }])->get();

        return view('mahasiswa.pelanggaran', compact('mahasiswa', 'jenisPelanggarans', 'rekapJenis'));
    }

    public function storePelanggaran(Request $request, $id)
    {
        if (Auth::user()->role === 'user') {
            abort(403, 'Mahasiswa hanya memiliki akses lihat data.');
        }

        $request->validate([
            'id_jenis'            => 'required|exists:tb_jenis_pelanggaran,id_jenis',
            'keterangan'          => 'required',
            'tanggal_pelanggaran' => 'required|date',
        ]);

        Pelanggaran::create([
            'id_mahasiswa'        => $id,
            'id_jenis'            => $request->id_jenis,
            'keterangan'          => $request->keterangan,
            'tanggal_pelanggaran' => $request->tanggal_pelanggaran,
        ]);

        return redirect()->route('pelanggaran.show', $id)->with('sukses', 'Catatan pelanggaran berhasil ditambahkan!');
    }

    public function updatePelanggaran(Request $request, $id_pelanggaran)
    {
        if (Auth::user()->role === 'user') {
            abort(403, 'Mahasiswa hanya memiliki akses lihat data.');
        }

        $pelanggaran = Pelanggaran::findOrFail($id_pelanggaran);

        $request->validate([
            'id_jenis'            => 'required|exists:tb_jenis_pelanggaran,id_jenis',
            'keterangan'          => 'required',
            'tanggal_pelanggaran' => 'required|date',
        ]);

        $pelanggaran->update($request->only([
            'id_jenis', 'keterangan', 'tanggal_pelanggaran'
        ]));

        return redirect()->route('pelanggaran.show', $pelanggaran->id_mahasiswa)->with('sukses', 'Catatan pelanggaran berhasil diupdate!');
    }

    public function destroyPelanggaran($id_pelanggaran)
    {
        if (Auth::user()->role === 'user') {
            abort(403, 'Mahasiswa hanya memiliki akses lihat data.');
        }

        $pelanggaran = Pelanggaran::findOrFail($id_pelanggaran);
        $idMahasiswa = $pelanggaran->id_mahasiswa;
        $pelanggaran->delete();

        return redirect()->route('pelanggaran.show', $idMahasiswa)->with('sukses', 'Catatan pelanggaran berhasil dihapus!');
    }

    // =========================================================================
    // HALAMAN KELOLA & EDIT JENIS PELANGGARAN (MASTER DATA)
    // =========================================================================

    public function indexJenisPelanggaran(Request $request)
    {
        if (Auth::user()->role === 'user') {
            abort(403, 'Mahasiswa tidak memiliki akses ke halaman ini.');
        }

        if ($request->filled('mahasiswa_id')) {
            session(['last_mahasiswa_id' => $request->mahasiswa_id]);
        }

        $idMahasiswa = $request->mahasiswa_id ?? session('last_mahasiswa_id') ?? Mahasiswa::orderBy('id', 'asc')->value('id');

        $jenisPelanggarans = JenisPelanggaran::withCount('pelanggarans')
            ->orderBy('id_jenis', 'asc')
            ->get();

        return view('mahasiswa.jenis_pelanggaran', compact('jenisPelanggarans', 'idMahasiswa'));
    }

    public function storeJenisPelanggaran(Request $request)
    {
        if (Auth::user()->role === 'user') {
            abort(403, 'Mahasiswa tidak memiliki akses mengubah jenis pelanggaran.');
        }

        $request->validate([
            'nama_jenis' => 'required|max:50|unique:tb_jenis_pelanggaran,nama_jenis',
            'poin'       => 'required|integer|min:1',
        ], [
            'nama_jenis.unique' => 'Nama jenis pelanggaran tersebut sudah ada!'
        ]);

        JenisPelanggaran::create([
            'nama_jenis' => $request->nama_jenis,
            'poin'       => $request->poin,
        ]);

        return redirect()->route('jenis-pelanggaran.index')->with('sukses', 'Jenis pelanggaran baru berhasil ditambahkan!');
    }

    public function updateJenisPelanggaran(Request $request, $id_jenis)
    {
        if (Auth::user()->role === 'user') {
            abort(403, 'Mahasiswa tidak memiliki akses mengubah jenis pelanggaran.');
        }

        $jenis = JenisPelanggaran::findOrFail($id_jenis);

        $request->validate([
            'nama_jenis' => 'required|max:50|unique:tb_jenis_pelanggaran,nama_jenis,' . $id_jenis . ',id_jenis',
            'poin'       => 'required|integer|min:1',
        ], [
            'nama_jenis.unique' => 'Nama jenis pelanggaran sudah dipakai kategori lain!'
        ]);

        $jenis->update([
            'nama_jenis' => $request->nama_jenis,
            'poin'       => $request->poin,
        ]);

        return redirect()->route('jenis-pelanggaran.index')->with('sukses', 'Jenis pelanggaran berhasil diupdate!');
    }

    public function destroyJenisPelanggaran($id_jenis)
    {
        if (Auth::user()->role === 'user') {
            abort(403, 'Mahasiswa tidak memiliki akses menghapus jenis pelanggaran.');
        }

        $jenis = JenisPelanggaran::withCount('pelanggarans')->findOrFail($id_jenis);

        if ($jenis->pelanggarans_count > 0) {
            return redirect()->route('jenis-pelanggaran.index')
                ->withErrors(['Kategori "' . $jenis->nama_jenis . '" tidak bisa dihapus karena masih digunakan pada ' . $jenis->pelanggarans_count . ' catatan pelanggaran mahasiswa!']);
        }

        $jenis->delete();

        return redirect()->route('jenis-pelanggaran.index')->with('sukses', 'Jenis pelanggaran berhasil dihapus!');
    }

    // =========================================================================
    // HALAMAN PENGAJUAN TRAINER (SISI ADMIN)
    // =========================================================================

    public function indexPengajuanTrainer()
    {
        if (!in_array(Auth::user()->role, ['admin', 'atasan_admin'])) {
            abort(403, 'Akses ditolak.');
        }

        $pengajuans = PengajuanTrainer::with(['pengaju', 'verifikator'])
            ->orderBy('id_pengajuan', 'desc')
            ->get();

        return view('mahasiswa.pengajuan_trainer', compact('pengajuans'));
    }

    public function storePengajuanTrainer(Request $request)
    {
        if (!in_array(Auth::user()->role, ['admin', 'atasan_admin'])) {
            abort(403, 'Akses ditolak.');
        }

        $request->validate([
            'nama_pengajar'    => 'required|max:100',
            'no_hp_pengajar'   => 'nullable|max:20',
            'email'            => 'required|email|max:100|unique:users,email',
            'password'         => 'required|min:6',
            'alasan_pengajuan' => 'nullable|max:500',
        ], [
            'email.unique' => 'Email tersebut sudah terdaftar sebagai akun pengguna!',
            'password.min' => 'Password minimal 6 karakter.'
        ]);

        // Cek apakah email ini sudah ada di antrean pending
        $sudahAdaPending = PengajuanTrainer::where('email', $request->email)
            ->where('status', 'pending')
            ->exists();

        if ($sudahAdaPending) {
            return redirect()->route('pengajuan-trainer.index')
                ->withErrors(['Email tersebut sedang dalam antrean pengajuan yang menunggu verifikasi Atasan Admin!']);
        }

        PengajuanTrainer::create([
            'nama_pengajar'    => $request->nama_pengajar,
            'no_hp_pengajar'   => $request->no_hp_pengajar,
            'email'            => $request->email,
            'password'         => Hash::make($request->password),
            'alasan_pengajuan' => $request->alasan_pengajuan,
            'status'           => 'pending',
            'diajukan_oleh'    => Auth::id(),
        ]);

        return redirect()->route('pengajuan-trainer.index')
            ->with('sukses', 'Pengajuan trainer baru berhasil dikirim ke dashboard Atasan Admin!');
    }

    public function destroyPengajuanTrainer($id_pengajuan)
    {
        if (!in_array(Auth::user()->role, ['admin', 'atasan_admin'])) {
            abort(403, 'Akses ditolak.');
        }

        $pengajuan = PengajuanTrainer::findOrFail($id_pengajuan);

        if ($pengajuan->status !== 'pending') {
            return redirect()->route('pengajuan-trainer.index')
                ->withErrors(['Pengajuan yang sudah diverifikasi tidak dapat dibatalkan.']);
        }

        $pengajuan->delete();

        return redirect()->route('pengajuan-trainer.index')
            ->with('sukses', 'Pengajuan trainer berhasil dibatalkan.');
    }

    // =========================================================================
    // HALAMAN VERIFIKASI TRAINER (SISI ATASAN ADMIN)
    // =========================================================================

    public function indexVerifikasiTrainer()
    {
        if (Auth::user()->role !== 'atasan_admin') {
            abort(403, 'Halaman ini khusus untuk Atasan Admin.');
        }

        $pengajuans = PengajuanTrainer::with(['pengaju', 'verifikator'])
            ->orderByRaw("FIELD(status, 'pending', 'disetujui', 'ditolak')")
            ->orderBy('id_pengajuan', 'desc')
            ->get();

        return view('mahasiswa.verifikasi_trainer', compact('pengajuans'));
    }

    public function approveTrainer($id_pengajuan)
    {
        if (Auth::user()->role !== 'atasan_admin') {
            abort(403, 'Hanya Atasan Admin yang dapat menyetujui pengajuan trainer.');
        }

        $pengajuan = PengajuanTrainer::findOrFail($id_pengajuan);

        if ($pengajuan->status !== 'pending') {
            return redirect()->route('verifikasi-trainer.index')
                ->withErrors(['Pengajuan ini sudah diverifikasi sebelumnya.']);
        }

        // Gunakan DB::transaction agar insert ke tb_pengajar, users, dan update status berjalan serentak
        DB::transaction(function () use ($pengajuan) {
            // 1. Masukkan ke tabel master tb_pengajar
            $pengajarBaru = Pengajar::create([
                'nama_pengajar'  => $pengajuan->nama_pengajar,
                'no_hp_pengajar' => $pengajuan->no_hp_pengajar,
            ]);

            // 2. Buat akun login di tabel users dengan role trainer
            User::create([
                'name'        => $pengajuan->nama_pengajar,
                'email'       => $pengajuan->email,
                'password'    => $pengajuan->password,
                'role'        => 'trainer',
                'status_akun' => 'aktif',
                'id_pengajar' => $pengajarBaru->id_pengajar,
            ]);

            // 3. Perbarui status di tb_pengajuan_trainer
            $pengajuan->update([
                'status'            => 'disetujui',
                'diverifikasi_oleh' => Auth::id(),
                'verified_at'       => now(),
            ]);
        });

        return redirect()->route('verifikasi-trainer.index')
            ->with('sukses', 'Trainer "' . $pengajuan->nama_pengajar . '" disetujui! Data pengajar dan akun login sudah aktif.');
    }

    public function rejectTrainer(Request $request, $id_pengajuan)
    {
        if (Auth::user()->role !== 'atasan_admin') {
            abort(403, 'Hanya Atasan Admin yang dapat menolak pengajuan trainer.');
        }

        $pengajuan = PengajuanTrainer::findOrFail($id_pengajuan);

        if ($pengajuan->status !== 'pending') {
            return redirect()->route('verifikasi-trainer.index')
                ->withErrors(['Pengajuan ini sudah diverifikasi sebelumnya.']);
        }

        $pengajuan->update([
            'status'            => 'ditolak',
            'catatan_atasan'    => $request->catatan_atasan ?? 'Ditolak oleh Atasan Admin.',
            'diverifikasi_oleh' => Auth::id(),
            'verified_at'       => now(),
        ]);

        return redirect()->route('verifikasi-trainer.index')
            ->with('sukses', 'Pengajuan trainer "' . $pengajuan->nama_pengajar . '" telah ditolak.');
    }
}