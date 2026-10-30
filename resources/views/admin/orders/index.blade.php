@extends('layouts.admin')
@section('title', 'Pesanan — Admin Viera Bakery')
@section('page-title', 'Manajemen Pesanan')
@section('content')

<div class="card p-0">
    <div class="d-flex align-items-center justify-content-between px-4 pt-4 pb-3 flex-wrap gap-2">
        <div class="card-header-vb mb-0 border-0 pb-0">
            <i class="bi bi-bag-check"></i> Daftar Pesanan
        </div>
        <span class="text-muted" style="font-size:.82rem;">Total: {{ $orders->total() }}</span>
    </div>
    <div class="table-responsive">
        <table class="table-vb w-100">
            <thead>
                <tr>
                    <th>#ID</th>
                    <th>Pelanggan</th>
                    <th>Tanggal</th>
                    <th class="text-center">Metode</th>
                    <th class="text-center">Status</th>
                    <th class="text-center">Pembayaran</th>
                    <th class="text-end">Ongkir</th>
                    <th class="text-end">Total</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                <tr>
                    <td class="text-muted" style="font-size:.82rem;">#{{ $order->id }}</td>
                    <td>
                        <div class="fw-600" style="font-size:.875rem;">{{ $order->user->name ?? '—' }}</div>
                        <div style="font-size:.75rem; color: var(--muted);">{{ $order->user->email ?? '' }}</div>
                    </td>
                    <td style="font-size:.82rem; white-space:nowrap; color: var(--muted);">
                        {{ $order->created_at->format('d/m/Y H:i') }}
                    </td>
                    <td class="text-center">
                        @if($order->delivery_method === 'self_pickup')
                            <span class="badge-status badge-siap-ambil" style="font-size:.7rem;">
                                <i class="bi bi-shop me-1"></i>Pickup
                            </span>
                        @else
                            <span class="badge-status badge-perjalanan" style="font-size:.7rem;">
                                <i class="bi bi-truck me-1"></i>Delivery
                            </span>
                        @endif
                    </td>
                    <td class="text-center">
                        @php
                            $cls = match($order->status) {
                                'Menunggu Konfirmasi' => 'badge-menunggu',
                                'Diproses'           => 'badge-diproses',
                                'Siap Ambil'         => 'badge-siap-ambil',
                                'Dalam Perjalanan'   => 'badge-perjalanan',
                                'Selesai'            => 'badge-selesai',
                                'Dibatalkan'         => 'badge-dibatalkan',
                                default              => 'badge-menunggu',
                            };
                        @endphp
                        <span class="badge-status {{ $cls }}" style="font-size:.7rem;">{{ $order->status }}</span>
                    </td>
                    <td class="text-center">
                        @if($order->transaction)
                            @php
                                $pCls = match($order->transaction->payment_status) {
                                    'paid' => 'badge-selesai',
                                    'failed' => 'badge-dibatalkan',
                                    default => 'badge-menunggu',
                                };
                            @endphp
                            <span class="badge-status {{ $pCls }}" style="font-size:.7rem;">
                                {{ ucfirst($order->transaction->payment_status) }}
                            </span>
                        @else
                            <span class="text-muted" style="font-size:.78rem;">—</span>
                        @endif
                    </td>
                    <td class="text-end" style="font-size:.82rem; white-space:nowrap;">
                        Rp {{ number_format($order->shipping_cost ?? 0, 0, ',', '.') }}
                    </td>
                    <td class="text-end fw-600" style="color: var(--orange); font-size:.875rem; white-space:nowrap;">
                        Rp {{ number_format($order->total_amount, 0, ',', '.') }}
                    </td>
                    <td class="text-center">
                        <a href="{{ route('admin.orders.show', $order) }}"
                           class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-eye"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center text-muted py-5">Belum ada pesanan</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($orders->hasPages())
        <div class="px-4 py-3">{{ $orders->links() }}</div>
    @endif
</div>

@endsection
