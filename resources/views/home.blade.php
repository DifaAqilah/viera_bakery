@extends('layouts.app')
@section('title', 'Beranda — Viera Bakery')
@section('content')

{{-- ══════════════════════════════════════════════
     HERO SECTION
══════════════════════════════════════════════ --}}
<section class="home-hero">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <div class="section-badge mb-2">Viera Bakery — Est. 2020</div>
                <h1 class="home-hero-title mb-3">
                    Kue <span class="highlight-orange">Lezat</span> &amp;<br>
                    Pesan dengan<br>
                    <span class="highlight-blue">Mudah</span>!
                </h1>
                <p class="mb-4" style="color: var(--muted); font-size: 1rem; max-width: 460px; line-height: 1.7;">
                    Nikmati berbagai pilihan kue terbaik dari Viera Bakery — pastry, roti artisan, kue kering, hingga hampers spesial. Pesan online, kami siapkan dengan cinta. 🍞
                </p>

                {{-- Feature Pills --}}
                <div class="d-flex flex-wrap gap-2 mb-4">
                    <span class="feature-pill">
                        <span class="pill-icon" style="background: var(--yellow-lt);">🛒</span> Pesan Online
                    </span>
                    <span class="feature-pill">
                        <span class="pill-icon" style="background: var(--blue-lt);">🚚</span> Delivery &amp; Pickup
                    </span>
                    <span class="feature-pill">
                        <span class="pill-icon" style="background: var(--orange-lt);">💬</span> Chat Admin
                    </span>
                </div>

                <div class="d-flex gap-3 flex-wrap">
                    <a href="{{ route('products.index') }}" class="btn btn-primary" style="padding: .7rem 1.8rem; font-size: 1rem;">
                        <i class="bi bi-grid me-2"></i>Lihat Katalog
                    </a>
                    @guest
                        <a href="{{ route('register') }}" class="btn btn-outline-secondary" style="padding: .7rem 1.8rem; font-size: 1rem;">
                            <i class="bi bi-person-plus me-2"></i>Daftar Gratis
                        </a>
                    @else
                        @if(!Auth::user()->isAdmin())
                        <a href="{{ route('cart.index') }}" class="btn btn-secondary" style="padding: .7rem 1.8rem; font-size: 1rem;">
                            <i class="bi bi-cart me-2"></i>Keranjang Saya
                        </a>
                        @endif
                    @endguest
                </div>
            </div>
            <div class="col-lg-6 d-none d-lg-block">
                <img src="{{ asset('images/bakery_hero.png') }}" alt="Produk Viera Bakery"
                     class="home-hero-img">
            </div>
        </div>
    </div>
</section>

{{-- ══════════════════════════════════════════════
     SHOWCASE FOTO — 3 Kategori Produk
══════════════════════════════════════════════ --}}
<section class="section-vb bg-white">
    <div class="container">
        <div class="text-center mb-4">
            <div class="section-badge">📸 Koleksi Kami</div>
            <div class="section-heading">Temukan Pilihan Terbaik</div>
            <p class="section-subheading">Dari pastry crispy hingga bolu lembut, semua ada di sini</p>
        </div>
        <div class="row g-3">
            {{-- Kiri: Aneka Pastry (tall card) --}}
            <div class="col-md-6">
                <a href="{{ $catPastry ? route('products.index', ['category' => $catPastry->id]) : route('products.index') }}" class="text-decoration-none">
                    <div class="showcase-card tall">
                        <img src="{{ asset('images/bakery_pastry.png') }}" alt="Aneka Pastry">
                        <div class="showcase-label">🥐 Aneka Pastry</div>
                    </div>
                </a>
            </div>
            {{-- Kanan: 2 card kecil --}}
            <div class="col-md-6">
                <div class="d-flex flex-column gap-3">
                    {{-- Aneka Kue Kering --}}
                    <a href="{{ $catKueKering ? route('products.index', ['category' => $catKueKering->id]) : route('products.index') }}" class="text-decoration-none">
                        <div class="showcase-card">
                            <img src="{{ asset('images/aneka_kue_kering.png') }}" alt="Aneka Kue Kering">
                            <div class="showcase-label">🍪 Aneka Kue Kering</div>
                        </div>
                    </a>
                    {{-- Aneka Bolu Lembut --}}
                    <a href="{{ $catBolu ? route('products.index', ['category' => $catBolu->id]) : route('products.index') }}" class="text-decoration-none">
                        <div class="showcase-card">
                            <img src="{{ asset('images/aneka_bolu_lembut.png') }}" alt="Aneka Bolu Lembut">
                            <div class="showcase-label">🎂 Aneka Bolu Lembut</div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ══════════════════════════════════════════════
     SECTION: BEST SELLER
