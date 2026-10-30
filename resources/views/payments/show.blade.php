@extends('layouts.app')
@section('title', 'Pembayaran Pesanan — Viera Bakery')
@section('content')
<div class="container py-4">

<div class="mb-3">
    <a href="{{ route('orders.history') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Kembali ke Pesanan
    </a>
</div>

<h2 class="section-title mb-4">Pembayaran Pesanan #{{ $order->id }}</h2>

<div class="row g-4">
    {{-- Kolom Kiri: Pembayaran --}}
    <div class="col-lg-7">
        {{-- Pilihan Metode Pembayaran --}}
        <div class="card mb-3">
            <div class="card-header-vb">
                <i class="bi bi-wallet2"></i> Metode Pembayaran
            </div>
            <div class="payment-method-options">
                <button type="button" class="payment-method-btn" id="btn-method-onsite" onclick="selectPaymentMethod('onsite')">
                    <i class="bi bi-shop"></i> Onsite
                </button>
                <button type="button" class="payment-method-btn" id="btn-method-qris" onclick="selectPaymentMethod('qris')">
                    <i class="bi bi-qr-code-scan"></i> QRIS
                </button>
            </div>
        </div>

        {{-- Alert belum pilih metode --}}
        <div id="alert-no-method" style="display:none;" class="alert alert-warning">
            <i class="bi bi-exclamation-triangle me-2"></i>Silakan pilih metode pembayaran terlebih dahulu.
        </div>

        {{-- Section QRIS: Midtrans card diganti QRIS --}}
        <div id="section-qris-midtrans" class="payment-section" style="display:none;">
            <div class="card mb-3">
                <div class="card-header-vb">
                    <i class="bi bi-qr-code-scan"></i> Pembayaran via QRIS
                </div>
                <div class="payment-method-badge">
                    <i class="bi bi-qr-code-scan"></i> Scan QRIS untuk Pembayaran
                </div>
                <div class="qris-display-area">
                    <img id="qris-image" src="{{ asset('images/qris.jpg') }}" alt="QRIS Viera Bakery" class="qris-img" onerror="this.style.display='none'; document.getElementById('qris-placeholder').style.display='flex';">
                    <div id="qris-placeholder" class="qris-placeholder" style="display:none;">
                        <i class="bi bi-qr-code" style="font-size:3rem; color:var(--muted);"></i>
                        <p style="margin:.5rem 0 0; font-size:.85rem; color:var(--muted);">Gambar QRIS belum tersedia.<br><small>Letakkan file <code>qris.jpg</code> di folder <code>public/images/</code></small></p>
                    </div>
                </div>
                <div class="qris-instructions">
                    <p style="font-weight:600; font-size:.85rem; margin-bottom:.4rem;">Cara Pembayaran:</p>
                    <ol style="font-size:.82rem; color:var(--muted); padding-left:1.2rem; margin:0;">
                        <li>Buka aplikasi e-wallet atau mobile banking</li>
                        <li>Pilih menu <strong>Scan QR / QRIS</strong></li>
                        <li>Scan kode QR di atas</li>
                        <li>Masukkan jumlah sesuai total pesanan</li>
                        <li>Konfirmasi dan selesaikan pembayaran</li>
                    </ol>
                </div>
                <div class="payment-secure-note" style="margin-top:1rem;">
                    <i class="bi bi-shield-lock-fill"></i>
                    Pastikan nama merchant sesuai
                </div>
            </div>
        </div>

        {{-- Divider (hanya QRIS) --}}
        <div id="section-divider" class="payment-section" style="display:none;">
            <div class="payment-divider">
                <span>upload bukti pembayaran</span>
            </div>
        </div>

        {{-- Upload Bukti Pembayaran (hanya QRIS) --}}
        <div id="section-upload" class="payment-section" style="display:none;">
            <div class="card">
                <div class="card-header-vb">
                    <i class="bi bi-upload"></i> Upload Bukti Pembayaran
                </div>
                <p style="font-size:.85rem; color: var(--muted); margin-bottom: 1rem;">
                    Upload bukti pembayaran QRIS Anda di bawah ini.
                </p>
                <form method="POST" action="{{ route('payment.uploadProof', $order->id) }}" enctype="multipart/form-data" id="form-upload-proof">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label" for="payment_proof">File Bukti Pembayaran</label>
                        <div class="upload-zone" id="upload-zone" onclick="document.getElementById('payment_proof').click();">
                            <div class="upload-zone-icon">
                                <i class="bi bi-cloud-arrow-up"></i>
                            </div>
                            <div class="upload-zone-text">
                                <strong>Klik untuk memilih file</strong>
                                <span>atau drag & drop di sini</span>
                            </div>
                            <div class="upload-zone-formats">JPG, JPEG, PNG — Maks. 2MB</div>
                        </div>
                        <input type="file" name="payment_proof" id="payment_proof"
                               class="d-none @error('payment_proof') is-invalid @enderror"
                               accept=".jpg,.jpeg,.png">
                        @error('payment_proof')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror

                        {{-- Preview area --}}
                        <div id="upload-preview" class="upload-preview d-none">
                            <img id="preview-img" src="" alt="Preview">
                            <div class="upload-preview-info">
                                <span id="preview-name"></span>
                                <button type="button" class="btn btn-sm btn-danger" onclick="clearUpload()">
                                    <i class="bi bi-x-lg"></i> Hapus
                                </button>
                            </div>
                        </div>

                        {{-- Success message after upload --}}
                        <div id="upload-success-msg" class="upload-success-msg" style="display:none;">
                            <i class="bi bi-check-circle-fill"></i> Bukti pembayaran berhasil dipilih.
                        </div>
                    </div>

                    @if($order->payment_proof)
                        <div class="mb-3">
                            <label class="form-label" style="font-size: .85rem;">Bukti yang sudah diupload:</label>
                            <div class="uploaded-proof">
                                <img src="{{ asset('storage/' . $order->payment_proof) }}"
                                     alt="Bukti Pembayaran">
                                <span class="badge-status badge-menunggu" style="font-size: .7rem;">
                                    <i class="bi bi-clock me-1"></i>Menunggu Verifikasi
                                </span>
                            </div>
                        </div>
                    @endif

                    <button type="submit" class="btn btn-primary w-100" id="btn-upload-proof">
                        <i class="bi bi-cloud-upload me-2"></i>Upload Bukti Pembayaran
                    </button>
                </form>
            </div>
        </div>

        {{-- Tombol Buat Pesanan --}}
        <div id="section-place-order" class="payment-section" style="display:none; margin-top:1rem;">
            <button type="button" class="btn btn-primary w-100 btn-place-order" id="btn-place-order" onclick="placeOrder()">
                <i class="bi bi-check-circle me-2"></i>Buat Pesanan
            </button>
        </div>
    </div>

    {{-- Kolom Kanan: Ringkasan --}}
    <div class="col-lg-5">
        {{-- Info Pesanan --}}
        <div class="card mb-3">
            <div class="card-header-vb">
                <i class="bi bi-info-circle"></i> Informasi Pesanan
            </div>
            <div class="order-info-row">
                <span class="text-muted">No. Pesanan</span>
                <span class="fw-600">#{{ $order->id }}</span>
            </div>
            <div class="order-info-row">
                <span class="text-muted">Metode Pengiriman</span>
                <span class="fw-600">
                    @if($order->delivery_method === 'self_pickup')
                        <i class="bi bi-shop"></i> Ambil Sendiri
                    @else
                        <i class="bi bi-truck"></i> Diantar
                    @endif
                </span>
            </div>
            <div class="order-info-row">
                <span class="text-muted">Nomor Telepon</span>
                <span class="fw-600">{{ $order->customer_phone }}</span>
            </div>
            @if($order->delivery_method === 'delivery' && $order->delivery_date)
                <div class="order-info-row">
                    <span class="text-muted">Tanggal Pengiriman</span>
                    <span class="fw-600">{{ \Carbon\Carbon::parse($order->delivery_date)->format('d M Y') }}</span>
                </div>
            @endif
            @if($order->delivery_method === 'self_pickup' && $order->pickup_date)
                <div class="order-info-row">
                    <span class="text-muted">Tanggal Pengambilan</span>
                    <span class="fw-600">{{ \Carbon\Carbon::parse($order->pickup_date)->format('d M Y') }}</span>
                </div>
            @endif
            @if($order->note)
                <div class="order-info-row">
                    <span class="text-muted">Catatan</span>
                    <span class="fw-600" style="max-width: 200px; text-align: right;">{{ $order->note }}</span>
                </div>
            @endif
            <div class="order-info-row" style="border-bottom: none;">
                <span class="text-muted">Status Pembayaran</span>
                @php
                    $pStatus = $transaction->payment_status ?? 'pending';
                    $pBadge = match($pStatus) {
                        'paid' => 'badge-selesai',
                        'failed' => 'badge-dibatalkan',
                        default => 'badge-menunggu',
                    };
                    $pLabel = match($pStatus) {
                        'paid' => 'Lunas',
                        'failed' => 'Gagal',
                        default => 'Menunggu Konfirmasi',
                    };
                @endphp
                <span class="badge-status {{ $pBadge }}">
                    {{ $pLabel }}
                </span>
            </div>
        </div>

        {{-- Ringkasan Produk --}}
        <div class="card">
            <div class="card-header-vb">
                <i class="bi bi-receipt"></i> Ringkasan Pembayaran
            </div>

            @foreach($order->items as $item)
                <div class="payment-item-row">
                    @if($item->product && $item->product->image && file_exists(public_path('storage/'.$item->product->image)))
                        <img src="{{ asset('storage/'.$item->product->image) }}"
                             alt="{{ $item->product->name ?? 'Produk' }}"
                             class="payment-item-img">
                    @else
                        <div class="payment-item-img-placeholder">
                            <i class="bi bi-image" style="color:var(--orange-dk); font-size:1rem;"></i>
                        </div>
                    @endif
                    <div class="payment-item-info">
                        <div class="payment-item-name">
                            {{ $item->product->name ?? '—' }}
                        </div>
                        <div class="payment-item-qty">
                            {{ $item->quantity }}x @ Rp {{ number_format($item->price, 0, ',', '.') }}
                        </div>
                    </div>
                    <div class="payment-item-subtotal">
                        Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                    </div>
                </div>
            @endforeach

            <div class="payment-summary-line">
                <span>Subtotal</span>
                <span>Rp {{ number_format($order->total_amount - $order->shipping_cost, 0, ',', '.') }}</span>
            </div>
            <div class="payment-summary-line">
                <span>Ongkos Kirim</span>
                <span>
                    @if($order->shipping_cost > 0)
                        Rp {{ number_format($order->shipping_cost, 0, ',', '.') }}
                    @else
                        <span style="color: #22C55E; font-weight: 600;">Gratis</span>
                    @endif
                </span>
            </div>
            <div class="payment-total-line">
                <span>Total Pembayaran</span>
                <span class="payment-total-amount">
                    Rp {{ number_format($order->total_amount, 0, ',', '.') }}
                </span>
            </div>
        </div>
    </div>
