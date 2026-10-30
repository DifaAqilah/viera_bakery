<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Viera Bakery')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>

{{-- ─── Navbar ────────────────────────────────────────────── --}}
<nav class="navbar navbar-expand-lg navbar-bakery sticky-top">
    <div class="container">
        <a class="navbar-brand" href="{{ route('home') }}">
            Viera Bakery
        </a>
        <button class="navbar-toggler border-0" type="button"
                data-bs-toggle="collapse" data-bs-target="#navbarMain"
                style="color: var(--dark);">
            <i class="bi bi-list" style="font-size:1.4rem;"></i>
        </button>
        <div class="collapse navbar-collapse" id="navbarMain">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-1 py-2 py-lg-0">
                {{-- Beranda --}}
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}"
                       href="{{ route('home') }}">
                        <i class="bi bi-house me-1"></i>Beranda
                    </a>
                </li>
                {{-- Katalog (publik) --}}
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('products.*') ? 'active' : '' }}"
                       href="{{ route('products.index') }}">
                        <i class="bi bi-grid me-1"></i>Katalog
                    </a>
                </li>
                @auth
                    @if(Auth::user()->role !== 'admin')
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('cart.*') ? 'active' : '' }}"
                           href="{{ route('cart.index') }}">
                            <i class="bi bi-cart me-1"></i>Keranjang
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('orders.*') ? 'active' : '' }}"
                           href="{{ route('orders.history') }}">
                            <i class="bi bi-clock-history me-1"></i>Pesanan
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('chat.*') ? 'active' : '' }}"
                           href="{{ route('chat.index') }}" style="position:relative;">
                            <i class="bi bi-chat-dots me-1"></i>Chat
                            <span id="chat-badge" class="nav-badge" style="display:none;"></span>
                        </a>
                    </li>
                    @endif
                    {{-- Divider --}}
                    <li class="nav-item d-none d-lg-block">
                        <span style="width:1px; height:22px; background:rgba(26,26,26,.18); display:inline-block; vertical-align:middle; margin: 0 .25rem;"></span>
                    </li>
                    @if(Auth::user()->role === 'admin')
                    <li class="nav-item ms-lg-1">
                        <a href="{{ route('admin.dashboard') }}" class="btn-nav-register">
                            <i class="bi bi-speedometer2 me-1"></i>Panel Admin
                        </a>
                    </li>
                    @else
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('profile.*') ? 'active' : '' }}"
                           href="{{ route('profile.edit') }}">
                            <i class="bi bi-person-circle me-1"></i>{{ Auth::user()->name }}
                        </a>
                    </li>
                    @endif
                    <li class="nav-item ms-lg-1">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="btn-logout">
                                <i class="bi bi-box-arrow-right me-1"></i>Logout
                            </button>
                        </form>

                    </li>
                @else
                    <li class="nav-item d-none d-lg-block">
                        <span style="width:1px; height:22px; background:rgba(26,26,26,.18); display:inline-block; vertical-align:middle; margin: 0 .25rem;"></span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('login') }}">
                            <i class="bi bi-box-arrow-in-right me-1"></i>Login
                        </a>
                    </li>
                    <li class="nav-item ms-lg-1">
                        <a href="{{ route('register') }}" class="btn-nav-register">
                            <i class="bi bi-person-plus me-1"></i>Daftar
                        </a>
                    </li>
                @endauth
            </ul>
        </div>
    </div>
</nav>

{{-- ─── Flash Messages ────────────────────────────────────── --}}
@if(session('success'))
    <div class="container mt-3">
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    </div>
@endif
@if(session('error'))
    <div class="container mt-3">
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    </div>
@endif
@if($errors->any())
    <div class="container mt-3">
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <strong>Terdapat kesalahan:</strong>
            <ul class="mb-0 mt-1 ps-3">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    </div>
@endif

{{-- ─── Content ────────────────────────────────────────────── --}}
<main>
    @yield('content')
</main>

{{-- ─── Footer ─────────────────────────────────────────────── --}}
<footer class="footer-bakery">
    <div class="container">
        <div class="row g-4 py-4 text-start">
            <div class="col-md-4">
                <div class="fw-800 mb-2" style="font-family: var(--font-heading); font-size:1.15rem; color: var(--orange);">
                    Viera Bakery
                </div>
                <p style="font-size:.85rem; color: var(--muted); max-width:260px; line-height:1.65;">
                    Kue lezat, buatan tangan, dikirim langsung ke pintu Anda. Pesan online, pilih metode pengiriman, dan nikmati!
                </p>
            </div>
            <div class="col-md-4">
                <div class="fw-700 mb-2" style="font-size:.9rem;">Menu</div>
                <ul style="list-style:none; padding:0; font-size:.85rem; color: var(--muted);">
                    <li class="mb-1"><a href="{{ route('home') }}" class="footer-link">Beranda</a></li>
                    <li class="mb-1"><a href="{{ route('products.index') }}" class="footer-link">Katalog Produk</a></li>
                    <li class="mb-1"><a href="{{ route('login') }}" class="footer-link">Login</a></li>
                    <li class="mb-1"><a href="{{ route('register') }}" class="footer-link">Daftar Akun</a></li>
                </ul>
            </div>
            <div class="col-md-4">
                <div class="fw-700 mb-2" style="font-size:.9rem;">Kontak</div>
                <ul style="list-style:none; padding:0; font-size:.85rem; color: var(--muted);">
                    <li class="mb-1"><i class="bi bi-geo-alt me-2 text-orange"></i>Perumahan Villa Bekasi Indah 2 Blok J1 no 12B, Kabupaten Bekasi, Tambun Selatan</li>
                    <li class="mb-1"><i class="bi bi-telephone me-2 text-orange"></i>0812-9428-3499</li>
                    <li class="mb-1"><i class="bi bi-envelope me-2 text-orange"></i>info@vierabakery.com</li>
                </ul>
            </div>
        </div>
        <div class="border-top pt-3 text-center" style="font-size:.8rem; color: var(--muted); border-color: var(--border) !important;">
            &copy; {{ date('Y') }} Viera Bakery. Semua hak dilindungi.
        </div>
    </div>
</footer>

<style>
    .nav-badge { position:absolute; top:4px; right:-4px; min-width:17px; height:17px; padding:0 4px; background:#EF4444; color:#fff; border-radius:20px; font-size:.6rem; font-weight:700; display:flex; align-items:center; justify-content:center; line-height:1; }
</style>
@auth
@if(Auth::user()->isPelanggan())
<script>
    function fetchChatBadge() {
        fetch('{{ route("chat.unreadCount") }}', { headers: { 'Accept': 'application/json' } })
        .then(function(r) { return r.json(); })
        .then(function(d) {
            var badge = document.getElementById('chat-badge');
            if (badge) {
                if (d.count > 0) { badge.textContent = d.count > 99 ? '99+' : d.count; badge.style.display = 'flex'; }
                else { badge.style.display = 'none'; }
            }
        }).catch(function(){});
    }
    fetchChatBadge();
    setInterval(fetchChatBadge, 10000);
</script>
@endif
@endauth
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