══════════════════════════════════════════════ --}}
<section class="section-vb bg-cream">
    <div class="container">
        <div class="d-flex align-items-end justify-content-between mb-4 flex-wrap gap-2">
            <div>
                <div class="section-badge">⭐ Paling Diminati</div>
                <div class="section-heading">Produk Best Seller</div>
                <p class="section-subheading mb-0">Pilihan favorit pelanggan setia kami</p>
            </div>
            <a href="{{ route('products.index') }}" class="btn btn-outline-primary">
                Lihat Semua <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>

        @if(isset($bestSellers) && $bestSellers->isNotEmpty())
            <div class="row g-3">
                @foreach($bestSellers as $i => $product)
                    <div class="col-6 col-md-3">
                        <div class="home-product-card">
                            <a href="{{ route('products.show', $product) }}" class="text-decoration-none">
                                @if($product->image && file_exists(public_path('storage/'.$product->image)))
                                    <img src="{{ asset('storage/'.$product->image) }}"
                                         alt="{{ $product->name }}" class="hpc-img">
                                @else
                                    @php $emojis = ['🫓','🥐','🍪','🍞']; @endphp
                                    <div class="hpc-placeholder"
                                         style="background: {{ ['var(--yellow-lt)','var(--blue-lt)','var(--orange-lt)','#F0FDF4'][$i % 4] }}">
                                        {{ $emojis[$i % 4] }}
                                    </div>
                                @endif
                            </a>
                            <div class="hpc-body">
                                <span class="hpc-cat orange">{{ $product->category->name ?? 'Produk' }}</span>
                                <div class="hpc-name">
                                    <a href="{{ route('products.show', $product) }}"
                                       style="color: var(--text); text-decoration:none;">
                                        {{ $product->name }}
                                    </a>
                                </div>
                                <div class="hpc-price">Rp {{ number_format($product->price, 0, ',', '.') }}</div>
                                @auth
                                    @if(!Auth::user()->isAdmin())
                                        @if($product->stock > 0)
                                            <form method="POST" action="{{ route('cart.add') }}">
                                                @csrf
                                                <input type="hidden" name="product_id" value="{{ $product->id }}">
                                                <input type="hidden" name="quantity" value="1">
                                                <button type="submit" class="btn btn-primary btn-sm w-100">
                                                    <i class="bi bi-cart-plus me-1"></i>Tambah
                                                </button>
                                            </form>
                                        @else
                                            <button class="btn btn-secondary btn-sm w-100" disabled>Habis</button>
                                        @endif
                                    @endif
                                @else
                                    <a href="{{ route('login') }}" class="btn btn-outline-primary btn-sm w-100">
                                        Login untuk Pesan
                                    </a>
                                @endauth
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            {{-- Dummy placeholder jika belum ada produk --}}
            <div class="row g-3">
                @foreach([['🫓','Kue Tart Ulang Tahun','Rp 145.000'],['🥐','Croissant Butter Premium','Rp 28.000'],['🍪','Nastar Keju Box','Rp 85.000'],['🍰','Lapis Legit Prunes','Rp 120.000']] as $i => $item)
                    <div class="col-6 col-md-3">
                        <div class="home-product-card">
                            <div class="hpc-placeholder"
                                 style="background: {{ ['var(--yellow-lt)','var(--blue-lt)','var(--orange-lt)','#F0FDF4'][$i] }}">
                                {{ $item[0] }}
                            </div>
                            <div class="hpc-body">
                                <span class="hpc-cat orange">Produk Unggulan</span>
                                <div class="hpc-name">{{ $item[1] }}</div>
                                <div class="hpc-price">{{ $item[2] }}</div>
                                <a href="{{ route('login') }}" class="btn btn-outline-primary btn-sm w-100">
                                    Login untuk Pesan
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</section>