</div>

</div>

{{-- Modal Sukses Pesanan --}}
<div id="orderSuccessOverlay" class="order-success-overlay" style="display:none;">
    <div class="order-success-modal">
        <div class="success-checkmark">
            <div class="success-checkmark-circle">
                <i class="bi bi-check-lg"></i>
            </div>
        </div>
        <h4 class="order-success-title">Pesanan Berhasil Dibuat</h4>
        <p class="order-success-desc">Terima kasih, pesanan Anda berhasil dibuat dan sedang diproses.</p>
        <div class="order-success-detail" id="orderSuccessDetail">
            <div class="order-detail-row"><span>No. Pesanan</span><span id="detail-order-id" class="fw-600">#{{ $order->id }}</span></div>
            <div class="order-detail-row"><span>Metode Pembayaran</span><span id="detail-payment-method" class="fw-600">—</span></div>
            <div class="order-detail-row"><span>Status Pembayaran</span><span id="detail-payment-status" class="fw-600">—</span></div>
            <div class="order-detail-row"><span>Total Pembayaran</span><span id="detail-total" class="fw-600" style="color:var(--orange);">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span></div>
            <div class="order-detail-row" style="border:none;"><span>Tanggal Pesanan</span><span id="detail-date" class="fw-600">{{ $order->created_at->format('d M Y') }}</span></div>
        </div>
        <p class="order-success-info" id="orderSuccessInfo"></p>
        <button type="button" class="btn btn-primary w-100 btn-done" onclick="window.location.href='{{ route('home') }}';">Done</button>
    </div>
