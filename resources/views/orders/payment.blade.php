@extends('layouts.app')
@section('title', 'Pembayaran Pesanan — Viera Bakery')
@section('content')
<div class="container py-4">

<div class="mb-3">
    <a href="{{ route('checkout') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Kembali ke Checkout
    </a>
</div>

<h2 class="section-title mb-4">Pembayaran Pesanan</h2>

<div class="row g-4">
    {{-- Kolom Kiri: Pembayaran --}}
    <div class="col-lg-7">

        {{-- Mini Progress Steps --}}
        <div class="progress-steps mb-3">
            <div class="progress-step active" id="step-1">
                <div class="step-circle">1</div>
                <div class="step-label">Pilih Metode</div>
            </div>
            <div class="step-line" id="line-1"></div>
            <div class="progress-step" id="step-2">
                <div class="step-circle">2</div>
                <div class="step-label">Konfirmasi</div>
            </div>
            <div class="step-line" id="line-2"></div>
            <div class="progress-step" id="step-3">
                <div class="step-circle">3</div>
                <div class="step-label">Buat Pesanan</div>
            </div>
        </div>

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

        {{-- Section Onsite --}}
        <div id="section-onsite" class="payment-section" style="display:none;">
            <div class="card mb-3">
                <div class="card-header-vb">
                    @if($checkout['delivery_method'] === 'self_pickup')
                        <i class="bi bi-shop"></i> Bayar di Tempat
                    @else
                        <i class="bi bi-truck"></i> Bayar saat Pesanan Diterima
                    @endif
                </div>

                <div class="onsite-icon-area">
                    @if($checkout['delivery_method'] === 'self_pickup')
                        <div class="onsite-icon"><i class="bi bi-shop"></i></div>
                    @else
                        <div class="onsite-icon"><i class="bi bi-truck"></i></div>
                        <span class="cod-badge">COD</span>
                    @endif
                </div>

                <p class="onsite-desc">
                    @if($checkout['delivery_method'] === 'self_pickup')
                        Anda dapat melakukan pembayaran langsung saat mengambil pesanan di toko.
                    @else
                        Anda dapat melakukan pembayaran langsung saat pesanan diterima di lokasi tujuan.
                    @endif
                </p>

                <div class="onsite-checklist">
                    <div class="check-item"><i class="bi bi-check-circle-fill text-success"></i> Tidak perlu upload bukti pembayaran</div>
                    <div class="check-item"><i class="bi bi-check-circle-fill text-success"></i>
                        @if($checkout['delivery_method'] === 'self_pickup')
                            Bayar langsung saat pengambilan
                        @else
                            Bayar langsung saat pesanan diterima
                        @endif
                    </div>
                    <div class="check-item"><i class="bi bi-check-circle-fill text-success"></i> Pesanan akan diproses setelah dibuat</div>
                </div>

                <div class="onsite-info-box">
                    <div class="onsite-info-row">
                        <span>Total Pembayaran</span>
                        <span class="fw-700" style="color:var(--orange);">Rp {{ number_format($checkout['total'], 0, ',', '.') }}</span>
                    </div>
                    <div class="onsite-info-row">
                        <span>{{ $checkout['delivery_method'] === 'self_pickup' ? 'Tanggal Pengambilan' : 'Tanggal Pengiriman' }}</span>
                        <span class="fw-600">
                            @php
                                $dateVal = $checkout['delivery_method'] === 'self_pickup' ? $checkout['pickup_date'] : $checkout['delivery_date'];
                            @endphp
                            {{ $dateVal ? \Carbon\Carbon::parse($dateVal)->format('d M Y') : '-' }}
                        </span>
                    </div>
                    <div class="onsite-info-row">
                        <span>Metode Pengiriman</span>
                        <span class="fw-600">{{ $checkout['delivery_method'] === 'self_pickup' ? 'Ambil Sendiri' : 'Diantar' }}</span>
                    </div>
                    <div class="onsite-info-row" style="border:none;">
                        <span>Nomor Telepon</span>
                        <span class="fw-600">{{ $checkout['customer_phone'] }}</span>
                    </div>
                </div>

                <div class="onsite-note-box">
                    <i class="bi bi-info-circle"></i>
                    Pastikan nomor telepon aktif agar admin dapat menghubungi Anda jika diperlukan.
                </div>
            </div>
        </div>

        {{-- Section QRIS --}}
        <div id="section-qris" class="payment-section" style="display:none;">
            <div class="card mb-3">
                <div class="card-header-vb">
                    <i class="bi bi-qr-code-scan"></i> Pembayaran QRIS
                </div>
                <div class="payment-method-badge">
                    <i class="bi bi-qr-code-scan"></i> Scan QRIS untuk Pembayaran
                </div>
                <p class="onsite-desc">
                    Silakan scan kode QRIS di bawah ini, lalu upload bukti pembayaran.
                </p>
                <div class="qris-display-area">
                    <img id="qris-image" src="{{ asset('images/qris.jpg') }}" alt="QRIS Viera Bakery" class="qris-img" onerror="this.style.display='none'; document.getElementById('qris-placeholder').style.display='flex';">
                    <div id="qris-placeholder" class="qris-placeholder" style="display:none;">
                        <i class="bi bi-qr-code" style="font-size:3rem; color:var(--muted);"></i>
                        <p style="margin:.5rem 0 0; font-size:.85rem; color:var(--muted);">Gambar QRIS belum tersedia.</p>
                    </div>
                </div>
                <div class="qris-instructions">
                    <p style="font-weight:600; font-size:.85rem; margin-bottom:.4rem;">Instruksi Pembayaran:</p>
                    <ol style="font-size:.82rem; color:var(--muted); padding-left:1.2rem; margin:0;">
                        <li>Scan QRIS menggunakan aplikasi pembayaran</li>
                        <li>Pastikan nominal sesuai total pembayaran</li>
                        <li>Upload bukti pembayaran</li>
                        <li>Klik tombol <strong>Buat Pesanan</strong></li>
                    </ol>
                </div>
            </div>

            {{-- Upload Bukti --}}
            <div class="card mb-3">
                <div class="card-header-vb">
                    <i class="bi bi-upload"></i> Upload Bukti Pembayaran
                </div>
                <form id="form-upload-proof" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <div class="upload-zone" id="upload-zone" onclick="document.getElementById('payment_proof').click();">
                            <div class="upload-zone-icon"><i class="bi bi-cloud-arrow-up"></i></div>
                            <div class="upload-zone-text">
                                <strong>Klik untuk memilih file</strong>
                                <span>atau drag & drop di sini</span>
                            </div>
                            <div class="upload-zone-formats">JPG, JPEG, PNG — Maks. 2MB</div>
                        </div>
                        <input type="file" name="payment_proof" id="payment_proof" class="d-none" accept=".jpg,.jpeg,.png">

                        <div id="upload-preview" class="upload-preview d-none">
                            <img id="preview-img" src="" alt="Preview">
                            <div class="upload-preview-info">
                                <span id="preview-name"></span>
                                <button type="button" class="btn btn-sm btn-danger" onclick="clearUpload()">
                                    <i class="bi bi-x-lg"></i> Hapus
                                </button>
                            </div>
                        </div>

                        <div id="upload-success-msg" class="upload-success-msg" style="display:none;">
                            <i class="bi bi-check-circle-fill"></i> <span id="upload-msg-text">Bukti pembayaran berhasil dipilih.</span>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100" id="btn-upload-proof">
                        <i class="bi bi-cloud-upload me-2"></i>Upload Bukti Pembayaran
                    </button>
                </form>
            </div>
        </div>

        {{-- Tombol Buat Pesanan --}}
        <div id="section-place-order" class="payment-section" style="display:none;">
            <button type="button" class="btn btn-primary w-100 btn-place-order" id="btn-place-order" onclick="placeOrder()">
                <i class="bi bi-check-circle me-2"></i>Buat Pesanan
            </button>
        </div>
    </div>

    {{-- Kolom Kanan --}}
    <div class="col-lg-5">
        <div class="card mb-3">
            <div class="card-header-vb"><i class="bi bi-info-circle"></i> Informasi Pesanan</div>
            <div class="order-info-row">
                <span class="text-muted">Metode Pengiriman</span>
                <span class="fw-600">
                    @if($checkout['delivery_method'] === 'self_pickup')
                        <i class="bi bi-shop"></i> Ambil Sendiri
                    @else
                        <i class="bi bi-truck"></i> Diantar
                    @endif
                </span>
            </div>
            <div class="order-info-row">
                <span class="text-muted">Nomor Telepon</span>
                <span class="fw-600">{{ $checkout['customer_phone'] }}</span>
            </div>
            @php $dateLabel = $checkout['delivery_method'] === 'self_pickup' ? 'Tanggal Pengambilan' : 'Tanggal Pengiriman'; @endphp
            @php $dateVal2 = $checkout['delivery_method'] === 'self_pickup' ? $checkout['pickup_date'] : $checkout['delivery_date']; @endphp
            @if($dateVal2)
            <div class="order-info-row">
                <span class="text-muted">{{ $dateLabel }}</span>
                <span class="fw-600">{{ \Carbon\Carbon::parse($dateVal2)->format('d M Y') }}</span>
            </div>
            @endif
            @if($checkout['note'])
            <div class="order-info-row">
                <span class="text-muted">Catatan</span>
                <span class="fw-600" style="max-width:200px; text-align:right;">{{ $checkout['note'] }}</span>
            </div>
            @endif
            @if($checkout['delivery_method'] === 'delivery' && $checkout['delivery_address'])
            <div class="order-info-row" style="border:none;">
                <span class="text-muted">Alamat</span>
                <span class="fw-600" style="max-width:200px; text-align:right; font-size:.82rem;">{{ $checkout['delivery_address'] }}</span>
            </div>
            @endif
        </div>

        <div class="card">
            <div class="card-header-vb"><i class="bi bi-receipt"></i> Ringkasan Pembayaran</div>
            @foreach($checkout['items'] as $item)
                <div class="payment-item-row">
                    @if($item['product_image'] && file_exists(public_path('storage/'.$item['product_image'])))
                        <img src="{{ asset('storage/'.$item['product_image']) }}" alt="{{ $item['product_name'] }}" class="payment-item-img">
                    @else
                        <div class="payment-item-img-placeholder">
                            <i class="bi bi-image" style="color:var(--orange-dk); font-size:1rem;"></i>
                        </div>
                    @endif
                    <div class="payment-item-info">
                        <div class="payment-item-name">{{ $item['product_name'] }}</div>
                        <div class="payment-item-qty">{{ $item['quantity'] }}x @ Rp {{ number_format($item['price'], 0, ',', '.') }}</div>
                    </div>
                    <div class="payment-item-subtotal">Rp {{ number_format($item['subtotal'], 0, ',', '.') }}</div>
                </div>
            @endforeach

            <div class="payment-summary-line">
                <span>Subtotal</span>
                <span>Rp {{ number_format($checkout['subtotal'], 0, ',', '.') }}</span>
            </div>
            <div class="payment-summary-line">
                <span>Ongkos Kirim</span>
                <span>
                    @if($checkout['shipping_cost'] > 0)
                        Rp {{ number_format($checkout['shipping_cost'], 0, ',', '.') }}
                    @else
                        <span style="color: #22C55E; font-weight: 600;">Gratis</span>
                    @endif
                </span>
            </div>
            <div class="payment-total-line">
                <span>Total Pembayaran</span>
                <span class="payment-total-amount">Rp {{ number_format($checkout['total'], 0, ',', '.') }}</span>
            </div>
        </div>
    </div>
