<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Sistem Akademik</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center justify-content-center" style="min-height: 100vh;">

    <div class="card shadow-sm" style="width: 100%; max-width: 420px;">
        <div class="card-body p-4">
            <h4 class="text-center mb-1 fw-semibold">Login Sistem Akademik</h4>
            <p class="text-center text-muted small mb-4">Masuk sebagai Atasan Admin, Admin, Trainer, atau User</p>

            @if(session('sukses'))
                <div class="alert alert-success py-2 small">{{ session('sukses') }}</div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger py-2 small">{{ $errors->first() }}</div>
            @endif

            <form action="{{ route('login.post') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email') }}" placeholder="contoh: admin@kampus.com" required autofocus>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" placeholder="Masukkan password" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Masuk</button>
            </form>

            <hr class="my-4">
            <div class="small text-muted">
                <div class="fw-semibold mb-1">Akun Uji Coba (Password: <code>password</code>):</div>
                <ul class="mb-0 ps-3">
                    <li>Atasan Admin: <code>atasan@kampus.com</code></li>
                    <li>Admin: <code>admin@kampus.com</code></li>
                    <li>Trainer: <code>trainer@kampus.com</code></li>
                    <li>User (Mahasiswa): <code>user@kampus.com</code></li>
                </ul>
            </div>
        </div>
    </div>

</body>
</html>