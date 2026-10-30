<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin — Viera Bakery')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body style="background: var(--bg);">

<div class="admin-wrapper">

    {{-- ─── Sidebar ────────────────────────────────────────── --}}
    <aside class="admin-sidebar">
        <a href="{{ route('admin.dashboard') }}" class="sidebar-brand">
            Viera Bakery
            <span>Panel Admin</span>
        </a>

        {{-- 1. Menu Utama --}}
        <p class="sidebar-section">Menu Utama</p>
        <ul class="sidebar-nav">
            <li>
                <a href="{{ route('home') }}">
                    <i class="bi bi-house"></i> Beranda (Publik)
                </a>
            </li>
            <li>
                <a href="{{ route('admin.dashboard') }}"
                   class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="bi bi-grid-1x2"></i> Dashboard
                </a>
            </li>
        </ul>

        {{-- 2. Transaksi --}}
        <p class="sidebar-section">Transaksi</p>
        <ul class="sidebar-nav">
            <li>
                <a href="{{ route('admin.orders.index') }}"
                   class="{{ request()->routeIs('admin.orders.*') ? 'active' : '' }}"
                   style="position:relative;">
                    <i class="bi bi-bag-check"></i> Pesanan
                    <span id="admin-order-badge" class="sidebar-badge" style="display:none;"></span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.reports.index') }}"
                   class="{{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
                    <i class="bi bi-bar-chart-line"></i> Laporan
                </a>
            </li>
        </ul>

        {{-- 3. Komunikasi --}}
        <p class="sidebar-section">Komunikasi</p>
        <ul class="sidebar-nav">
            <li>
                <a href="{{ route('admin.chat.index') }}"
                   class="{{ request()->routeIs('admin.chat.*') ? 'active' : '' }}" style="position:relative;">
                    <i class="bi bi-chat-dots"></i> Chat Pelanggan
                    <span id="admin-chat-badge" class="sidebar-badge" style="display:none;"></span>
                </a>
            </li>
        </ul>

        {{-- 4. Kelola Data --}}
        <p class="sidebar-section">Kelola Data</p>
        <ul class="sidebar-nav">
            <li>
                <a href="{{ route('admin.categories.index') }}"
                   class="{{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
                    <i class="bi bi-tag"></i> Kategori
                </a>
            </li>
            <li>
                <a href="{{ route('admin.products.index') }}"
                   class="{{ request()->routeIs('admin.products.*') ? 'active' : '' }}">
                    <i class="bi bi-box-seam"></i> Produk
                </a>
            </li>
            <li>
                <a href="{{ route('admin.pelanggan.index') }}"
                   class="{{ request()->routeIs('admin.pelanggan.*') ? 'active' : '' }}">
                    <i class="bi bi-people"></i> Pelanggan
                </a>
            </li>
        </ul>

        {{-- 5. Logout / Area Admin --}}
        <div class="sidebar-footer">
            <div class="mb-2" style="font-size:.78rem; color:#6B6B6B;">
                <i class="bi bi-person-circle me-1"></i>
                {{ Auth::user()->name ?? 'Admin' }}
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit">
                    <i class="bi bi-box-arrow-right me-1"></i> Logout
                </button>
            </form>
        </div>
    </aside>

    {{-- ─── Konten Utama ───────────────────────────────────── --}}
    <div class="admin-main">

        {{-- Topbar --}}
        <div class="admin-topbar">
            <div class="topbar-title">@yield('page-title', 'Dashboard')</div>
            <div class="topbar-user">
                <i class="bi bi-clock me-1"></i>
                {{ now()->translatedFormat('l, d F Y') }}
            </div>
        </div>

        {{-- Flash Messages --}}
        <div class="px-4 pt-3">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>Terdapat kesalahan:</strong>
                    <ul class="mb-0 mt-1 ps-3">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        {{-- Konten --}}
        <div class="admin-content">
            @yield('content')
        </div>

    </div>
</div>

<style>
    .sidebar-badge { position:absolute; top:50%; right:12px; transform:translateY(-50%); min-width:18px; height:18px; padding:0 5px; background:#EF4444; color:#fff; border-radius:20px; font-size:.6rem; font-weight:700; display:flex; align-items:center; justify-content:center; line-height:1; }
</style>
<script>
    // ── Badge: Pesanan baru ──
    function fetchAdminOrderBadge() {
        fetch('{{ route("admin.orders.newCount") }}', { headers: { 'Accept': 'application/json' } })
        .then(function(r) { return r.json(); })
        .then(function(d) {
            var badge = document.getElementById('admin-order-badge');
            if (badge) {
                if (d.count > 0) { badge.textContent = d.count > 9 ? '9+' : d.count; badge.style.display = 'flex'; }
                else { badge.style.display = 'none'; }
            }
        }).catch(function(){});
    }
    fetchAdminOrderBadge();
    setInterval(fetchAdminOrderBadge, 10000);

    // ── Badge: Chat unread ──
    function fetchAdminChatBadge() {
        fetch('{{ route("admin.chat.unreadCount") }}', { headers: { 'Accept': 'application/json' } })
        .then(function(r) { return r.json(); })
        .then(function(d) {
            var badge = document.getElementById('admin-chat-badge');
            if (badge) {
                if (d.count > 0) { badge.textContent = d.count > 99 ? '99+' : d.count; badge.style.display = 'flex'; }
                else { badge.style.display = 'none'; }
            }
        }).catch(function(){});
    }
    fetchAdminChatBadge();
    setInterval(fetchAdminChatBadge, 10000);
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