{{-- ══════════════════════════════════════════════
     SECTION: EDISI HAMPERS
══════════════════════════════════════════════ --}}
<section class="section-vb bg-blue">
    <div class="container">
        <div class="row align-items-center g-5 mb-4">
            <div class="col-lg-5">
                <div class="section-badge blue">🎁 Koleksi Spesial</div>
                <div class="section-heading">Edisi Hampers</div>
                <p class="section-subheading mb-3">
                    Hadiah istimewa untuk momen berharga — ulang tahun, lebaran, pernikahan, dan perayaan spesial lainnya. Dikemas dengan cantik, dikirim dengan cinta. 💝
                </p>
                <a href="{{ $hampersCategory ? route('products.index', ['category' => $hampersCategory->id]) : route('products.index') }}" class="btn btn-primary">
                    <i class="bi bi-gift me-2"></i>Lihat Koleksi Hampers
                </a>
            </div>
            <div class="col-lg-7">
                <img src="{{ asset('images/bakery_hampers.png') }}"
                     alt="Hampers Viera Bakery"
                     style="width:100%; border-radius: 20px; box-shadow: var(--shadow-lg);">
            </div>
        </div>

        @if(isset($hampers) && $hampers->isNotEmpty())
            <div class="row g-3">
                @foreach($hampers as $i => $product)
                    <div class="col-6 col-md-3">
                        <div class="home-product-card">
                            <a href="{{ route('products.show', $product) }}" class="text-decoration-none">
                                @if($product->image && file_exists(public_path('storage/'.$product->image)))
                                    <img src="{{ asset('storage/'.$product->image) }}"
                                         alt="{{ $product->name }}" class="hpc-img">
                                @else
                                    @php $hemojis = ['🎁','🎀','🎊','🎉']; @endphp
                                    <div class="hpc-placeholder"
                                         style="background: {{ ['var(--blue-lt)','var(--yellow-lt)','var(--orange-lt)','#F0FDF4'][$i % 4] }}">
                                        {{ $hemojis[$i % 4] }}
                                    </div>
                                @endif
                            </a>
                            <div class="hpc-body">
                                <span class="hpc-cat blue">Edisi Hampers</span>
                                <div class="hpc-name">
                                    <a href="{{ route('products.show', $product) }}"
                                       style="color: var(--text); text-decoration:none;">
                                        {{ $product->name }}
                                    </a>
                                </div>
                                <div class="hpc-price">Rp {{ number_format($product->price, 0, ',', '.') }}</div>
                                @auth
                                    @if(!Auth::user()->isAdmin())
                                        @if($product->stock > 0)
                                            <form method="POST" action="{{ route('cart.add') }}">
                                                @csrf
                                                <input type="hidden" name="product_id" value="{{ $product->id }}">
                                                <input type="hidden" name="quantity" value="1">
                                                <button type="submit" class="btn btn-primary btn-sm w-100">
                                                    <i class="bi bi-cart-plus me-1"></i>Tambah
                                                </button>
                                            </form>
                                        @else
                                            <button class="btn btn-secondary btn-sm w-100" disabled>Habis</button>
                                        @endif
                                    @endif
                                @else
                                    <a href="{{ route('login') }}" class="btn btn-outline-primary btn-sm w-100">
                                        Login untuk Pesan
                                    </a>
                                @endauth
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="row g-3">
                @foreach([['🎁','Hampers Lebaran Mix','Rp 250.000'],['🎀','Hampers Birthday Cake','Rp 185.000'],['🎊','Hampers Wedding Cookies','Rp 320.000'],['🎉','Hampers Premium Box','Rp 450.000']] as $i => $item)
                    <div class="col-6 col-md-3">
                        <div class="home-product-card">
                            <div class="hpc-placeholder"
                                 style="background: {{ ['var(--blue-lt)','var(--yellow-lt)','var(--orange-lt)','#F0FDF4'][$i] }}">
                                {{ $item[0] }}
                            </div>
                            <div class="hpc-body">
                                <span class="hpc-cat blue">Edisi Hampers</span>
                                <div class="hpc-name">{{ $item[1] }}</div>
                                <div class="hpc-price">{{ $item[2] }}</div>
                                <a href="{{ route('login') }}" class="btn btn-outline-primary btn-sm w-100">
                                    Login untuk Pesan
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</section>

