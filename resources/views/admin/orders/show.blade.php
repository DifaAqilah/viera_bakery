@extends('layouts.admin')
@section('title', 'Detail Pesanan #' . $order->id . ' — Admin')
@section('page-title', 'Detail Pesanan #' . $order->id)
@section('content')

<div class="mb-3">
    @if(request('from') === 'chat' && request('chat_id'))
        <a href="{{ route('admin.chat.show', request('chat_id')) }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Kembali ke Chat
        </a>
    @else
        <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Kembali
        </a>
    @endif
</div>

<div class="row g-4">
    {{-- Item Pesanan --}}
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header-vb">
                <i class="bi bi-bag"></i> Item Pesanan
            </div>
            <div class="table-responsive">
                <table class="table-vb w-100">
                    <thead>
                        <tr>
                            <th>Produk</th>
                            <th class="text-center">Harga</th>
                            <th class="text-center">Qty</th>
                            <th class="text-end">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->items as $item)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    @if($item->product && $item->product->image && file_exists(public_path('storage/'.$item->product->image)))
                                        <img src="{{ asset('storage/'.$item->product->image) }}"
                                             alt="{{ $item->product->name }}"
                                             style="width:40px; height:40px; object-fit:cover;
                                                    border-radius:6px; flex-shrink:0; border: 1px solid var(--border);">
                                    @else
                                        <div style="width:40px; height:40px; background: var(--yellow-lt);
                                                    border-radius:6px; display:flex; align-items:center;
                                                    justify-content:center; flex-shrink:0;">
                                            <i class="bi bi-image" style="color:var(--orange-dk); font-size:.85rem;"></i>
                                        </div>
                                    @endif
                                    <span style="font-size:.875rem;">{{ $item->product->name ?? '—' }}</span>
                                </div>
                            </td>
                            <td class="text-center" style="font-size:.875rem; white-space:nowrap;">
                                Rp {{ number_format($item->price, 0, ',', '.') }}
                            </td>
                            <td class="text-center">{{ $item->quantity }}</td>
                            <td class="text-end fw-600" style="white-space:nowrap;">
                                Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        @php $subtotal = $order->total_amount - ($order->shipping_cost ?? 0); @endphp
                        <tr>
                            <td colspan="3" class="text-end" style="font-size:.85rem;">Subtotal Produk</td>
                            <td class="text-end fw-600" style="font-size:.9rem;">
                                Rp {{ number_format($subtotal, 0, ',', '.') }}
                            </td>
                        </tr>
                        <tr>
                            <td colspan="3" class="text-end" style="font-size:.85rem;">Ongkos Kirim</td>
                            <td class="text-end" style="font-size:.9rem;">
                                Rp {{ number_format($order->shipping_cost ?? 0, 0, ',', '.') }}
                            </td>
                        </tr>
                        <tr>
                            <td colspan="3" class="text-end fw-700">Total</td>
                            <td class="text-end fw-800" style="color: var(--orange); font-size:1rem;">
                                Rp {{ number_format($order->total_amount, 0, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        @if($order->note)
        <div class="card mt-3">
            <div class="card-header-vb"><i class="bi bi-sticky"></i> Catatan Pelanggan</div>
            <p class="mb-0" style="font-size:.9rem;">{{ $order->note }}</p>
        </div>
        @endif

        {{-- Bukti Pembayaran --}}
        @if($order->payment_proof)
        <div class="card mt-3">
            <div class="card-header-vb"><i class="bi bi-image"></i> Bukti Pembayaran</div>
            <div class="text-center">
                <a href="{{ asset('storage/' . $order->payment_proof) }}" target="_blank">
                    <img src="{{ asset('storage/' . $order->payment_proof) }}"
                         alt="Bukti Pembayaran"
                         style="max-width: 100%; max-height: 400px; border-radius: 8px; border: 1px solid var(--border);">
                </a>
                <div class="mt-2" style="font-size:.8rem; color: var(--muted);">
                    Klik gambar untuk melihat ukuran penuh
                </div>
            </div>
        </div>
        @endif
    </div>

    {{-- Info & Update Status --}}
    <div class="col-lg-5">
        <div class="card mb-3">
            <div class="card-header-vb"><i class="bi bi-info-circle"></i> Informasi Pesanan</div>

            {{-- Data Pelanggan --}}
            <div class="info-section-label">
                <i class="bi bi-person me-1"></i>Data Pelanggan
            </div>
            <table class="table-vb w-100 mb-0">
                <tr>
                    <td class="text-muted" style="width:42%;">Pelanggan</td>
                    <td class="fw-600">{{ $order->user->name ?? '—' }}</td>
                </tr>
                <tr>
                    <td class="text-muted">Email</td>
                    <td style="font-size:.82rem;">{{ $order->user->email ?? '—' }}</td>
                </tr>
                <tr>
                    <td class="text-muted">Nomor Telepon</td>
                    <td class="fw-600">{{ $order->customer_phone ?? '—' }}</td>
                </tr>
            </table>

            {{-- Detail Pesanan --}}
            <div class="info-section-label" style="margin-top:.5rem;">
                <i class="bi bi-box-seam me-1"></i>Detail Pesanan
            </div>
            <table class="table-vb w-100 mb-0">
                <tr>
                    <td class="text-muted" style="width:42%;">Tanggal Pesan</td>
                    <td style="font-size:.82rem;">{{ $order->created_at->timezone('Asia/Jakarta')->format('d M Y, H:i') }}</td>
                </tr>
                <tr>
                    <td class="text-muted">Metode</td>
                    <td>
                        @if($order->delivery_method === 'self_pickup')
                            <span class="badge-status badge-siap-ambil" style="font-size:.72rem;">🏪 Ambil Sendiri</span>
                        @else
                            <span class="badge-status badge-perjalanan" style="font-size:.72rem;">🚚 Delivery</span>
                        @endif
                    </td>
                </tr>
                @if($order->delivery_address)
                <tr>
                    <td class="text-muted">Alamat</td>
                    <td style="font-size:.82rem;">{{ $order->delivery_address }}</td>
                </tr>
                @endif
                @if($order->delivery_method === 'delivery' && $order->delivery_date)
                <tr>
                    <td class="text-muted">Tanggal Kirim</td>
                    <td style="font-size:.82rem;">{{ \Carbon\Carbon::parse($order->delivery_date)->format('d M Y') }}</td>
                </tr>
                @endif
                @if($order->delivery_method === 'self_pickup' && $order->pickup_date)
                <tr>
                    <td class="text-muted">Tanggal Ambil</td>
                    <td style="font-size:.82rem;">{{ \Carbon\Carbon::parse($order->pickup_date)->format('d M Y') }}</td>
                </tr>
                @endif
                <tr>
                    <td class="text-muted">Ongkos Kirim</td>
                    <td class="fw-600">
                        @if($order->delivery_method === 'delivery' && ($order->shipping_cost ?? 0) > 0)
                            Rp {{ number_format($order->shipping_cost, 0, ',', '.') }}
                        @else
                            <span style="color:#22C55E;">Rp 0</span>
                        @endif
                    </td>
                </tr>
            </table>

            {{-- Status Pesanan --}}
            <div class="info-section-label" style="margin-top:.5rem;">
                <i class="bi bi-clipboard-check me-1"></i>Status Pesanan
            </div>
            <table class="table-vb w-100 mb-0">
                <tr>
                    <td class="text-muted" style="width:42%;">Status Pesanan</td>
                    <td>
                        @php
                            $cls = match($order->status) {
                                'Menunggu Konfirmasi' => 'badge-menunggu',
                                'Menunggu Diproses'   => 'badge-menunggu',
                                'Diproses'            => 'badge-diproses',
                                'Siap Ambil'          => 'badge-siap-ambil',
                                'Dalam Perjalanan'    => 'badge-perjalanan',
                                'Selesai'             => 'badge-selesai',
                                'Dibatalkan'          => 'badge-dibatalkan',
                                default               => 'badge-menunggu',
                            };
                        @endphp
                        <span class="badge-status {{ $cls }}">{{ $order->status }}</span>
                    </td>
                </tr>
                <tr>
                    <td class="text-muted">Status Pembayaran</td>
                    <td>
                        @if($order->transaction)
                            @php
                                $pCls = match($order->transaction->payment_status) {
                                    'paid' => 'badge-selesai',
                                    'failed' => 'badge-dibatalkan',
                                    default => 'badge-menunggu',
                                };
                            @endphp
                            <span class="badge-status {{ $pCls }}">
                                {{ ucfirst(str_replace('_', ' ', $order->transaction->payment_status)) }}
                            </span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                </tr>
                @if($order->transaction && $order->transaction->payment_method)
                <tr>
                    <td class="text-muted">Metode Bayar</td>
                    <td style="font-size:.82rem;">{{ ucfirst(str_replace('_', ' ', $order->transaction->payment_method)) }}</td>
                </tr>
                @endif
                <tr>
                    <td class="text-muted">Bukti Bayar</td>
                    <td>
                        @if($order->payment_proof)
                            <span class="badge-status badge-selesai" style="font-size:.72rem;">
                                <i class="bi bi-check-circle me-1"></i>Sudah Upload
                            </span>
                        @else
                            <span class="badge-status badge-menunggu" style="font-size:.72rem;">
                                <i class="bi bi-clock me-1"></i>Belum Upload
                            </span>
                        @endif
                    </td>
                </tr>
            </table>
        </div>

        {{-- Informasi Pembatalan (jika pesanan dibatalkan) --}}
        @if($order->status === 'Dibatalkan')
        <div class="card mb-3" style="border: 1px solid rgba(220, 53, 69, 0.25); background: rgba(220, 53, 69, 0.03);">
            <div class="card-header-vb" style="color: #DC3545;">
                <i class="bi bi-x-octagon"></i> Informasi Pembatalan
            </div>
            <table class="table-vb w-100 mb-0">
                <tr>
                    <td class="text-muted" style="width:42%;">Status Pesanan</td>
                    <td><span class="badge-status badge-dibatalkan">Dibatalkan</span></td>
                </tr>
                <tr>
                    <td class="text-muted">Status Pembayaran</td>
                    <td>
                        @if($order->transaction && $order->transaction->payment_status === 'failed')
                            <span class="badge-status badge-dibatalkan" style="font-size:.72rem;">
                                <i class="bi bi-x-circle me-1"></i>Gagal
                            </span>
                        @else
                            <span class="badge-status badge-menunggu" style="font-size:.72rem;">
                                {{ ucfirst($order->transaction->payment_status ?? '—') }}
                            </span>
                        @endif
                    </td>
                </tr>
                @if($order->cancel_reason)
                <tr>
                    <td class="text-muted">Alasan Pembatalan</td>
                    <td style="font-size:.85rem;">{{ $order->cancel_reason }}</td>
                </tr>
                @endif
                @if($order->cancelled_at)
                <tr>
                    <td class="text-muted">Waktu Pembatalan</td>
                    <td style="font-size:.82rem;">{{ $order->cancelled_at->timezone('Asia/Jakarta')->format('d M Y, H:i') }}</td>
                </tr>
                @endif
                <tr>
                    <td class="text-muted">Dibatalkan Oleh</td>
                    <td>
                        @if($order->cancelled_by === 'customer')
                            <span class="badge-status badge-menunggu" style="font-size:.72rem;">
                                <i class="bi bi-person me-1"></i>Pelanggan
                            </span>
                        @elseif($order->cancelled_by === 'admin')
                            <span class="badge-status badge-diproses" style="font-size:.72rem;">
                                <i class="bi bi-shield me-1"></i>Admin
                            </span>
                        @else
                            <span class="text-muted" style="font-size:.82rem;">—</span>
                        @endif
                    </td>
                </tr>
            </table>
        </div>
        @endif

        {{-- Form Update Status Pesanan --}}
        <div class="card mb-3">
            <div class="card-header-vb"><i class="bi bi-pencil-square"></i> Update Status Pesanan</div>
            <form method="POST" action="{{ route('admin.orders.updateStatus', $order) }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Status Baru</label>
                    <select name="status" class="form-select">
                        @foreach(['Menunggu Konfirmasi','Menunggu Diproses','Diproses','Siap Ambil','Dalam Perjalanan','Selesai','Dibatalkan'] as $s)
                            <option value="{{ $s }}" {{ $order->status === $s ? 'selected' : '' }}>
                                {{ $s }}
                            </option>
                        @endforeach
                    </select>
                    <div class="form-text">
                        "Siap Ambil" → untuk pickup &nbsp;|&nbsp; "Dalam Perjalanan" → untuk delivery
                    </div>
                </div>
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-check-circle me-2"></i>Perbarui Status Pesanan
                </button>
            </form>
        </div>

        {{-- Form Update Status Pembayaran --}}
        @if($order->transaction)
        <div class="card">
            <div class="card-header-vb"><i class="bi bi-credit-card-2-front"></i> Update Status Pembayaran</div>
            <form method="POST" action="{{ route('admin.orders.updatePaymentStatus', $order) }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Status Pembayaran</label>
                    <select name="payment_status" class="form-select">
                        @foreach(['pending' => 'Pending', 'paid' => 'Paid (Lunas)', 'failed' => 'Failed (Gagal)'] as $val => $label)
                            <option value="{{ $val }}" {{ $order->transaction->payment_status === $val ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    <div class="form-text">
                        Mengubah ke "Paid" akan otomatis menandai transaksi sebagai lunas.
                    </div>
                </div>
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-check-circle me-2"></i>Perbarui Status Pembayaran
                </button>
            </form>
        </div>
        @endif
    </div>
</div>

<style>
    .info-section-label {
        font-size: .75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .5px;
        color: var(--orange);
        padding: .5rem .75rem .25rem;
        margin-top: .25rem;
        border-top: 1px solid var(--border);
    }
    .info-section-label:first-of-type {
        border-top: none;
        margin-top: 0;
    }
</style>

@endsection
