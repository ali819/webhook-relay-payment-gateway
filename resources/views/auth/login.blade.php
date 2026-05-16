<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Auth</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center justify-content-center" style="min-height:100vh">
    <div class="card shadow-sm" style="width:100%;max-width:380px">
        <div class="card-body p-4">
            <h5 class="fw-semibold mb-1">Login UI</h5>
            <p class="text-muted small mb-4">Halo selamat datang!</p>

            @if($errors->any())
                <div class="alert alert-danger py-2 small">{{ $errors->first() }}</div>
            @endif

            <form action="{{ route('login.post') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label small fw-medium">Email</label>
                    <input type="email" name="email" class="form-control"
                           value="" required autofocus>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-medium">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" name="remember" id="remember">
                    <label class="form-check-label small" for="remember">Ingat saya</label>
                </div>
                <button type="submit" class="btn btn-dark w-100">Masuk</button>
            </form>
        </div>
    </div>
    <script>
    document.querySelectorAll('input, select, textarea').forEach(el => {
        el.setAttribute('autocomplete', 'off');
    });
    document.querySelector('form').addEventListener('submit', function () {
        const btn = this.querySelector('button[type="submit"]');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Masuk...';
    });
    </script>
</body>
</html>