</div>

{{-- Styles khusus halaman pembayaran --}}
<style>
    .payment-method-badge {
        display: inline-flex;
        align-items: center;
        gap: .5rem;
        background: var(--blue-lt);
        color: var(--blue-dk);
        font-weight: 600;
        font-size: .8rem;
        padding: .4rem .9rem;
        border-radius: 50px;
        margin-bottom: 1rem;
        border: 1px solid rgba(110, 201, 227, .3);
    }
    .payment-secure-note {
        margin-top: 12px;
        font-size: 0.8rem;
        color: var(--muted);
        text-align: center;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: .4rem;
    }
    .payment-secure-note i {
        color: #22C55E;
    }
    .payment-info-box {
        display: flex;
        gap: 1rem;
        align-items: flex-start;
        padding: 1rem;
        background: var(--blue-lt);
        border-radius: var(--radius-sm);
        border: 1px solid rgba(110, 201, 227, .3);
    }
    .payment-divider {
        display: flex;
        align-items: center;
        gap: .75rem;
        margin: 1.25rem 0;
        color: var(--muted);
        font-size: .82rem;
    }
    .payment-divider::before,
    .payment-divider::after {
        content: '';
        flex: 1;
        height: 1px;
        background: var(--border);
    }
    .upload-zone {
        border: 2px dashed var(--border);
        border-radius: var(--radius-sm);
        padding: 2rem 1.5rem;
        text-align: center;
        cursor: pointer;
        transition: border-color .2s, background .2s;
        background: #FAFAF8;
    }
    .upload-zone:hover {
        border-color: var(--orange);
        background: var(--orange-lt);
    }
    .upload-zone-icon {
        font-size: 2.5rem;
        color: var(--orange);
        margin-bottom: .5rem;
    }
    .upload-zone-text {
        display: flex;
        flex-direction: column;
        gap: .15rem;
        font-size: .9rem;
    }
    .upload-zone-text span {
        font-size: .8rem;
        color: var(--muted);
    }
    .upload-zone-formats {
        font-size: .72rem;
        color: var(--muted);
        margin-top: .5rem;
        letter-spacing: .3px;
    }
    .upload-preview {
        margin-top: .75rem;
        padding: .75rem;
        border: 1.5px solid var(--border);
        border-radius: var(--radius-sm);
        background: var(--card);
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    .upload-preview img {
        width: 64px;
        height: 64px;
        object-fit: cover;
        border-radius: 8px;
        border: 1px solid var(--border);
    }
    .upload-preview-info {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .75rem;
    }
    .upload-preview-info span {
        font-size: .82rem;
        font-weight: 500;
        word-break: break-all;
    }
    .uploaded-proof {
        display: flex;
        flex-direction: column;
        gap: .5rem;
        align-items: flex-start;
    }
    .uploaded-proof img {
        max-width: 200px;
        border-radius: 8px;
        border: 1px solid var(--border);
    }
    .order-info-row {
        display: flex;
        justify-content: space-between;
        padding: .6rem 0;
        border-bottom: 1px solid var(--border);
        font-size: 0.9rem;
    }
    .payment-item-row {
        display: flex;
        align-items: center;
        gap: .75rem;
        margin-bottom: .75rem;
        padding-bottom: .75rem;
        border-bottom: 1px solid var(--border);
    }
    .payment-item-img {
        width: 48px;
        height: 48px;
        object-fit: cover;
        border-radius: 8px;
        flex-shrink: 0;
        border: 1px solid var(--border);
    }
    .payment-item-img-placeholder {
        width: 48px;
        height: 48px;
        background: var(--yellow-lt);
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .payment-item-info {
        flex: 1;
        min-width: 0;
    }
    .payment-item-name {
        font-weight: 600;
        font-size: .85rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .payment-item-qty {
        font-size: .75rem;
        color: var(--muted);
    }
    .payment-item-subtotal {
        font-weight: 600;
        font-size: .85rem;
        white-space: nowrap;
        color: var(--orange);
    }
    .payment-summary-line {
        display: flex;
        justify-content: space-between;
        padding: .5rem 0;
        font-size: 0.875rem;
        border-bottom: 1px solid var(--border);
    }
    .payment-total-line {
        display: flex;
        justify-content: space-between;
        padding: .75rem 0 .25rem;
    }
    .payment-total-line span:first-child {
        font-weight: 700;
        font-family: var(--font-heading);
        font-size: 1rem;
    }
    .payment-total-amount {
        font-weight: 800;
        color: var(--orange);
        font-family: var(--font-heading);
        font-size: 1.15rem;
    }
    /* Success checkmark animation */
    .success-checkmark {
        margin-bottom: 1.25rem;
    }
    .success-checkmark-circle {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: linear-gradient(135deg, #22C55E 0%, #16A34A 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto;
        animation: successPop 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        box-shadow: 0 6px 20px rgba(34, 197, 94, 0.35);
    }
    .success-checkmark-circle i {
        font-size: 2.5rem;
        color: #fff;
    }
    @keyframes successPop {
        0% { transform: scale(0); opacity: 0; }
        100% { transform: scale(1); opacity: 1; }
    }
    /* ── Payment Method Options ── */
    .payment-method-options { display: flex; gap: .75rem; }
    .payment-method-btn {
        flex: 1; display: flex; align-items: center; justify-content: center; gap: .5rem;
        padding: .7rem 1rem; border-radius: var(--radius-xs); font-weight: 600;
        font-size: .9rem; font-family: var(--font-body); cursor: pointer;
        border: 2px solid var(--orange); background: #fff; color: var(--orange);
        transition: all .2s ease;
    }
    .payment-method-btn:hover { background: var(--orange-lt); }
    .payment-method-btn.active {
        background: var(--orange); color: #fff; border-color: var(--orange);
        box-shadow: 0 3px 10px rgba(244,125,53,.3);
    }
    .payment-section {
        animation: fadeSlideIn .3s ease forwards;
    }
    @keyframes fadeSlideIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    /* ── QRIS Area ── */
    .qris-display-area {
        text-align: center; padding: 1rem; background: #FAFAF8;
        border: 1.5px solid var(--border); border-radius: var(--radius-sm); margin-bottom: 1rem;
    }
    .qris-img { max-width: 280px; width: 100%; border-radius: 8px; margin-bottom: .75rem; }
    .qris-placeholder {
        display: flex; flex-direction: column; align-items: center; justify-content: center;
        min-height: 200px; text-align: center;
    }
    .qris-instructions { padding: .75rem; background: var(--orange-lt); border-radius: var(--radius-xs); margin-bottom: .5rem; }
    /* ── Buat Pesanan Button ── */
    .btn-place-order { padding: .75rem !important; font-size: 1rem !important; font-weight: 700 !important; }
    /* ── Upload Success ── */
    .upload-success-msg {
        margin-top: .5rem; padding: .5rem .75rem; background: #D1FAE5; color: #065F46;
        border-radius: var(--radius-xs); font-size: .82rem; font-weight: 500;
        display: flex; align-items: center; gap: .4rem;
    }
    /* ── Success Modal Overlay ── */
    .order-success-overlay {
        position: fixed; inset: 0; z-index: 9999; background: rgba(0,0,0,.5);
        display: flex; align-items: center; justify-content: center; padding: 1rem;
        animation: fadeIn .25s ease;
    }
    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
    .order-success-modal {
        background: #fff; border-radius: 20px; padding: 2rem 1.75rem; width: 100%;
        max-width: 420px; box-shadow: var(--shadow-lg); text-align: center;
        animation: scaleIn .3s cubic-bezier(.68,-.55,.265,1.55);
    }
    @keyframes scaleIn { from { transform: scale(.85); opacity: 0; } to { transform: scale(1); opacity: 1; } }
    .order-success-title {
        font-family: var(--font-heading); font-weight: 800; font-size: 1.3rem;
        color: var(--text); margin-bottom: .5rem;
    }
    .order-success-desc { color: var(--muted); font-size: .875rem; margin-bottom: 1.25rem; line-height: 1.6; }
    .order-success-detail {
        background: var(--orange-lt); border: 1px solid rgba(244,125,53,.15);
        border-radius: var(--radius-sm); padding: .75rem 1rem; margin-bottom: 1rem; text-align: left;
    }
    .order-detail-row {
        display: flex; justify-content: space-between; padding: .4rem 0;
        border-bottom: 1px solid rgba(244,125,53,.1); font-size: .82rem;
    }
    .order-detail-row span:first-child { color: var(--muted); }
    .order-success-info { font-size: .8rem; color: var(--muted); font-style: italic; margin-bottom: 1rem; }
    .btn-done { height: 46px; font-size: .95rem !important; font-weight: 700 !important; }
    @media (max-width: 576px) {
        .payment-method-options { flex-direction: column; }
        .order-success-modal { padding: 1.5rem 1.25rem; }
    }
</style>

{{-- JavaScript --}}
<script>
    var selectedMethod = null;
    var proofUploaded = {{ $order->payment_proof ? 'true' : 'false' }};

    function selectPaymentMethod(method) {
        selectedMethod = method;
        document.getElementById('alert-no-method').style.display = 'none';

        // Toggle button active state
        document.getElementById('btn-method-onsite').classList.toggle('active', method === 'onsite');
        document.getElementById('btn-method-qris').classList.toggle('active', method === 'qris');

        // Toggle sections
        var qrisMidtrans = document.getElementById('section-qris-midtrans');
        var divider = document.getElementById('section-divider');
        var upload = document.getElementById('section-upload');
        var placeOrder = document.getElementById('section-place-order');

        if (method === 'onsite') {
            qrisMidtrans.style.display = 'none';
            divider.style.display = 'none';
            upload.style.display = 'none';
            placeOrder.style.display = 'block';
        } else {
            qrisMidtrans.style.display = 'block';
            divider.style.display = 'block';
            upload.style.display = 'block';
            placeOrder.style.display = proofUploaded ? 'block' : 'none';
        }
    }

    function placeOrder() {
        if (!selectedMethod) {
            document.getElementById('alert-no-method').style.display = 'block';
            return;
        }
        if (selectedMethod === 'qris' && !proofUploaded) {
            alert('Silakan upload bukti pembayaran terlebih dahulu.');
            return;
        }

        var btn = document.getElementById('btn-place-order');
        btn.disabled = true;
        btn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Memproses...';

        var url = selectedMethod === 'onsite'
            ? '{{ route("payment.placeOnsite", $order->id) }}'
            : '{{ route("payment.placeQris", $order->id) }}';

        fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
            body: JSON.stringify({})
        })
        .then(function(r) { return r.json().then(function(d) { return { ok: r.ok, data: d }; }); })
        .then(function(res) {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-check-circle me-2"></i>Buat Pesanan';
            if (!res.ok) {
                alert(res.data.message || 'Terjadi kesalahan.');
                return;
            }
            // Fill modal
            var o = res.data.order;
            if (o) {
                document.getElementById('detail-order-id').textContent = '#' + o.id;
                document.getElementById('detail-payment-method').textContent = o.payment_method;
                document.getElementById('detail-payment-status').textContent = o.payment_status;
                if (o.total_amount !== undefined) {
                    document.getElementById('detail-total').textContent = 'Rp ' + Number(o.total_amount).toLocaleString('id-ID');
                }
                if (o.created_at) document.getElementById('detail-date').textContent = o.created_at;
            }
            // Info text
            var info = document.getElementById('orderSuccessInfo');
            if (selectedMethod === 'onsite') {
                info.textContent = 'Silakan lakukan pembayaran langsung saat pengambilan pesanan.';
            } else {
                info.textContent = 'Bukti pembayaran Anda telah dikirim dan menunggu konfirmasi admin.';
            }
            document.getElementById('orderSuccessOverlay').style.display = 'flex';
        })
        .catch(function() {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-check-circle me-2"></i>Buat Pesanan';
            alert('Terjadi kesalahan jaringan. Silakan coba lagi.');
        });
    }

    // Upload preview with validation
    document.getElementById('payment_proof').addEventListener('change', function(e) {
        var file = e.target.files[0];
        if (!file) return;
        // Validate type
        var allowed = ['image/jpeg', 'image/jpg', 'image/png'];
        if (allowed.indexOf(file.type) === -1) {
            alert('Format file tidak didukung. Gunakan JPG, JPEG, atau PNG.');
            this.value = '';
            return;
        }
        // Validate size
        if (file.size > 2 * 1024 * 1024) {
            alert('Ukuran file maksimal 2MB.');
            this.value = '';
            return;
        }
        var reader = new FileReader();
        reader.onload = function(ev) {
            document.getElementById('preview-img').src = ev.target.result;
            document.getElementById('preview-name').textContent = file.name;
            document.getElementById('upload-preview').classList.remove('d-none');
            document.getElementById('upload-zone').style.display = 'none';
            document.getElementById('upload-success-msg').style.display = 'flex';
        };
        reader.readAsDataURL(file);
    });

    function clearUpload() {
        document.getElementById('payment_proof').value = '';
        document.getElementById('upload-preview').classList.add('d-none');
        document.getElementById('upload-zone').style.display = 'block';
        document.getElementById('upload-success-msg').style.display = 'none';
    }

    // AJAX upload form
    document.getElementById('form-upload-proof').addEventListener('submit', function(e) {
        e.preventDefault();
        var form = this;
        var btn = document.getElementById('btn-upload-proof');
        var formData = new FormData(form);

        btn.disabled = true;
        btn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Mengupload...';

        fetch(form.action, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
            body: formData
        })
        .then(function(r) { return r.json().then(function(d) { return { ok: r.ok, data: d }; }); })
        .then(function(res) {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-cloud-upload me-2"></i>Upload Bukti Pembayaran';
            if (res.ok) {
                proofUploaded = true;
                document.getElementById('upload-success-msg').style.display = 'flex';
                document.getElementById('upload-success-msg').innerHTML = '<i class="bi bi-check-circle-fill"></i> Bukti pembayaran berhasil diupload.';
                if (selectedMethod === 'qris') {
                    document.getElementById('section-place-order').style.display = 'block';
                }
            } else {
                alert(res.data.message || 'Upload gagal.');
            }
        })
        .catch(function() {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-cloud-upload me-2"></i>Upload Bukti Pembayaran';
            alert('Upload gagal. Silakan coba lagi.');
        });
    });

    // Drag and drop
    var zone = document.getElementById('upload-zone');
    if (zone) {
        zone.addEventListener('dragover', function(e) {
            e.preventDefault();
            zone.style.borderColor = 'var(--orange)';
            zone.style.background = 'var(--orange-lt)';
        });
        zone.addEventListener('dragleave', function(e) {
            e.preventDefault();
            zone.style.borderColor = 'var(--border)';
            zone.style.background = '#FAFAF8';
        });
        zone.addEventListener('drop', function(e) {
            e.preventDefault();
            zone.style.borderColor = 'var(--border)';
            zone.style.background = '#FAFAF8';
            var files = e.dataTransfer.files;
            if (files.length > 0) {
                var input = document.getElementById('payment_proof');
                input.files = files;
                input.dispatchEvent(new Event('change'));
            }
        });
    }
</script>

@endsection