{{-- ══════════════════════════════════════════════
     SECTION: KEUNGGULAN LAYANAN
══════════════════════════════════════════════ --}}
<section class="section-vb bg-white">
    <div class="container">
        <div class="text-center mb-4">
            <div class="section-badge">✨ Mengapa Kami?</div>
            <div class="section-heading">Layanan Terbaik Viera Bakery</div>
        </div>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card text-center h-100" style="border: 2px solid var(--yellow-lt);">
                    <div style="width:64px; height:64px; background: var(--yellow-lt); border-radius:50%;
                                display:flex; align-items:center; justify-content:center;
                                font-size:1.75rem; margin: 0 auto 1rem;">🛒</div>
                    <h6 style="font-family: var(--font-heading); font-weight:700; font-size:1rem;">
                        Pesan Online 24 Jam
                    </h6>
                    <p class="mb-0" style="font-size:.84rem; color: var(--muted); line-height:1.6;">
                        Pesan kapan saja tanpa perlu telepon atau WhatsApp. Sistem mencatat semuanya secara otomatis.
                    </p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center h-100" style="border: 2px solid var(--blue-lt);">
                    <div style="width:64px; height:64px; background: var(--blue-lt); border-radius:50%;
                                display:flex; align-items:center; justify-content:center;
                                font-size:1.75rem; margin: 0 auto 1rem;">🚚</div>
                    <h6 style="font-family: var(--font-heading); font-weight:700; font-size:1rem;">
                        Delivery &amp; Self Pickup
                    </h6>
                    <p class="mb-0" style="font-size:.84rem; color: var(--muted); line-height:1.6;">
                        Pilih diantar ke alamat tujuan atau ambil sendiri di toko. Fleksibel sesuai kebutuhanmu.
                    </p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center h-100" style="border: 2px solid var(--orange-lt);">
                    <div style="width:64px; height:64px; background: var(--orange-lt); border-radius:50%;
                                display:flex; align-items:center; justify-content:center;
                                font-size:1.75rem; margin: 0 auto 1rem;">💬</div>
                    <h6 style="font-family: var(--font-heading); font-weight:700; font-size:1rem;">
                        Chat Langsung Admin
                    </h6>
                    <p class="mb-0" style="font-size:.84rem; color: var(--muted); line-height:1.6;">
                        Tanya langsung ke admin mengenai pesanan, jadwal pengambilan, atau pertanyaan lainnya.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ══════════════════════════════════════════════
     CTA BANNER
══════════════════════════════════════════════ --}}
@guest
<section class="section-vb" style="background: var(--bg);">
    <div class="container">
        <div class="cta-banner">
            <div style="font-size:3rem; margin-bottom:.75rem;">🎉</div>
            <h2>Siap Memesan Sekarang?</h2>
            <p>Daftar akun gratis dan dapatkan pengalaman pemesanan kue yang mudah, cepat, dan menyenangkan!</p>
            <div class="d-flex justify-content-center gap-3 flex-wrap">
                <a href="{{ route('register') }}" class="btn-cta-white">
                    <i class="bi bi-person-plus me-2"></i>Daftar Gratis Sekarang
                </a>
                <a href="{{ route('products.index') }}"
                   style="color:#fff; text-decoration:none; font-weight:600; font-size:.95rem;
                          display:inline-flex; align-items:center; gap:.4rem;">
                    <i class="bi bi-grid"></i> Lihat Katalog Dulu
                </a>
            </div>
        </div>
    </div>
</section>
@endguest

@endsection
