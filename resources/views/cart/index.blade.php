@extends('layouts.app')
@section('title', 'Keranjang Belanja — Viera Bakery')
@section('content')
<div class="container py-4">

<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <h2 class="section-title">Keranjang Belanja</h2>
    <a href="{{ route('products.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Lanjut Belanja
    </a>
</div>

@if(!isset($items) || $items->isEmpty())
    <div class="card text-center py-5">
        <div style="font-size:3.5rem; margin-bottom:.75rem;">🛒</div>
        <h5 style="font-family: var(--font-heading);">Keranjang masih kosong</h5>
        <p class="text-muted mb-3">Yuk, tambahkan produk dari katalog kami!</p>
        <div>
            <a href="{{ route('products.index') }}" class="btn btn-primary">
                <i class="bi bi-grid me-2"></i>Lihat Katalog
            </a>
        </div>
    </div>
@else
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card p-0">
                <div class="card-header-vb px-4 pt-4 pb-0 border-0">
                    <i class="bi bi-cart3"></i> Item Keranjang
                </div>
                <div class="table-responsive">
                    <table class="table-vb w-100">
                        <thead>
                            <tr>
                                <th style="width:40%">Produk</th>
                                <th class="text-center">Harga</th>
                                <th class="text-center" style="width:130px">Jumlah</th>
                                <th class="text-center">Subtotal</th>
                                <th class="text-center">Hapus</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($items as $item)
                            <tr id="cart-row-{{ $item->id }}">
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        @if($item->product && $item->product->image && file_exists(public_path('storage/'.$item->product->image)))
                                            <img src="{{ asset('storage/'.$item->product->image) }}"
                                                 alt="{{ $item->product->name }}"
                                                 style="width:44px; height:44px; object-fit:cover;
                                                        border-radius:8px; flex-shrink:0; border: 1px solid var(--border);">
                                        @else
                                            <div style="width:44px; height:44px; background: var(--yellow-lt);
                                                        border-radius:8px; display:flex; align-items:center;
                                                        justify-content:center; font-size:1.5rem; flex-shrink:0;">
                                                <i class="bi bi-image" style="color:var(--orange-dk); font-size:1rem;"></i>
                                            </div>
                                        @endif
                                        <div>
                                            <div class="fw-600" style="font-size:.9rem;">
                                                {{ $item->product->name ?? 'Produk dihapus' }}
                                            </div>
                                            <div style="font-size:.75rem; color: var(--muted);">
                                                {{ $item->product->category->name ?? '' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center" style="font-size:.875rem; white-space:nowrap;">
                                    Rp {{ number_format($item->price, 0, ',', '.') }}
                                </td>
                                <td class="text-center">
                                    <div class="qty-control">
                                        <button type="button" class="qty-btn qty-minus {{ $item->quantity <= 1 ? 'disabled' : '' }}"
                                                data-item-id="{{ $item->id }}"
                                                onclick="updateQty({{ $item->id }}, -1)"
                                                {{ $item->quantity <= 1 ? 'disabled' : '' }}>
                                            <i class="bi bi-dash"></i>
                                        </button>
                                        <span class="qty-value" id="qty-{{ $item->id }}">{{ $item->quantity }}</span>
                                        <button type="button" class="qty-btn qty-plus"
                                                data-item-id="{{ $item->id }}"
                                                onclick="updateQty({{ $item->id }}, 1)">
                                            <i class="bi bi-plus"></i>
                                        </button>
                                    </div>
                                </td>
                                <td class="text-center fw-600" style="color: var(--orange); white-space:nowrap;">
                                    <span id="subtotal-{{ $item->id }}">Rp {{ number_format($item->quantity * $item->price, 0, ',', '.') }}</span>
                                </td>
                                <td class="text-center">
                                    <form method="POST" action="{{ route('cart.remove', $item) }}">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm"
                                                onclick="return confirm('Hapus item ini?')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header-vb"><i class="bi bi-receipt"></i> Ringkasan</div>
                @php $total = $items->sum(fn($i) => $i->quantity * $i->price); @endphp
                <div class="d-flex justify-content-between mb-2" style="font-size:.9rem;">
                    <span class="text-muted">Total Item</span>
                    <span id="summary-total-items">{{ $items->sum('quantity') }} item</span>
                </div>
                <hr style="border-color: var(--border);">
                <div class="d-flex justify-content-between mb-4">
                    <span class="fw-700" style="font-family: var(--font-heading);">Total Harga</span>
                    <span class="fw-800" id="summary-total-price" style="color: var(--orange); font-family: var(--font-heading); font-size:1.1rem;">
                        Rp {{ number_format($total, 0, ',', '.') }}
                    </span>
                </div>
                <a href="{{ route('checkout') }}" class="btn btn-primary w-100">
                    <i class="bi bi-bag-check me-2"></i>Lanjut ke Checkout
                </a>
                <a href="{{ route('products.index') }}" class="btn btn-outline-secondary w-100 mt-2">
                    Tambah Produk Lagi
                </a>
            </div>
        </div>
    </div>
@endif

</div>

<style>
    .qty-control {
        display: inline-flex;
        align-items: center;
        gap: 0;
        border: 1.5px solid var(--border);
        border-radius: 8px;
        overflow: hidden;
        background: #fff;
    }
    .qty-btn {
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: none;
        background: #fff;
        color: var(--orange);
        font-size: 1rem;
        cursor: pointer;
        transition: all .15s ease;
        padding: 0;
    }
    .qty-btn:hover:not(.disabled):not(:disabled) {
        background: var(--orange-lt);
    }
    .qty-btn.disabled, .qty-btn:disabled {
        color: var(--muted);
        cursor: not-allowed;
        opacity: .4;
    }
    .qty-value {
        min-width: 36px;
        text-align: center;
        font-weight: 700;
        font-size: .9rem;
        border-left: 1.5px solid var(--border);
        border-right: 1.5px solid var(--border);
        line-height: 32px;
    }
</style>

<script>
    var csrfToken = '{{ csrf_token() }}';

    function fmt(n) {
        return 'Rp ' + new Intl.NumberFormat('id-ID').format(n);
    }

    function updateQty(itemId, delta) {
        var qtyEl = document.getElementById('qty-' + itemId);
        var currentQty = parseInt(qtyEl.textContent);
        var newQty = currentQty + delta;

        if (newQty < 1) return;

        // Disable buttons temporarily
        var row = document.getElementById('cart-row-' + itemId);
        var btns = row.querySelectorAll('.qty-btn');
        btns.forEach(function(b) { b.disabled = true; });

        fetch('/cart/item/' + itemId + '/update-qty', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ quantity: newQty })
        })
        .then(function(r) { return r.json().then(function(d) { return { ok: r.ok, data: d }; }); })
        .then(function(res) {
            if (res.ok) {
                qtyEl.textContent = res.data.item_qty;
                document.getElementById('subtotal-' + itemId).textContent = fmt(res.data.item_subtotal);
                document.getElementById('summary-total-items').textContent = res.data.total_items + ' item';
                document.getElementById('summary-total-price').textContent = fmt(res.data.total_price);

                // Toggle minus button
                var minusBtn = row.querySelector('.qty-minus');
                if (res.data.item_qty <= 1) {
                    minusBtn.disabled = true;
                    minusBtn.classList.add('disabled');
                } else {
                    minusBtn.disabled = false;
                    minusBtn.classList.remove('disabled');
                }
            } else {
                alert(res.data.message || 'Gagal update quantity.');
            }

            // Re-enable buttons
            btns.forEach(function(b) {
                if (!b.classList.contains('qty-minus') || parseInt(qtyEl.textContent) > 1) {
                    b.disabled = false;
                }
            });
        })
        .catch(function() {
            alert('Terjadi kesalahan jaringan.');
            btns.forEach(function(b) { b.disabled = false; });
        });
    }
</script>

@endsection
