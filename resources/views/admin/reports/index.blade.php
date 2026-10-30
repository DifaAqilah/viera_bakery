@extends('layouts.admin')
@section('title', 'Laporan Penjualan — Admin Viera Bakery')
@section('page-title', 'Laporan Penjualan')
@section('content')

{{-- ═══════════ A. FILTER LAPORAN ═══════════ --}}
<div class="card mb-4">
    <div class="card-header-vb"><i class="bi bi-funnel"></i> Filter Laporan</div>
    <form method="GET" action="{{ route('admin.reports.index') }}" class="row g-2 align-items-end">
        <div class="col-md-2">
            <label class="form-label mb-1" style="font-size:.8rem;">Dari Tanggal</label>
            <input type="date" name="date_from" class="form-control form-control-sm" value="{{ $dateFrom }}">
        </div>
        <div class="col-md-2">
            <label class="form-label mb-1" style="font-size:.8rem;">Sampai Tanggal</label>
            <input type="date" name="date_to" class="form-control form-control-sm" value="{{ $dateTo }}">
        </div>
        <div class="col-md-2">
            <label class="form-label mb-1" style="font-size:.8rem;">Status Pembayaran</label>
            <select name="payment_status" class="form-select form-select-sm">
                <option value="">Semua</option>
                <option value="paid" {{ $filterPayment === 'paid' ? 'selected' : '' }}>Lunas</option>
                <option value="pending" {{ $filterPayment === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="failed" {{ $filterPayment === 'failed' ? 'selected' : '' }}>Gagal</option>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label mb-1" style="font-size:.8rem;">Status Pesanan</label>
            <select name="order_status" class="form-select form-select-sm">
                <option value="">Semua</option>
                @foreach(['Menunggu Konfirmasi','Menunggu Diproses','Diproses','Siap Ambil','Dalam Perjalanan','Selesai','Dibatalkan'] as $s)
                    <option value="{{ $s }}" {{ $filterOrder === $s ? 'selected' : '' }}>{{ $s }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label mb-1" style="font-size:.8rem;">Metode Pemesanan</label>
            <select name="delivery_method" class="form-select form-select-sm">
                <option value="">Semua</option>
                <option value="self_pickup" {{ $filterMethod === 'self_pickup' ? 'selected' : '' }}>Pickup</option>
                <option value="delivery" {{ $filterMethod === 'delivery' ? 'selected' : '' }}>Delivery</option>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label mb-1" style="font-size:.8rem;">Metode Pembayaran</label>
            <select name="payment_method" class="form-select form-select-sm">
                <option value="">Semua</option>
                <option value="qris" {{ $filterPayMethod === 'qris' ? 'selected' : '' }}>QRIS</option>
                <option value="bank_transfer" {{ $filterPayMethod === 'bank_transfer' ? 'selected' : '' }}>Transfer Manual</option>
                <option value="onsite" {{ $filterPayMethod === 'onsite' ? 'selected' : '' }}>Onsite</option>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label mb-1" style="font-size:.8rem;">Nama Pelanggan</label>
            <input type="text" name="customer" class="form-control form-control-sm" placeholder="Cari nama..." value="{{ $filterCustomer }}">
        </div>
        <div class="col-md-3 d-flex gap-2">
            <button type="submit" class="btn btn-primary btn-sm">
                <i class="bi bi-search me-1"></i>Filter
            </button>
            <a href="{{ route('admin.reports.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
        </div>
    </form>
</div>

{{-- ═══════════ B. CARD RINGKASAN ═══════════ --}}
{{-- Baris 1: 5 card statistik operasional --}}
<div class="row g-3 mb-3">
    @php
        $cardsRow1 = [
            ['Transaksi Lunas', $transactionsCount, 'bi-receipt', 'orange'],
            ['Total Pesanan', $totalOrders, 'bi-bag', 'blue'],
            ['Pesanan Pending', $pendingOrders, 'bi-clock-history', 'yellow'],
            ['Pesanan Dibatalkan', $cancelledOrders, 'bi-x-circle', 'red'],
            ['Produk Terjual', $productsSold, 'bi-box-seam', 'teal'],
        ];
    @endphp
    @foreach($cardsRow1 as $card)
        <div class="col-6 col-md-4 col-lg">
            <div class="stat-card">
                <div class="stat-icon {{ $card[3] }}"><i class="bi {{ $card[2] }}"></i></div>
                <div>
                    <div class="stat-value" style="font-size:1.1rem;">{{ $card[1] }}</div>
                    <div class="stat-label">{{ $card[0] }}</div>
                </div>
            </div>
        </div>
    @endforeach
</div>

{{-- Baris 2: Total Pendapatan + Rata-rata Transaksi --}}
<div class="row g-3 mb-4">
    <div class="col-12 col-md-6">
        <div class="stat-card">
            <div class="stat-icon green"><i class="bi bi-cash-stack"></i></div>
            <div>
                <div class="stat-value" style="font-size:1.15rem;">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</div>
                <div class="stat-label">Total Pendapatan</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6">
        <div class="stat-card">
            <div class="stat-icon purple"><i class="bi bi-graph-up"></i></div>
            <div>
                <div class="stat-value" style="font-size:1.15rem;">Rp {{ number_format($avgTransaction, 0, ',', '.') }}</div>
                <div class="stat-label">Rata-rata Transaksi</div>
            </div>
        </div>
    </div>
</div>

{{-- ═══════════ C. GRAFIK PENJUALAN ═══════════ --}}
{{-- Baris 1: Grafik besar --}}
<div class="row g-3 mb-4">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header-vb"><i class="bi bi-graph-up"></i> Pendapatan per Hari (14 Hari)</div>
            <div style="height:280px; padding:.5rem;">
                <canvas id="chartRevenue"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header-vb"><i class="bi bi-bar-chart"></i> Jumlah Pesanan per Hari</div>
            <div style="height:280px; padding:.5rem;">
                <canvas id="chartOrders"></canvas>
            </div>
        </div>
    </div>
</div>

{{-- Baris 2: 3 card sama besar --}}
<div class="row g-3 mb-4">
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header-vb"><i class="bi bi-truck"></i> Metode Pemesanan</div>
            <div style="height:240px; padding:.5rem; display:flex; align-items:center; justify-content:center;">
                <canvas id="chartMethod"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header-vb"><i class="bi bi-credit-card"></i> Status Pembayaran</div>
            <div style="height:240px; padding:.5rem; display:flex; align-items:center; justify-content:center;">
                <canvas id="chartPayment"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header-vb"><i class="bi bi-file-earmark-excel"></i> Export Laporan Penjualan</div>
            <div style="padding:1.5rem; text-align:center; display:flex; flex-direction:column; align-items:center; justify-content:center; min-height:240px;">
                <div style="width:56px; height:56px; background:var(--green-lt, #D1FAE5); border-radius:12px; display:flex; align-items:center; justify-content:center; margin-bottom:1rem;">
                    <i class="bi bi-file-earmark-spreadsheet" style="font-size:1.6rem; color:#059669;"></i>
                </div>
                <p style="font-size:.82rem; color:var(--muted); margin-bottom:1.2rem; max-width:220px;">
                    Unduh laporan penjualan lengkap dalam format Excel (.xlsx)
                </p>
                <div class="text-center">
                    @if($transactionsCount > 0)
                        <a href="{{ route('admin.reports.export', ['date_from' => $dateFrom, 'date_to' => $dateTo]) }}"
                           class="btn btn-primary btn-sm">
                            <i class="bi bi-file-earmark-excel me-1"></i>Download Excel
                        </a>
                    @else
                        <button type="button" class="btn btn-secondary btn-sm" onclick="alert('Tidak ada data laporan untuk diexport.')">
                            <i class="bi bi-file-earmark-excel me-1"></i>Download Excel
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>



{{-- ═══════════ D. PRODUK TERLARIS ═══════════ --}}
@if($topProducts->isNotEmpty())
<div class="card mb-4 p-0">
    <div class="card-header-vb px-4 pt-4 pb-0 border-0 mb-3">
        <i class="bi bi-trophy"></i> Produk Terlaris
    </div>
    <div class="table-responsive">
        <table class="table-vb w-100">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nama Produk</th>
                    <th>Kategori</th>
                    <th class="text-center">Jumlah Terjual</th>
                    <th class="text-end">Total Pendapatan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($topProducts as $i => $tp)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            @if($tp->product && $tp->product->image && file_exists(public_path('storage/'.$tp->product->image)))
                                <img src="{{ asset('storage/'.$tp->product->image) }}"
                                     style="width:32px; height:32px; object-fit:cover; border-radius:6px; border:1px solid var(--border);">
                            @else
                                <div style="width:32px; height:32px; background:var(--yellow-lt); border-radius:6px; display:flex; align-items:center; justify-content:center; font-size:.7rem;">
                                    <i class="bi bi-image" style="color:var(--orange-dk);"></i>
                                </div>
                            @endif
                            <span style="font-size:.875rem; font-weight:600;">{{ $tp->product->name ?? '—' }}</span>
                        </div>
                    </td>
                    <td style="font-size:.82rem;">{{ $tp->product->category->name ?? '—' }}</td>
                    <td class="text-center fw-600">{{ number_format($tp->total_sold) }}</td>
                    <td class="text-end fw-700" style="color:var(--orange); white-space:nowrap;">
                        Rp {{ number_format($tp->total_revenue, 0, ',', '.') }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- ═══════════ E. RINCIAN TRANSAKSI LUNAS ═══════════ --}}
<div class="card p-0">
    <div class="card-header-vb px-4 pt-4 pb-0 border-0 mb-3">
        <i class="bi bi-bar-chart-line"></i> Rincian Transaksi Lunas
    </div>
    <div class="table-responsive">
        <table class="table-vb w-100">
            <thead>
                <tr>
                    <th>Kode Transaksi</th>
                    <th>Tanggal Bayar</th>
                    <th>Pelanggan</th>
                    <th class="text-center">Status Pesanan</th>
                    <th class="text-center">Metode Pesanan</th>
                    <th class="text-center">Metode Bayar</th>
                    <th class="text-end">Ongkir</th>
                    <th class="text-end">Jumlah</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions as $tx)
                <tr>
                    <td class="fw-600" style="font-size:.875rem; font-family: var(--font-heading);">
                        {{ $tx->transaction_code }}
                    </td>
                    <td style="font-size:.82rem; white-space:nowrap; color: var(--muted);">
                        {{ $tx->paid_at ? \Carbon\Carbon::parse($tx->paid_at)->timezone('Asia/Jakarta')->format('d/m/Y H:i') : '—' }}
                    </td>
                    <td style="font-size:.875rem;">{{ $tx->order->user->name ?? '—' }}</td>
                    <td class="text-center">
                        @php
                            $cls = match($tx->order->status ?? '') {
                                'Selesai' => 'badge-selesai',
                                'Diproses' => 'badge-diproses',
                                'Dibatalkan' => 'badge-dibatalkan',
                                default => 'badge-menunggu',
                            };
                        @endphp
                        <span class="badge-status {{ $cls }}" style="font-size:.7rem;">
                            {{ $tx->order->status ?? '—' }}
                        </span>
                    </td>
                    <td class="text-center" style="font-size:.82rem;">
                        {{ $tx->order->delivery_method === 'self_pickup' ? 'Pickup' : 'Delivery' }}
                    </td>
                    <td class="text-center" style="font-size:.82rem;">
                        {{ ucfirst(str_replace('_', ' ', $tx->payment_method ?? '—')) }}
                    </td>
                    <td class="text-end" style="font-size:.82rem; white-space:nowrap;">
                        Rp {{ number_format($tx->order->shipping_cost ?? 0, 0, ',', '.') }}
                    </td>
                    <td class="text-end fw-700" style="color: var(--orange); white-space:nowrap;">
                        Rp {{ number_format($tx->amount, 0, ',', '.') }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center text-muted py-5">
                        Tidak ada data transaksi lunas
                        @if($dateFrom || $dateTo) untuk periode yang dipilih @endif
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($transactions->hasPages())
        <div class="px-4 py-3">{{ $transactions->links() }}</div>
    @endif
</div>

{{-- ═══════════ CHART.JS ═══════════ --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script>
    var chartLabels = @json($chartLabels);

    // Revenue chart
    new Chart(document.getElementById('chartRevenue'), {
        type: 'line',
        data: {
            labels: chartLabels,
            datasets: [{
                label: 'Pendapatan (Rp)',
                data: @json($chartRevenue),
                borderColor: '#E67E22',
                backgroundColor: 'rgba(230,126,34,.12)',
                fill: true,
                tension: .3,
                pointRadius: 3,
                pointBackgroundColor: '#E67E22'
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, ticks: { callback: function(v) { return 'Rp ' + v.toLocaleString('id-ID'); } } }
            }
        }
    });

    // Orders chart
    new Chart(document.getElementById('chartOrders'), {
        type: 'bar',
        data: {
            labels: chartLabels,
            datasets: [{
                label: 'Jumlah Pesanan',
                data: @json($chartOrders),
                backgroundColor: 'rgba(59,130,246,.65)',
                borderRadius: 4
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
        }
    });

    // Method chart
    new Chart(document.getElementById('chartMethod'), {
        type: 'doughnut',
        data: {
            labels: ['Pickup', 'Delivery'],
            datasets: [{
                data: [{{ $pickupCount }}, {{ $deliveryCount }}],
                backgroundColor: ['#22C55E', '#3B82F6'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom', labels: { padding: 12, usePointStyle: true } } }
        }
    });

    // Payment status chart
    new Chart(document.getElementById('chartPayment'), {
        type: 'doughnut',
        data: {
            labels: ['Lunas', 'Pending', 'Gagal'],
            datasets: [{
                data: [{{ $paymentPaid }}, {{ $paymentPending }}, {{ $paymentFailed }}],
                backgroundColor: ['#22C55E', '#F59E0B', '#EF4444'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom', labels: { padding: 12, usePointStyle: true } } }
        }
    });
</script>

<style>
    .stat-icon.red { background: #FEE2E2; color: #EF4444; }
    .stat-icon.teal { background: #D1FAE5; color: #059669; }
    .stat-icon.purple { background: #EDE9FE; color: #7C3AED; }
    .stat-icon.yellow { background: #FEF3C7; color: #D97706; }
</style>

@endsection
