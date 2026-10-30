@extends('layouts.app')
@section('title', 'Riwayat Pesanan — Viera Bakery')
@section('content')
<div class="container py-4">

<h2 class="section-title mb-4">Riwayat Pesanan</h2>

@if($orders->isEmpty())
    <div class="card text-center py-5">
        <div style="font-size:3rem; margin-bottom:.75rem;">📦</div>
        <h5 style="font-family: var(--font-heading);">Belum ada pesanan</h5>
        <p class="text-muted mb-3">Ayo pesan kue favoritmu sekarang!</p>
        <div>
            <a href="{{ route('products.index') }}" class="btn btn-primary">
                <i class="bi bi-grid me-2"></i>Lihat Katalog
            </a>
        </div>
    </div>
@else
    <div class="card p-0">
        <div class="table-responsive">
            <table class="table-vb w-100">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Tanggal & Jam</th>
                        <th>Produk</th>
                        <th class="text-center">Pengiriman</th>
                        <th class="text-center">Metode Pembayaran</th>
                        <th class="text-center">Status Pesanan</th>
                        <th class="text-end">Total</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($orders as $order)
                    <tr>
                        <td class="text-muted" style="font-size:.82rem;">#{{ $order->id }}</td>
                        <td style="font-size:.82rem; white-space:nowrap;">
                            {{ $order->created_at->timezone('Asia/Jakarta')->format('d/m/Y H:i') }}
                        </td>
                        <td style="font-size:.82rem;">
                            @if($order->items->isEmpty())
                                <span class="text-muted">—</span>
                            @else
                                @foreach($order->items->take(2) as $item)
                                    <div>{{ $item->product->name ?? '—' }} ({{ $item->quantity }}x)</div>
                                @endforeach
                                @if($order->items->count() > 2)
                                    <div class="text-muted">+{{ $order->items->count() - 2 }} produk lainnya</div>
                                @endif
                            @endif
                        </td>
                        <td class="text-center">
                            @if($order->delivery_method === 'self_pickup')
                                <span class="badge-status badge-siap-ambil" style="font-size:.7rem;">
                                    <i class="bi bi-shop me-1"></i>Ambil Sendiri
                                </span>
                            @else
                                <span class="badge-status badge-perjalanan" style="font-size:.7rem;">
                                    <i class="bi bi-truck me-1"></i>Diantar
                                </span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($order->payment_method === 'onsite')
                                <span class="badge-status badge-siap-ambil" style="font-size:.7rem;">
                                    <i class="bi bi-cash-coin me-1"></i>Onsite / COD
                                </span>
                            @elseif($order->payment_method === 'qris')
                                <span class="badge-status badge-menunggu" style="font-size:.7rem; background:var(--blue-lt); color:var(--blue-dk); border-color:rgba(110,201,227,.3);">
                                    <i class="bi bi-qr-code-scan me-1"></i>QRIS
                                </span>
                            @elseif($order->payment_method === 'midtrans')
                                <span class="badge-status badge-diproses" style="font-size:.7rem;">
                                    <i class="bi bi-credit-card me-1"></i>Midtrans
                                </span>
                            @elseif($order->payment_method === 'transfer_manual')
                                <span class="badge-status badge-menunggu" style="font-size:.7rem;">
                                    <i class="bi bi-bank me-1"></i>Transfer Manual
                                </span>
                            @else
                                <span class="text-muted" style="font-size:.78rem;">—</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @php
                                $statusClass = match($order->status) {
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
                            <span class="badge-status {{ $statusClass }}" style="font-size:.7rem;">
                                {{ $order->status }}
                            </span>
                        </td>
                        <td class="text-end fw-600" style="color: var(--orange); font-size:.875rem; white-space:nowrap;">
                            @if($order->total_amount > 0)
                                Rp {{ number_format($order->total_amount, 0, ',', '.') }}
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($order->transaction)
                                <a href="{{ route('transactions.show', $order->transaction) }}"
                                   class="btn btn-primary btn-sm">
                                    <i class="bi bi-eye me-1"></i>Detail
                                </a>
                            @else
                                <span class="text-muted" style="font-size:.78rem;">—</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-4">{{ $orders->links() }}</div>
@endif

</div>
@endsection