</div>

</div>

{{-- Modal Sukses --}}
<div id="orderSuccessOverlay" class="order-success-overlay" style="display:none;">
    <div class="order-success-modal">
        <div class="success-checkmark">
            <div class="success-checkmark-circle"><i class="bi bi-check-lg"></i></div>
        </div>
        <h4 class="order-success-title">Pesanan Berhasil Dibuat</h4>
        <p class="order-success-desc">Terima kasih, pesanan Anda berhasil dibuat dan sedang diproses.</p>
        <div class="order-success-detail" id="orderSuccessDetail">
            <div class="order-detail-row"><span>No. Pesanan</span><span id="detail-order-id" class="fw-600">—</span></div>
            <div class="order-detail-row"><span>Metode Pembayaran</span><span id="detail-payment-method" class="fw-600">—</span></div>
            <div class="order-detail-row"><span>Status Pembayaran</span><span id="detail-payment-status" class="fw-600">—</span></div>
            <div class="order-detail-row"><span>Total Pembayaran</span><span id="detail-total" class="fw-600" style="color:var(--orange);">Rp {{ number_format($checkout['total'], 0, ',', '.') }}</span></div>
            <div class="order-detail-row" style="border:none;"><span>Tanggal Pesanan</span><span id="detail-date" class="fw-600">{{ now()->format('d M Y') }}</span></div>
        </div>
        <p class="order-success-info" id="orderSuccessInfo"></p>
        <button type="button" class="btn btn-primary w-100 btn-done" onclick="window.location.href='{{ route('home') }}';">Done</button>
    </div>
