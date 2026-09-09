<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Buat Admin — Auth</title>
    @include('partials.head-assets')
</head>
<body class="bg-light d-flex align-items-center justify-content-center" style="min-height:100vh">
    <div class="card shadow-sm" style="width:100%;max-width:420px">
        <div class="card-body p-4">
            <h5 class="fw-semibold mb-1">Buat akun admin</h5>
            <p class="text-muted small mb-4">
                Belum ada akun di panel ini. Buat akun admin pertama untuk mulai memakai relay.
            </p>

            @if($errors->any())
                <div class="alert alert-danger py-2 small mb-3">
                    <ul class="mb-0 ps-3">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('register.post') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label small fw-medium">Nama</label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name') }}" required autofocus>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-medium">Email</label>
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                           value="{{ old('email') }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-medium">Password</label>
                    <div class="input-group">
                        <input type="password" name="password" id="password"
                               class="form-control @error('password') is-invalid @enderror" required>
                        <button class="btn btn-outline-secondary" type="button"
                                data-toggle-password="#password" title="Tampilkan password">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                    <div class="form-text">Minimal 8 karakter, mengandung huruf dan angka.</div>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-medium">Ulangi password</label>
                    <div class="input-group">
                        <input type="password" name="password_confirmation" id="password_confirmation"
                               class="form-control" required>
                        <button class="btn btn-outline-secondary" type="button"
                                data-toggle-password="#password_confirmation" title="Tampilkan password">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>
                <button type="submit" class="btn btn-dark w-100">Buat akun &amp; masuk</button>
            </form>
        </div>
    </div>

    @include('partials.js-assets')

    <script>
    document.querySelector('form').addEventListener('submit', function () {
        const btn = this.querySelector('button[type="submit"]');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...';
    });
    </script>
</body>
</html>
