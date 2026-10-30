@extends('layouts.app')
@section('title', 'Checkout — Viera Bakery')
@section('content')
<div class="container py-4">

<div class="mb-3">
    <a href="{{ route('cart.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Kembali ke Keranjang
    </a>
</div>

<h2 class="section-title mb-4">Checkout Pesanan</h2>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card">
            <form method="POST" action="{{ route('order.place') }}">
                @csrf

                <div class="card-header-vb">
                    <i class="bi bi-truck"></i> Metode Penerimaan
                </div>

                <div class="mb-3">
                    <div class="d-flex gap-3 flex-wrap">
                        <div class="flex-fill">
                            <input type="radio" class="btn-check" name="delivery_method"
                                   id="pickup" value="self_pickup"
                                   {{ old('delivery_method','self_pickup') === 'self_pickup' ? 'checked' : '' }}>
                            <label class="btn btn-outline-primary w-100 py-3 delivery-btn" for="pickup">
                                <div style="font-size:1.5rem; margin-bottom:.25rem;">🏪</div>
                                <div class="fw-600">Ambil Sendiri</div>
                                <div style="font-size:.75rem;" class="delivery-btn-sub">Gratis ongkos</div>
                            </label>
                        </div>
                        <div class="flex-fill">
                            <input type="radio" class="btn-check" name="delivery_method"
                                   id="delivery" value="delivery"
                                   {{ old('delivery_method') === 'delivery' ? 'checked' : '' }}>
                            <label class="btn btn-outline-primary w-100 py-3 delivery-btn" for="delivery">
                                <div style="font-size:1.5rem; margin-bottom:.25rem;">🚚</div>
                                <div class="fw-600">Diantar</div>
                                <div style="font-size:.75rem;" class="delivery-btn-sub">Ke alamat tujuan</div>
                            </label>
                        </div>
                    </div>
                    @error('delivery_method')
                        <div class="text-danger mt-1" style="font-size:.82rem;">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Fields untuk Diantar --}}
                <div class="mb-3" id="address-field">
                    <label class="form-label" for="delivery_address">
                        Alamat Pengiriman <span class="text-danger">*</span>
                    </label>
                    <textarea id="delivery_address" name="delivery_address"
                              class="form-control @error('delivery_address') is-invalid @enderror"
                              rows="3" placeholder="Tuliskan alamat lengkap pengiriman Anda...">{{ old('delivery_address', auth()->user()->address ?? '') }}</textarea>
                    @error('delivery_address')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3" id="delivery-date-field">
                    <label class="form-label" for="delivery_date">
                        Tanggal Pengiriman <span class="text-danger">*</span>
                    </label>
                    <input type="date" id="delivery_date" name="delivery_date"
                           class="form-control @error('delivery_date') is-invalid @enderror"
                           value="{{ old('delivery_date') }}" min="{{ date('Y-m-d', strtotime('+1 day')) }}">
                    <small class="form-text text-muted d-block mt-1">
                        Minimal besok atau seterusnya
                    </small>
                    @error('delivery_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Fields untuk Ambil Sendiri --}}
                <div class="mb-3" id="pickup-date-field">
                    <label class="form-label" for="pickup_date">
                        Tanggal Pengambilan <span class="text-danger">*</span>
                    </label>
                    <input type="date" id="pickup_date" name="pickup_date"
                           class="form-control @error('pickup_date') is-invalid @enderror"
                           value="{{ old('pickup_date') }}" min="{{ date('Y-m-d', strtotime('+1 day')) }}">
                    <small class="form-text text-muted d-block mt-1">
                        Minimal besok atau seterusnya
                    </small>
                    @error('pickup_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Fields yang selalu tampil --}}
                <div class="mb-3">
                    <label class="form-label" for="customer_phone">
                        Nomor Telepon <span class="text-danger">*</span>
                    </label>
                    <input type="tel" id="customer_phone" name="customer_phone"
                           class="form-control @error('customer_phone') is-invalid @enderror"
                           value="{{ old('customer_phone', auth()->user()->phone_number ?? '') }}"
                           placeholder="Contoh: 08123456789" required>
                    @error('customer_phone')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label" for="note">Catatan Pesanan <span class="text-muted">(opsional)</span></label>
                    <textarea id="note" name="note" class="form-control" rows="2"
                              placeholder="Contoh: topping extra, tanpa gula, dll.">{{ old('note') }}</textarea>
                </div>

                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-credit-card me-2"></i>Bayar Pesanan
                </button>
            </form>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card">
            <div class="card-header-vb"><i class="bi bi-receipt"></i> Ringkasan Pesanan</div>
            <div class="table-responsive">
                <table class="table-vb w-100">
                    <thead>
                        <tr>
                            <th>Produk</th>
                            <th class="text-center">Qty</th>
                            <th class="text-end">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $total = 0; @endphp
                        @foreach($items as $item)
                            @php $sub = $item->quantity * $item->price; $total += $sub; @endphp
                            <tr>
                                <td style="font-size:.82rem;">{{ $item->product->name ?? '—' }}</td>
                                <td class="text-center" style="font-size:.82rem;">{{ $item->quantity }}</td>
                                <td class="text-end" style="font-size:.82rem; white-space:nowrap;">
                                    Rp {{ number_format($sub, 0, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <hr style="border-color: var(--border);">
            <div class="d-flex justify-content-between py-2" style="font-size: .875rem;">
                <span>Subtotal</span>
                <span id="subtotal-display">Rp {{ number_format($total, 0, ',', '.') }}</span>
            </div>
            <div class="d-flex justify-content-between py-2" style="font-size: .875rem;" id="shipping-row">
                <div>
                    <span>Ongkos Kirim</span>
                    <div id="shipping-note" style="font-size:.72rem; color: var(--muted); display:none;">
                        *harga ongkos kirim Jakarta dan Bekasi
                    </div>
                </div>
                <span id="shipping-display">Rp 0</span>
            </div>
            <hr style="border-color: var(--border);">
            <div class="d-flex justify-content-between">
                <span class="fw-700" style="font-family: var(--font-heading);">Total</span>
                <span class="fw-800" id="total-display" style="color: var(--orange); font-family: var(--font-heading); font-size:1.1rem;">
                    Rp {{ number_format($total, 0, ',', '.') }}
                </span>
            </div>
        </div>
    </div>
</div>

</div>

<input type="hidden" id="subtotal-value" value="{{ $total }}">

<style>
    /* Active delivery button: text putih */
    .btn-check:checked + .delivery-btn {
        color: #fff !important;
    }
    .btn-check:checked + .delivery-btn .delivery-btn-sub {
        color: rgba(255,255,255,.85) !important;
    }
    .btn-check:checked + .delivery-btn .fw-600 {
        color: #fff !important;
    }
</style>

<script>
    (function(){
        const SHIPPING_COST = 25000;
        const pickupRad = document.getElementById('pickup');
        const delivRad  = document.getElementById('delivery');
        const addrField = document.getElementById('address-field');
        const addrInput = document.getElementById('delivery_address');
        const delivDateField = document.getElementById('delivery-date-field');
        const delivDateInput = document.getElementById('delivery_date');
        const pickupDateField = document.getElementById('pickup-date-field');
        const pickupDateInput = document.getElementById('pickup_date');
        const shippingRow = document.getElementById('shipping-row');
        const shippingDisplay = document.getElementById('shipping-display');
        const shippingNote = document.getElementById('shipping-note');
        const totalDisplay = document.getElementById('total-display');
        const subtotalValue = parseFloat(document.getElementById('subtotal-value').value);

        function fmt(n) {
            return 'Rp ' + new Intl.NumberFormat('id-ID', {
                minimumFractionDigits: 0,
                maximumFractionDigits: 0,
            }).format(n);
        }

        function toggle(){
            const isDelivery = delivRad.checked;

            // Show/hide address & delivery date fields
            addrField.style.display = isDelivery ? 'block' : 'none';
            addrInput.required = isDelivery;
            delivDateField.style.display = isDelivery ? 'block' : 'none';
            delivDateInput.required = isDelivery;

            // Show/hide pickup date field
            pickupDateField.style.display = isDelivery ? 'none' : 'block';
            pickupDateInput.required = !isDelivery;

            // Ongkos kirim
            const shippingCost = isDelivery ? SHIPPING_COST : 0;
            shippingDisplay.textContent = fmt(shippingCost);
            shippingNote.style.display = isDelivery ? 'block' : 'none';

            // Total
            const total = subtotalValue + shippingCost;
            totalDisplay.textContent = fmt(total);
        }

        toggle();
        pickupRad.addEventListener('change', toggle);
        delivRad.addEventListener('change', toggle);
    })();
</script>

@endsection