</div>

@include('orders._payment_styles')

<script>
    var selectedMethod = null;
    var proofUploaded = false;
    var csrfToken = '{{ csrf_token() }}';
    var deliveryMethod = '{{ $checkout["delivery_method"] }}';

    function setStep(n) {
        for (var i = 1; i <= 3; i++) {
            var s = document.getElementById('step-' + i);
            s.classList.toggle('active', i <= n);
            s.classList.toggle('completed', i < n);
        }
        for (var j = 1; j <= 2; j++) {
            document.getElementById('line-' + j).classList.toggle('active', j < n);
        }
    }

    function selectPaymentMethod(method) {
        selectedMethod = method;
        document.getElementById('alert-no-method').style.display = 'none';
        document.getElementById('btn-method-onsite').classList.toggle('active', method === 'onsite');
        document.getElementById('btn-method-qris').classList.toggle('active', method === 'qris');

        document.getElementById('section-onsite').style.display = method === 'onsite' ? 'block' : 'none';
        document.getElementById('section-qris').style.display = method === 'qris' ? 'block' : 'none';
        document.getElementById('section-place-order').style.display = 'block';

        setStep(method === 'onsite' ? 2 : (proofUploaded ? 2 : 1));
    }

    function placeOrder() {
        if (!selectedMethod) {
            document.getElementById('alert-no-method').style.display = 'block';
            window.scrollTo({top: 0, behavior: 'smooth'});
            return;
        }
        if (selectedMethod === 'qris' && !proofUploaded) {
            alert('Silakan upload bukti pembayaran terlebih dahulu.');
            return;
        }

        var btn = document.getElementById('btn-place-order');
        btn.disabled = true;
        btn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Memproses...';
        setStep(3);

        fetch('{{ route("order.finalize") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            body: JSON.stringify({ payment_method: selectedMethod, has_proof: proofUploaded })
        })
        .then(function(r) { return r.json().then(function(d) { return { ok: r.ok, data: d }; }); })
        .then(function(res) {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-check-circle me-2"></i>Buat Pesanan';
            if (!res.ok) {
                alert(res.data.message || 'Terjadi kesalahan.');
                setStep(2);
                return;
            }
            var o = res.data.order;
            if (o) {
                document.getElementById('detail-order-id').textContent = '#' + o.id;
                document.getElementById('detail-payment-method').textContent = o.payment_method;
                document.getElementById('detail-payment-status').textContent = o.payment_status;
                if (o.total_amount !== undefined) document.getElementById('detail-total').textContent = 'Rp ' + Number(o.total_amount).toLocaleString('id-ID');
                if (o.created_at) document.getElementById('detail-date').textContent = o.created_at;
            }
            var info = document.getElementById('orderSuccessInfo');
            if (selectedMethod === 'onsite') {
                if (deliveryMethod === 'self_pickup') {
                    info.textContent = 'Silakan lakukan pembayaran langsung saat pengambilan pesanan.';
                } else {
                    info.textContent = 'Silakan lakukan pembayaran saat pesanan diterima di lokasi tujuan.';
                }
            } else {
                info.textContent = 'Bukti pembayaran Anda telah dikirim dan menunggu konfirmasi admin.';
            }
            document.getElementById('orderSuccessOverlay').style.display = 'flex';
        })
        .catch(function() {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-check-circle me-2"></i>Buat Pesanan';
            alert('Terjadi kesalahan jaringan.');
            setStep(2);
        });
    }

    // Upload preview
    document.getElementById('payment_proof').addEventListener('change', function(e) {
        var file = e.target.files[0];
        if (!file) return;
        if (['image/jpeg','image/jpg','image/png'].indexOf(file.type) === -1) {
            alert('Format file tidak didukung. Gunakan JPG, JPEG, atau PNG.');
            this.value = ''; return;
        }
        if (file.size > 2 * 1024 * 1024) {
            alert('Ukuran file maksimal 2MB.');
            this.value = ''; return;
        }
        var reader = new FileReader();
        reader.onload = function(ev) {
            document.getElementById('preview-img').src = ev.target.result;
            document.getElementById('preview-name').textContent = file.name;
            document.getElementById('upload-preview').classList.remove('d-none');
            document.getElementById('upload-zone').style.display = 'none';
        };
        reader.readAsDataURL(file);
    });

    function clearUpload() {
        document.getElementById('payment_proof').value = '';
        document.getElementById('upload-preview').classList.add('d-none');
        document.getElementById('upload-zone').style.display = 'block';
        document.getElementById('upload-success-msg').style.display = 'none';
        proofUploaded = false;
    }

    // AJAX upload
    document.getElementById('form-upload-proof').addEventListener('submit', function(e) {
        e.preventDefault();
        var btn = document.getElementById('btn-upload-proof');
        var formData = new FormData(this);
        btn.disabled = true;
        btn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Mengupload...';

        fetch('{{ route("order.uploadProofSession") }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            body: formData
        })
        .then(function(r) { return r.json().then(function(d) { return { ok: r.ok, data: d }; }); })
        .then(function(res) {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-cloud-upload me-2"></i>Upload Bukti Pembayaran';
            if (res.ok) {
                proofUploaded = true;
                document.getElementById('upload-success-msg').style.display = 'flex';
                document.getElementById('upload-msg-text').textContent = 'Bukti pembayaran berhasil diupload.';
                setStep(2);
            } else {
                alert(res.data.message || 'Upload gagal.');
            }
        })
        .catch(function() {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-cloud-upload me-2"></i>Upload Bukti Pembayaran';
            alert('Upload gagal.');
        });
    });

    // Drag and drop
    var zone = document.getElementById('upload-zone');
    if (zone) {
        zone.addEventListener('dragover', function(e) { e.preventDefault(); zone.style.borderColor = 'var(--orange)'; zone.style.background = 'var(--orange-lt)'; });
        zone.addEventListener('dragleave', function(e) { e.preventDefault(); zone.style.borderColor = 'var(--border)'; zone.style.background = '#FAFAF8'; });
        zone.addEventListener('drop', function(e) {
            e.preventDefault(); zone.style.borderColor = 'var(--border)'; zone.style.background = '#FAFAF8';
            if (e.dataTransfer.files.length > 0) { var input = document.getElementById('payment_proof'); input.files = e.dataTransfer.files; input.dispatchEvent(new Event('change')); }
        });
    }
</script>
@endsection
