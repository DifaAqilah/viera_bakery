@extends('layouts.app')
@section('title', 'Katalog Produk — Viera Bakery')
@section('content')
<div class="container py-4">

{{-- Header --}}
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
        <h2 class="section-title">Katalog Produk</h2>
        <p class="text-muted mb-0" style="font-size:.875rem;">
            Temukan kue favorit Anda dari koleksi kami
        </p>
    </div>
    @auth
        @if(!Auth::user()->isAdmin())
        <a href="{{ route('cart.index') }}" class="btn btn-outline-primary">
            <i class="bi bi-cart me-1"></i>Lihat Keranjang
        </a>
        @endif
    @endauth
</div>

{{-- Search & Filter --}}
<div class="card mb-4" style="border: 1px solid #e0e0e0; border-radius: 8px; padding: 1.25rem;">
    <form method="GET" action="{{ route('products.index') }}" class="d-flex gap-2 flex-wrap align-items-end">
        {{-- Search Input --}}
        <div class="flex-grow-1" style="min-width: 200px;">
            <label class="form-label mb-2" style="font-weight: 600; font-size: .875rem;">Cari Produk</label>
            <div class="input-group">
                <span class="input-group-text" style="background-color: #f8f9fa; border-right: none;">
                    <i class="bi bi-search"></i>
                </span>
                <input type="text" name="search" class="form-control" 
                       placeholder="Cari nama produk..." 
                       value="{{ request('search') }}"
                       style="border-left: none;">
            </div>
        </div>

        {{-- Category Filter --}}
        <div style="min-width: 180px;">
            <label class="form-label mb-2" style="font-weight: 600; font-size: .875rem;">Kategori</label>
            <select name="category" class="form-select">
                <option value="">Semua Kategori</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" 
                            {{ request('category') == $cat->id ? 'selected' : '' }}>
                        {{ $cat->name }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Submit & Reset Buttons --}}
        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-search me-1"></i>Cari
            </button>
            <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-counterclockwise me-1"></i>Reset
            </a>
        </div>
    </form>
</div>

@if($products->isEmpty())
    <div class="card text-center py-5">
        <div style="font-size:3rem; margin-bottom:.75rem;">🍰</div>
        <h5 style="font-family: var(--font-heading);">
            @if(request('search') || request('category'))
                Produk tidak ditemukan
            @else
                Belum ada produk tersedia
            @endif
        </h5>
        <p class="text-muted">
            @if(request('search') || request('category'))
                Coba ubah pencarian atau filter kategori Anda
            @else
                Produk akan segera ditambahkan. Pantau terus!
            @endif
        </p>
    </div>
@else
    <div class="row g-3">
        @foreach($products as $product)
            <div class="col-6 col-md-4 col-lg-3">
                <div class="product-card">
                    <a href="{{ route('products.show', $product) }}">
                        @if($product->image && file_exists(public_path('storage/'.$product->image)))
                            <img src="{{ asset('storage/'.$product->image) }}"
                                 alt="{{ $product->name }}" class="product-img">
                        @else
                            <div class="product-img-placeholder"><i class="bi bi-image" style="font-size:3rem; color:var(--muted);"></i></div>
                        @endif
                    </a>
                    <div class="product-body">
                        <span class="product-category">{{ $product->category->name ?? '—' }}</span>
                        <div class="product-name">
                            <a href="{{ route('products.show', $product) }}"
                               style="color: var(--text); text-decoration:none;">
                                {{ $product->name }}
                            </a>
                        </div>
                        <div class="product-price">Rp {{ number_format($product->price, 0, ',', '.') }}</div>
                        <div class="product-stock mb-3">Stok: {{ $product->stock }}</div>
                        @auth
                            @if(!Auth::user()->isAdmin())
                                @if($product->stock > 0)
                                    <form method="POST" action="{{ route('cart.add') }}">
                                        @csrf
                                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                                        <input type="hidden" name="quantity" value="1">
                                        <button type="submit" class="btn btn-primary w-100 btn-sm">
                                            <i class="bi bi-cart-plus me-1"></i>Tambah
                                        </button>
                                    </form>
                                @else
                                    <button class="btn btn-secondary w-100 btn-sm" disabled>Habis</button>
                                @endif
                            @endif
                        @else
                            <a href="{{ route('login') }}" class="btn btn-outline-primary w-100 btn-sm">
                                Login untuk Pesan
                            </a>
                        @endauth
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    <div class="mt-4">{{ $products->links() }}</div>
@endif

</div>
@endsection
