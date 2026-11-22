<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Mahasiswa;
use App\Models\Jurusan;
use App\Models\Pengajar;
use App\Models\Pelanggaran;
use App\Models\JenisPelanggaran;

class MahasiswaController extends Controller
{
    public function index(Request $request)
    {
        $query = Mahasiswa::with(['jurusan', 'pengajar'])->withCount('pelanggarans');

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

        $statistik = [
            'total_mahasiswa' => Mahasiswa::count(),
            'total_jurusan'   => Jurusan::count(),
            'total_pengajar'  => Pengajar::count(),
            'rata_semester'   => round(Mahasiswa::avg('semester') ?? 0, 1),
            'semester_max'    => Mahasiswa::max('semester') ?? 0,
        ];

        return view('mahasiswa.index', compact(
            'mahasiswas', 'jurusans', 'pengajars', 'statistik', 'mahasiswasTerhapus'
        ));
    }

    public function store(Request $request)
    {
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
        Mahasiswa::findOrFail($id)->delete();

        return redirect()->route('mahasiswa.index')->with('sukses', 'Data dipindahkan ke Tong Sampah!');
    }

    public function restore($id)
    {
        $mahasiswa = Mahasiswa::withTrashed()->where('id', $id)->firstOrFail();
        $mahasiswa->restore();

        return redirect()->route('mahasiswa.index')->with('sukses', 'Data mahasiswa berhasil dipulihkan!');
    }

    public function forceDelete($id)
    {
        $mahasiswa = Mahasiswa::withTrashed()->where('id', $id)->firstOrFail();
        $mahasiswa->forceDelete();

        return redirect()->route('mahasiswa.index')->with('sukses', 'Data mahasiswa dihapus permanen!');
    }

    public function storeJurusan(Request $request)
    {
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
        Jurusan::findOrFail($kode_jurusan)->delete();

        return redirect()->route('mahasiswa.index')->with('sukses', 'Jurusan berhasil dihapus!');
    }

    public function storePengajar(Request $request)
    {
        $request->validate([
            'nama_pengajar'  => 'required|max:100',
            'no_hp_pengajar' => 'nullable|max:20',
        ]);

        Pengajar::create($request->only(['nama_pengajar', 'no_hp_pengajar']));

        return redirect()->route('mahasiswa.index')->with('sukses', 'Pengajar/Wali berhasil ditambahkan!');
    }

    public function destroyPengajar($id_pengajar)
    {
        Pengajar::findOrFail($id_pengajar)->delete();

        return redirect()->route('mahasiswa.index')->with('sukses', 'Pengajar/Wali berhasil dihapus!');
    }

    // =========================================================================
    // HALAMAN DETAIL & CRUD PELANGGARAN PER MAHASISWA
    // =========================================================================

    public function showPelanggaran($id)
    {
        $mahasiswa = Mahasiswa::with(['jurusan', 'pelanggarans.jenisPelanggaran'])->findOrFail($id);
        $jenisPelanggarans = JenisPelanggaran::orderBy('id_jenis', 'asc')->get();

        $rekapJenis = JenisPelanggaran::withCount(['pelanggarans' => function ($query) use ($id) {
            $query->where('id_mahasiswa', $id);
        }])->get();

        return view('mahasiswa.pelanggaran', compact('mahasiswa', 'jenisPelanggarans', 'rekapJenis'));
    }

    public function storePelanggaran(Request $request, $id)
    {
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
        if ($request->filled('mahasiswa_id')) {
            session(['last_mahasiswa_id' => $request->mahasiswa_id]);
        }

        $idMahasiswa = session('last_mahasiswa_id');

        $jenisPelanggarans = JenisPelanggaran::withCount('pelanggarans')
            ->orderBy('id_jenis', 'asc')
            ->get();

        return view('mahasiswa.jenis_pelanggaran', compact('jenisPelanggarans', 'idMahasiswa'));
    }

    public function storeJenisPelanggaran(Request $request)
    {
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
        $jenis = JenisPelanggaran::withCount('pelanggarans')->findOrFail($id_jenis);

        // Proteksi Foreign Key: cegah penghapusan jika jenis pelanggaran masih dipakai di catatan mahasiswa
        if ($jenis->pelanggarans_count > 0) {
            return redirect()->route('jenis-pelanggaran.index')
                ->withErrors(['Kategori "' . $jenis->nama_jenis . '" tidak bisa dihapus karena masih digunakan pada ' . $jenis->pelanggarans_count . ' catatan pelanggaran mahasiswa!']);
        }

        $jenis->delete();

        return redirect()->route('jenis-pelanggaran.index')->with('sukses', 'Jenis pelanggaran berhasil dihapus!');
    }
}