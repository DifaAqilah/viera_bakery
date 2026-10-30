@extends('layouts.app')
@section('title', $product->name . ' — Viera Bakery')
@section('content')
<div class="container py-4">

<div class="mb-3">
    <a href="{{ route('products.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Kembali ke Katalog
    </a>
</div>

<div class="card">
    <div class="row g-0">
        <div class="col-md-5">
            @if($product->image && file_exists(public_path('storage/'.$product->image)))
                <img src="{{ asset('storage/'.$product->image) }}"
                     alt="{{ $product->name }}"
                     style="width:100%; height:360px; object-fit:cover; border-radius: var(--radius) 0 0 var(--radius);">
            @else
                <div style="width:100%; height:360px; background: linear-gradient(135deg, var(--yellow-lt), var(--blue-lt));
                            display:flex; align-items:center; justify-content:center;
                            font-size:5rem; border-radius: var(--radius) 0 0 var(--radius);">
                    <i class="bi bi-image" style="font-size:4rem; color:var(--muted);"></i>
                </div>
            @endif
        </div>
        <div class="col-md-7 p-4 d-flex flex-column">
            <span class="product-category mb-2">{{ $product->category->name ?? '—' }}</span>
            <h2 style="font-weight:800; font-size:1.75rem; margin-bottom:.5rem;">{{ $product->name }}</h2>
            <div style="font-size:1.65rem; font-weight:800; color: var(--orange); margin-bottom:1rem; font-family: var(--font-heading);">
                Rp {{ number_format($product->price, 0, ',', '.') }}
            </div>
            <p class="text-muted mb-3" style="font-size:.9rem; line-height:1.65;">
                {{ $product->description ?: 'Tidak ada deskripsi produk.' }}
            </p>
            <div class="mb-4">
                @if($product->stock > 0)
                    <span class="badge-status badge-siap-ambil">
                        <i class="bi bi-check-circle me-1"></i>Stok Tersedia ({{ $product->stock }})
                    </span>
                @else
                    <span class="badge-status badge-dibatalkan">
                        <i class="bi bi-x-circle me-1"></i>Stok Habis
                    </span>
                @endif
            </div>
            @auth
                @if($product->stock > 0)
                    <form method="POST" action="{{ route('cart.add') }}">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <label class="form-label mb-0 fw-600" style="white-space:nowrap;">Jumlah:</label>
                            <input type="number" name="quantity" value="1"
                                   min="1" max="{{ $product->stock }}"
                                   class="form-control" style="width:90px;">
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-cart-plus me-2"></i>Tambah ke Keranjang
                            </button>
                        </div>
                    </form>
                @else
                    <button class="btn btn-secondary" disabled>Stok Habis</button>
                @endif
            @else
                <a href="{{ route('login') }}" class="btn btn-primary">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Login untuk Memesan
                </a>
            @endauth
        </div>
    </div>
</div>

</div>
@endsection
