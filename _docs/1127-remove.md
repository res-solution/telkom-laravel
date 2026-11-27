# Daftar File yang Bisa Diremove

> Dibuat: 27 Nov 2026. Masih berupa rekomendasi — belum ada yang dihapus.

## Aman dihapus (tidak direferensikan kode manapun)

| File | Alasan |
|---|---|
| `resources/views/welcome.blade.php` | Halaman default Laravel. Route `/` memakai `MahasiswaController@index` dan tidak ada `view('welcome')` di seluruh kode. Ini satu-satunya file yang memakai `@vite`. |
| `tests/Unit/ExampleTest.php` | Test default `assertTrue(true)`, tidak menguji apa-apa. (Pertahankan `tests/Feature/ExampleTest.php` — sudah menguji redirect tamu ke halaman login.) |
| `routes/console.php` + baris `commands:` di `bootstrap/app.php` | Isinya hanya perintah demo `inspire`. Hapus filenya, lalu hapus baris `commands: __DIR__.'/../routes/console.php',` di `bootstrap/app.php` agar tidak error. |
| `.phpunit.result.cache` | Cache PHPUnit lokal, otomatis dibuat ulang saat test dijalankan (sudah di-gitignore). |
| `storage/framework/views/*` | Hasil compile Blade, otomatis dibuat ulang (sudah di-gitignore). Bisa juga dengan `php artisan view:clear`. |

## Ikut bisa dihapus jika `welcome.blade.php` dihapus (pipeline Vite/Tailwind tidak terpakai)

Semua view aktif memakai Bootstrap CDN, jadi aset Vite tidak dirender di halaman manapun:

- `resources/css/app.css`
- `resources/js/app.js`
- `vite.config.js`
- `public/build/` (hasil `npm run build`, sudah di-gitignore)
- `package.json`, `package-lock.json`, `node_modules/`

Catatan: jika dihapus, abaikan saran `npm run build` / `npm run dev` di AGENTS.md.

## Opsional — tooling AI (hasil `php artisan boost:install`)

Hanya dipakai kalau memakai Claude Code / OpenCode / Zed sebagai asisten AI:

- `.agents/` dan `.claude/` (skills, duplikat satu sama lain)
- `.mcp.json`, `opencode.json`, `boost.json`
- `.zed/settings.json`

**Jangan hapus** `AGENTS.md` dan `CLAUDE.md` — itu instruksi proyek untuk AI, bukan file sisa.

## Pertimbangkan (boleh dihapus, tapi disarankan disimpan)

- `database/factories/UserFactory.php` — belum dipakai test manapun, tapi standar Laravel dan berguna saat nanti menulis test dengan factory.
- `README.md` — masih boilerplate Laravel. Lebih baik diganti dengan README proyek (cara install, akun login, dll) daripada dihapus.

## Jangan dihapus

`app/`, `config/`, `database/migrations/`, `database/seeders/`, `routes/web.php`, `tests/Feature/ExampleTest.php`, `resources/views/auth/`, `resources/views/mahasiswa/`, `phpunit.xml`, `artisan`, `.env.example`, `.editorconfig`, `.gitattributes`, `.gitignore`, `.npmrc`, `public/index.php`, `public/.htaccess`, `composer.json`, `composer.lock`, `bootstrap/`.

## Catatan kecil

- `.env` jangan pernah di-commit (sudah di-gitignore).
- `.commandcode/` belum ada di `.gitignore` — sebaiknya ditambahkan agar tidak ikut ter-commit.
