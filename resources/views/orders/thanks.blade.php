@extends('layouts.app')
@section('title', 'Pesanan Berhasil — Viera Bakery')
@section('content')
<div class="container py-4">

<div class="text-center py-4">
    <div style="font-size:4.5rem; margin-bottom:1rem;">🎉</div>
    <h2 style="font-family: var(--font-heading); font-weight:800; color: var(--orange);">
        Pesanan Berhasil Dibuat!
    </h2>
    <p class="text-muted mb-4" style="max-width:460px; margin:0 auto;">
        Terima kasih! Pesanan Anda sudah kami terima. Cek chat untuk notifikasi dan perkembangan pesanan.
    </p>

    <div class="card mx-auto mb-4" style="max-width:460px; text-align:left;">
        <div class="card-header-vb">
            <i class="bi bi-bag-check"></i> Detail Pesanan
        </div>
        <table class="table-vb w-100">
            <tr>
                <td class="text-muted" style="width:45%;">ID Pesanan</td>
                <td class="fw-600">#{{ $order->id }}</td>
            </tr>
            <tr>
                <td class="text-muted">Metode</td>
                <td>
                    @if($order->delivery_method === 'self_pickup')
                        <span class="badge-status badge-siap-ambil">🏪 Ambil Sendiri</span>
                    @else
                        <span class="badge-status badge-perjalanan">🚚 Delivery</span>
                    @endif
                </td>
            </tr>
            <tr>
                <td class="text-muted">Nomor Telepon</td>
                <td class="fw-600">{{ $order->customer_phone }}</td>
            </tr>
            @if($order->delivery_date)
            <tr>
                <td class="text-muted">Tanggal Kirim</td>
                <td class="fw-600">{{ \Carbon\Carbon::parse($order->delivery_date)->format('d F Y') }}</td>
            </tr>
            @endif
            @if($order->delivery_address)
            <tr>
                <td class="text-muted">Alamat</td>
                <td>{{ $order->delivery_address }}</td>
            </tr>
            @endif
            <tr>
                <td class="text-muted">Status Pesanan</td>
                <td><span class="badge-status badge-menunggu">{{ $order->status }}</span></td>
            </tr>
            @if($order->transaction)
            <tr>
                <td class="text-muted">Status Pembayaran</td>
                <td>
                    @php
                        $status = $order->transaction->payment_status;
                        $badgeClass = $status === 'paid' ? 'badge-selesai' : ($status === 'failed' ? 'badge-dibatalkan' : 'badge-menunggu');
                    @endphp
                    <span class="badge-status {{ $badgeClass }}">{{ ucfirst(str_replace('_', ' ', $status)) }}</span>
                </td>
            </tr>
            @endif
            <tr>
                <td class="text-muted">Total</td>
                <td class="fw-700" style="color: var(--orange);">
                    Rp {{ number_format($order->total_amount, 0, ',', '.') }}
                </td>
            </tr>
        </table>
    </div>

    <div class="d-flex justify-content-center gap-3 flex-wrap">
        <a href="{{ route('orders.history') }}" class="btn btn-primary">
            <i class="bi bi-clock-history me-2"></i>Riwayat Pesanan
        </a>
        <a href="{{ route('chat.index') }}" class="btn btn-secondary">
            <i class="bi bi-chat-dots me-2"></i>Lihat Chat
        </a>
        <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-grid me-1"></i>Pesan Lagi
        </a>
    </div>
</div>

</div>
@endsection
