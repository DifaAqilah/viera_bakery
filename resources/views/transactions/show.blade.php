@extends('layouts.app')
@section('title', 'Detail Transaksi — Viera Bakery')
@section('content')
<div class="container py-4">

<div class="mb-3">
    @if(request('from') === 'chat')
        <a href="{{ route('chat.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Kembali ke Chat
        </a>
    @else
        <a href="{{ route('orders.history') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Kembali ke Riwayat
        </a>
    @endif
</div>

<h2 class="section-title mb-4">Detail Transaksi</h2>

<div class="row g-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header-vb"><i class="bi bi-credit-card"></i> Informasi Transaksi</div>
            <table class="table-vb w-100">
                <tr>
                    <td class="text-muted" style="width:45%;">Kode Transaksi</td>
                    <td class="fw-700" style="font-family: var(--font-heading);">
                        {{ $transaction->transaction_code }}
                    </td>
                </tr>
                <tr>
                    <td class="text-muted">Tanggal</td>
                    <td>{{ $transaction->created_at->format('d F Y, H:i') }}</td>
                </tr>
                <tr>
                    <td class="text-muted">Metode Bayar</td>
                    <td class="text-capitalize">{{ $transaction->payment_method }}</td>
                </tr>
                <tr>
                    <td class="text-muted">Status Pembayaran</td>
                    <td>
                        @if($transaction->payment_status === 'paid')
                            <span class="badge-status badge-selesai"><i class="bi bi-check-circle me-1"></i>Lunas</span>
                        @elseif($transaction->payment_status === 'pending')
                            <span class="badge-status badge-menunggu"><i class="bi bi-clock me-1"></i>Menunggu</span>
                        @elseif($transaction->payment_status === 'failed')
                            <span class="badge-status badge-dibatalkan"><i class="bi bi-x-circle me-1"></i>Gagal</span>
                        @else
                            <span class="badge-status badge-dibatalkan">{{ $transaction->payment_status }}</span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <td class="text-muted">Total Bayar</td>
                    <td class="fw-800" style="color: var(--orange); font-size:1.1rem; font-family: var(--font-heading);">
                        Rp {{ number_format($transaction->amount, 0, ',', '.') }}
                    </td>
                </tr>
                @if($transaction->paid_at)
                <tr>
                    <td class="text-muted">Dibayar Pada</td>
                    <td>{{ \Carbon\Carbon::parse($transaction->paid_at)->format('d F Y, H:i') }}</td>
                </tr>
                @endif
            </table>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header-vb"><i class="bi bi-bag"></i> Detail Pesanan #{{ $transaction->order->id }}</div>
            <table class="table-vb w-100">
                <tr>
                    <td class="text-muted" style="width:45%;">Status Pesanan</td>
                    <td>
                        @php
                            $cls = match($transaction->order->status) {
                                'Menunggu Konfirmasi' => 'badge-menunggu',
                                'Menunggu Diproses'   => 'badge-menunggu',
                                'Diproses'           => 'badge-diproses',
                                'Siap Ambil'         => 'badge-siap-ambil',
                                'Dalam Perjalanan'   => 'badge-perjalanan',
                                'Selesai'            => 'badge-selesai',
                                'Dibatalkan'         => 'badge-dibatalkan',
                                default              => 'badge-menunggu',
                            };
                        @endphp
                        <span class="badge-status {{ $cls }}">{{ $transaction->order->status }}</span>
                    </td>
                </tr>
                <tr>
                    <td class="text-muted">Metode</td>
                    <td>{{ $transaction->order->delivery_method === 'self_pickup' ? '🏪 Ambil Sendiri' : '🚚 Delivery' }}</td>
                </tr>
                @if($transaction->order->delivery_address)
                <tr>
                    <td class="text-muted">Alamat</td>
                    <td>{{ $transaction->order->delivery_address }}</td>
                </tr>
                @endif
                @if($transaction->order->note)
                <tr>
                    <td class="text-muted">Catatan</td>
                    <td>{{ $transaction->order->note }}</td>
                </tr>
                @endif
            </table>
            <hr style="border-color: var(--border); margin: .75rem 0;">
            <div class="px-2">
                <div class="fw-600 mb-2" style="font-size:.85rem;">Item yang dipesan:</div>
                @php $subtotalItems = 0; @endphp
                @foreach($transaction->order->items as $item)
                    @php $subtotalItems += $item->subtotal; @endphp
                    <div class="d-flex justify-content-between mb-1" style="font-size:.85rem;">
                        <span>{{ $item->product->name ?? '—' }} ×{{ $item->quantity }}</span>
                        <span class="fw-600">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                    </div>
                @endforeach
                <hr style="border-color: var(--border); margin: .5rem 0;">
                <div class="d-flex justify-content-between mb-1" style="font-size:.85rem;">
                    <span class="text-muted">Subtotal</span>
                    <span>Rp {{ number_format($subtotalItems, 0, ',', '.') }}</span>
                </div>
                <div class="d-flex justify-content-between mb-1" style="font-size:.85rem;">
                    <span class="text-muted">Ongkos Kirim</span>
                    @if($transaction->order->delivery_method === 'delivery' && $transaction->order->shipping_cost > 0)
                        <span>Rp {{ number_format($transaction->order->shipping_cost, 0, ',', '.') }}</span>
                    @else
                        <span style="color:#22C55E; font-weight:600;">Rp 0</span>
                    @endif
                </div>
                <div class="d-flex justify-content-between" style="font-size:.95rem;">
                    <span class="fw-700">Total</span>
                    <span class="fw-800" style="color:var(--orange); font-family:var(--font-heading);">Rp {{ number_format($transaction->order->total_amount, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        {{-- ═══ Info Pembatalan (jika dibatalkan) ═══════════════════════ --}}
        @if($transaction->order->status === 'Dibatalkan' && $transaction->order->cancel_reason)
        <div class="card mt-3" style="border: 1px solid rgba(220, 53, 69, 0.25); background: rgba(220, 53, 69, 0.03);">
            <div class="card-header-vb" style="color: #DC3545;"><i class="bi bi-x-octagon"></i> Pesanan Dibatalkan</div>
            <table class="table-vb w-100">
                <tr>
                    <td class="text-muted" style="width:40%;">Alasan</td>
                    <td style="font-size:.875rem;">{{ $transaction->order->cancel_reason }}</td>
                </tr>
                <tr>
                    <td class="text-muted">Waktu Batal</td>
                    <td style="font-size:.875rem;">{{ $transaction->order->cancelled_at ? $transaction->order->cancelled_at->timezone('Asia/Jakarta')->format('d M Y, H:i') : '—' }}</td>
                </tr>
                <tr>
                    <td class="text-muted">Dibatalkan Oleh</td>
                    <td><span class="badge-status badge-dibatalkan" style="font-size:.72rem;">{{ ucfirst($transaction->order->cancelled_by ?? 'customer') }}</span></td>
                </tr>
            </table>
        </div>
        @endif

        {{-- ═══ Tombol Batalkan Pesanan ═══════════════════════════════════ --}}
        @php
            $order = $transaction->order;
            $canCancel = false;
            $qrisPaidMessage = false;
            $cancellableStatuses = ['Menunggu Konfirmasi', 'Menunggu Diproses'];

            if (in_array($order->status, $cancellableStatuses)) {
                if ($order->payment_method === 'qris') {
                    if ($order->payment_proof || $transaction->payment_status === 'paid' || $order->payment_status === 'Menunggu Konfirmasi') {
                        $qrisPaidMessage = true;
                    } else {
                        $canCancel = true;
                    }
                } elseif ($transaction->payment_status !== 'paid') {
                    $canCancel = true;
                }
            }
        @endphp

        @if($canCancel)
        <div class="card mt-3" style="border: 1px solid rgba(220, 53, 69, 0.3);">
            <div class="p-3 text-center">
                <button type="button" class="btn btn-sm w-100" id="cancelOrderBtn"
                        style="background: linear-gradient(135deg, #DC3545, #C82333); color: #fff;
                               border: none; border-radius: 8px; padding: .6rem 1.5rem;
                               font-weight: 600; font-size: .875rem;
                               transition: all .2s ease;">
                    <i class="bi bi-x-circle me-2"></i>Batalkan Pesanan
                </button>
            </div>
        </div>

        {{-- Modal Konfirmasi Batal dengan Form Alasan --}}
        <div class="modal fade" id="cancelModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content" style="border-radius: 16px; border: none; overflow: hidden;">
                    <div class="modal-body p-4">
                        <div class="text-center mb-3">
                            <div style="font-size: 2.5rem; margin-bottom: .5rem;">⚠️</div>
                            <h5 class="fw-700 mb-1" style="font-family: var(--font-heading);">Batalkan Pesanan?</h5>
                            <p class="text-muted mb-0" style="font-size: .85rem;">
                                Tindakan ini tidak dapat dibatalkan. Silakan pilih alasan pembatalan.
                            </p>
                        </div>

                        <form method="POST" action="{{ route('transactions.cancel', $transaction) }}{{ request('from') === 'chat' ? '?from=chat' : '' }}" id="cancelForm">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label fw-600" style="font-size:.85rem;">Alasan Pembatalan <span class="text-danger">*</span></label>
                                @php
                                    $cancelReasons = [
                                        'Salah memilih produk',
                                        'Salah memilih metode pengiriman',
                                        'Ingin mengubah alamat pengiriman',
                                        'Ingin mengubah metode pembayaran',
                                        'Pesanan dibuat dua kali',
                                        'Tidak jadi membeli',
                                        'Lainnya',
                                    ];
                                @endphp
                                <div class="d-flex flex-column gap-2" id="cancelReasonGroup">
                                    @foreach($cancelReasons as $r)
                                    <label class="cancel-reason-option d-flex align-items-center gap-2 p-2"
                                           style="border: 1px solid var(--border); border-radius: 8px; cursor: pointer; transition: all .15s ease; font-size: .85rem;">
                                        <input type="radio" name="cancel_reason" value="{{ $r }}" class="form-check-input mt-0" style="flex-shrink:0;">
                                        <span>{{ $r }}</span>
                                    </label>
                                    @endforeach
                                </div>
                                <div id="cancelReasonError" class="text-danger mt-1" style="font-size:.8rem; display:none;">
                                    Silakan pilih alasan pembatalan.
                                </div>
                            </div>

                            <div class="mb-3" id="cancelReasonOtherWrap" style="display:none;">
                                <label class="form-label fw-600" style="font-size:.85rem;">Alasan Lainnya <span class="text-danger">*</span></label>
                                <textarea name="cancel_reason_other" id="cancelReasonOther" class="form-control" rows="3"
                                          maxlength="255" placeholder="Tuliskan alasan pembatalan Anda..."
                                          style="font-size:.85rem; border-radius:8px;"></textarea>
                                <div class="d-flex justify-content-between mt-1">
                                    <div id="cancelOtherError" class="text-danger" style="font-size:.8rem; display:none;">
                                        Silakan isi alasan pembatalan.
                                    </div>
                                    <div class="text-muted ms-auto" style="font-size:.75rem;">
                                        <span id="charCount">0</span>/255
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex gap-2 justify-content-end mt-3">
                                <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal"
                                        style="border-radius: 8px;">
                                    Kembali
                                </button>
                                <button type="submit" class="btn px-4" id="confirmCancelBtn"
                                        style="background: linear-gradient(135deg, #DC3545, #C82333); color: #fff;
                                               border: none; border-radius: 8px; font-weight: 600;">
                                    <i class="bi bi-x-circle me-1"></i>Batalkan Pesanan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            var btn = document.getElementById('cancelOrderBtn');
            var modal = new bootstrap.Modal(document.getElementById('cancelModal'));
            btn.addEventListener('click', function() { modal.show(); });

            // Show/hide "Lainnya" textarea
            var radios = document.querySelectorAll('input[name="cancel_reason"]');
            var otherWrap = document.getElementById('cancelReasonOtherWrap');
            var otherInput = document.getElementById('cancelReasonOther');
            var charCount = document.getElementById('charCount');

            radios.forEach(function(r) {
                r.addEventListener('change', function() {
                    document.getElementById('cancelReasonError').style.display = 'none';
                    if (this.value === 'Lainnya') {
                        otherWrap.style.display = 'block';
                    } else {
                        otherWrap.style.display = 'none';
                        otherInput.value = '';
                    }
                    // Highlight selected option
                    document.querySelectorAll('.cancel-reason-option').forEach(function(opt) {
                        opt.style.borderColor = 'var(--border)';
                        opt.style.background = 'transparent';
                    });
                    this.closest('.cancel-reason-option').style.borderColor = '#DC3545';
                    this.closest('.cancel-reason-option').style.background = 'rgba(220,53,69,.04)';
                });
            });

            // Character counter
            otherInput.addEventListener('input', function() {
                charCount.textContent = this.value.length;
                document.getElementById('cancelOtherError').style.display = 'none';
            });

            // Form validation
            document.getElementById('cancelForm').addEventListener('submit', function(e) {
                var selected = document.querySelector('input[name="cancel_reason"]:checked');
                if (!selected) {
                    e.preventDefault();
                    document.getElementById('cancelReasonError').style.display = 'block';
                    return;
                }
                if (selected.value === 'Lainnya' && otherInput.value.trim() === '') {
                    e.preventDefault();
                    document.getElementById('cancelOtherError').style.display = 'block';
                    return;
                }
            });
        });
        </script>
        @endif

        @if($qrisPaidMessage)
        <div class="card mt-3" style="border: 1px solid rgba(255, 193, 7, 0.4);">
            <div class="p-3">
                <div class="d-flex align-items-start gap-2">
                    <i class="bi bi-info-circle" style="color: var(--orange); font-size: 1.2rem; flex-shrink: 0; margin-top: 2px;"></i>
                    <div style="font-size: .85rem; color: var(--text-secondary);">
                        Pesanan yang sudah dibayar melalui QRIS tidak dapat dibatalkan.
                        Silakan hubungi admin jika membutuhkan bantuan.
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

</div>
@endsection
