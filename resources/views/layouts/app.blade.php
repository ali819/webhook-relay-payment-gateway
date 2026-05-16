<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Webhook Relay')</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .badge-midtrans { background-color: #00b4d8; color: #fff; }
        .badge-xendit { background-color: #4f46e5; color: #fff; }

        @media (min-width: 768px) {
            .sidebar {
                min-height: 100vh;
                background: #212529;
                position: sticky;
                top: 0;
                height: 100vh;
                overflow-y: auto;
            }
            .sidebar .nav-link { color: #adb5bd; border-radius: 6px; }
            .sidebar .nav-link:hover,
            .sidebar .nav-link.active { color: #fff; background: rgba(255,255,255,.08); }
            .sidebar .nav-link i { width: 20px; }
            .main-content { min-height: 100vh; }
        }

        @media (max-width: 767px) {
            .mobile-nav { background: #212529; }
            .mobile-nav .nav-link { color: #adb5bd; }
            .mobile-nav .nav-link:hover,
            .mobile-nav .nav-link.active { color: #fff; background: rgba(255,255,255,.08); border-radius: 6px; }
        }
    </style>
</head>
<body>

@php
$menus = [
    ['route' => 'panel.domains.index', 'active' => 'panel.domains.*', 'icon' => 'bi-globe2',       'label' => 'Domains'],
    ['route' => 'panel.logs.index',    'active' => 'panel.logs.*',    'icon' => 'bi-journal-text',  'label' => 'Logs'],
    ['route' => 'panel.tutorial',      'active' => 'panel.tutorial',  'icon' => 'bi-book',          'label' => 'Tutorial'],
];
@endphp

{{-- Mobile topbar --}}
<nav class="mobile-nav d-md-none navbar navbar-dark px-3 py-2">
    <span class="navbar-brand fw-semibold" style="font-size:15px">
        <i class="bi bi-broadcast me-2"></i>Webhook-PG
    </span>
    <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#mobileMenu">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse mt-2" id="mobileMenu">
        <ul class="navbar-nav gap-1 mb-2">
            @foreach($menus as $menu)
            <li class="nav-item">
                <a class="nav-link px-2 py-2 {{ request()->routeIs($menu['active']) ? 'active' : '' }}"
                   href="{{ route($menu['route']) }}">
                    <i class="bi {{ $menu['icon'] }} me-2"></i>{{ $menu['label'] }}
                </a>
            </li>
            @endforeach
        </ul>
        <div class="border-top border-secondary pt-2">
            <p class="text-secondary small mb-1 ps-1">{{ auth()->user()->name }}</p>
            <form action="{{ route('panel.logout') }}" method="POST">
                @csrf
                <button class="nav-link px-2 py-1 text-danger border-0 bg-transparent text-start">
                    <i class="bi bi-box-arrow-left me-2"></i>Logout
                </button>
            </form>
        </div>
    </div>
</nav>

{{-- Desktop layout --}}
<div class="container-fluid">
    <div class="row">

        {{-- Desktop sidebar --}}
        <nav class="col-md-2 sidebar d-none d-md-flex flex-column px-3 py-4">
            <div class="text-white fw-semibold mb-4 ps-2" style="font-size:15px">
                <i class="bi bi-broadcast me-2"></i>WR-PG
            </div>
            <ul class="nav flex-column gap-1 flex-grow-1">
                @foreach($menus as $menu)
                <li class="nav-item">
                    <a class="nav-link px-2 py-2 {{ request()->routeIs($menu['active']) ? 'active' : '' }}"
                       href="{{ route($menu['route']) }}">
                        <i class="bi {{ $menu['icon'] }} me-2"></i>{{ $menu['label'] }}
                    </a>
                </li>
                @endforeach
            </ul>
            <div class="border-top border-secondary pt-3">
                <p class="text-secondary small mb-1 ps-2">{{ auth()->user()->name }}</p>
                <form action="{{ route('panel.logout') }}" method="POST">
                    @csrf
                    <button class="nav-link px-2 py-1 text-danger border-0 bg-transparent w-100 text-start">
                        <i class="bi bi-box-arrow-left me-2"></i>Logout
                    </button>
                </form>
            </div>
        </nav>

        {{-- Main content --}}
        <main class="col-12 col-md-10 main-content py-4 px-3 px-md-4">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @yield('content')
        </main>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.querySelectorAll('input, select, textarea').forEach(el => {
    el.setAttribute('autocomplete', 'off');
});

// Global SweetAlert confirm — ganti semua onsubmit confirm()
document.querySelectorAll('form[data-confirm]').forEach(form => {
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        const title   = this.dataset.confirmTitle   || 'Yakin?';
        const text    = this.dataset.confirmText    || 'Tindakan ini tidak bisa dibatalkan.';
        const btnText = this.dataset.confirmBtn     || 'Ya, lanjutkan';
        const icon    = this.dataset.confirmIcon    || 'warning';

        Swal.fire({
            title,
            text,
            icon,
            showCancelButton:    true,
            confirmButtonText:   btnText,
            cancelButtonText:    'Batal',
            confirmButtonColor:  '#212529',
            cancelButtonColor:   '#6c757d',
            reverseButtons:      true,
        }).then(result => {
            if (result.isConfirmed) this.submit();
        });
    });
});
</script>

</body>
</html>
