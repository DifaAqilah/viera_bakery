@extends('layouts.admin')
@section('title', 'Dashboard — Admin Viera Bakery')
@section('page-title', 'Dashboard')
@section('content')

{{-- ═══ Stat Cards (existing) ═══════════════════════════════════════════ --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon orange"><i class="bi bi-bag-check"></i></div>
            <div>
                <div class="stat-value">{{ $totalOrders }}</div>
                <div class="stat-label">Total Pesanan</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon yellow"><i class="bi bi-clock-history"></i></div>
            <div>
                <div class="stat-value">{{ $waitingOrders }}</div>
                <div class="stat-label">Menunggu Konfirmasi</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon green"><i class="bi bi-people"></i></div>
            <div>
                <div class="stat-value">{{ $totalCustomers }}</div>
                <div class="stat-label">Total Pelanggan</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon blue"><i class="bi bi-cash-stack"></i></div>
            <div>
                <div class="stat-value" style="font-size:1.2rem;">
                    Rp {{ number_format($totalRevenue, 0, ',', '.') }}
                </div>
                <div class="stat-label">Total Pendapatan</div>
            </div>
        </div>
    </div>
</div>

{{-- ═══ 1. Ringkasan Pendapatan ═════════════════════════════════════════ --}}
<div class="row g-3 mb-4">
    <div class="col-12 col-md-4">
        <div class="revenue-card revenue-today">
            <div class="revenue-icon"><i class="bi bi-sun"></i></div>
            <div>
                <div class="revenue-amount">Rp {{ number_format($revenueToday, 0, ',', '.') }}</div>
                <div class="revenue-period">Pendapatan Hari Ini</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="revenue-card revenue-week">
            <div class="revenue-icon"><i class="bi bi-calendar-week"></i></div>
            <div>
                <div class="revenue-amount">Rp {{ number_format($revenueThisWeek, 0, ',', '.') }}</div>
                <div class="revenue-period">Pendapatan Minggu Ini</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="revenue-card revenue-month">
            <div class="revenue-icon"><i class="bi bi-calendar-month"></i></div>
            <div>
                <div class="revenue-amount">Rp {{ number_format($revenueThisMonth, 0, ',', '.') }}</div>
                <div class="revenue-period">Pendapatan Bulan Ini</div>
            </div>
        </div>
    </div>
</div>

{{-- ═══ 2. Grafik Penjualan + 3. Status Pesanan ════════════════════════ --}}
<div class="row g-3 mb-4">
    {{-- Bar Chart — Penjualan 7 Hari --}}
    <div class="col-12 col-lg-8">
        <div class="dash-card">
            <div class="dash-card-header">
                <div class="dash-card-title"><i class="bi bi-graph-up"></i> Grafik Penjualan Mingguan</div>
                <span class="dash-card-badge">7 Hari Terakhir</span>
            </div>
            <div class="chart-container">
                <canvas id="salesChart"></canvas>
            </div>
        </div>
    </div>

    {{-- Donut Chart — Status Pesanan --}}
    <div class="col-12 col-lg-4">
        <div class="dash-card">
            <div class="dash-card-header">
                <div class="dash-card-title"><i class="bi bi-pie-chart"></i> Status Pesanan</div>
            </div>
            <div class="chart-container-donut">
                <canvas id="statusChart"></canvas>
            </div>
            <div class="status-legend">
                @php
                    $statusColors = [
                        'Menunggu Konfirmasi' => '#FBBF24',
                        'Diproses' => '#60A5FA',
                        'Siap Ambil' => '#34D399',
                        'Dalam Perjalanan' => '#A78BFA',
                        'Selesai' => '#10B981',
                        'Dibatalkan' => '#F87171',
                    ];
                @endphp
                @foreach($orderStatusCounts as $status => $count)
                    @if($count > 0)
                    <div class="status-legend-item">
                        <span class="status-dot" style="background:{{ $statusColors[$status] ?? '#ccc' }}"></span>
                        <span class="status-legend-label">{{ $status }}</span>
                        <span class="status-legend-count">{{ $count }}</span>
                    </div>
                    @endif
                @endforeach
            </div>
        </div>
    </div>
</div>

{{-- ═══ 4. Produk Terlaris + 5. Stok Menipis ═══════════════════════════ --}}
<div class="row g-3 mb-4">
    {{-- Produk Terlaris --}}
    <div class="col-12 col-lg-7">
        <div class="dash-card">
            <div class="dash-card-header">
                <div class="dash-card-title"><i class="bi bi-star"></i> Produk Terlaris</div>
                <span class="dash-card-badge">Top 5</span>
            </div>
            @if($topProducts->count())
            <div class="table-responsive">
                <table class="table-vb w-100 mb-0">
                    <thead>
                        <tr>
                            <th style="width:42px">#</th>
                            <th>Produk</th>
                            <th class="text-center">Terjual</th>
                            <th class="text-end">Pendapatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($topProducts as $index => $item)
                        <tr>
                            <td>
                                <span class="top-rank rank-{{ $index + 1 }}">{{ $index + 1 }}</span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    @if($item->product && $item->product->image)
                                        <img src="{{ asset('storage/' . $item->product->image) }}"
                                             alt="" class="top-product-img"
                                             style="width:36px;height:36px;min-width:36px;max-width:36px;object-fit:cover;">
                                    @else
                                        <div class="top-product-img-placeholder">
                                            <i class="bi bi-box-seam"></i>
                                        </div>
                                    @endif
                                    <span class="fw-600" style="font-size:.875rem;">
                                        {{ $item->product->name ?? '—' }}
                                    </span>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="sold-badge">{{ $item->total_sold }} pcs</span>
                            </td>
                            <td class="text-end fw-600" style="color:var(--orange); font-size:.875rem; white-space:nowrap;">
                                Rp {{ number_format($item->total_revenue, 0, ',', '.') }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="text-center text-muted py-4" style="font-size:.875rem;">
                <i class="bi bi-inbox" style="font-size:2rem; display:block; margin-bottom:.5rem; opacity:.4;"></i>
                Belum ada data penjualan
            </div>
            @endif
        </div>
    </div>

    {{-- Stok Menipis --}}
    <div class="col-12 col-lg-5">
        <div class="dash-card">
            <div class="dash-card-header">
                <div class="dash-card-title"><i class="bi bi-exclamation-triangle"></i> Stok Menipis</div>
                <span class="dash-card-badge badge-warning">≤ 10</span>
            </div>
            @if($lowStockProducts->count())
            <div class="stock-alert-list" style="max-height:380px; overflow-y:auto;">
                @foreach($lowStockProducts as $product)
                <div class="stock-alert-item">
                    <div class="d-flex align-items-center gap-2 flex-grow-1">
                        @if($product->image)
                            <img src="{{ asset('storage/' . $product->image) }}" alt="" class="stock-product-img"
                                 style="width:34px;height:34px;min-width:34px;max-width:34px;object-fit:cover;">
                        @else
                            <div class="stock-product-img-placeholder"><i class="bi bi-box-seam"></i></div>
                        @endif
                        <div>
                            <div class="stock-product-name">{{ $product->name }}</div>
                            <div class="stock-product-price">Rp {{ number_format($product->price, 0, ',', '.') }}</div>
                        </div>
                    </div>
                    <div class="stock-count {{ $product->stock <= 3 ? 'stock-critical' : ($product->stock <= 5 ? 'stock-warning' : 'stock-low') }}">
                        {{ $product->stock }} <span>sisa</span>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="text-center py-4" style="font-size:.875rem;">
                <i class="bi bi-check-circle" style="font-size:2rem; display:block; margin-bottom:.5rem; color:#10B981;"></i>
                <span style="color: var(--muted);">Semua stok aman 👍</span>
            </div>
            @endif
        </div>
    </div>
</div>

{{-- ═══ 6. Aktivitas Hari Ini + 7. Pelanggan Teraktif ══════════════════ --}}
<div class="row g-3 mb-2">
    {{-- Aktivitas Hari Ini --}}
    <div class="col-12 col-lg-5">
        <div class="dash-card">
            <div class="dash-card-header">
                <div class="dash-card-title"><i class="bi bi-activity"></i> Aktivitas Hari Ini</div>
            </div>
            <div class="activity-feed">
                <div class="activity-item">
                    <div class="activity-icon bg-blue-lt"><i class="bi bi-bag-plus" style="color:var(--blue-dk);"></i></div>
                    <div class="activity-text">
                        <strong>{{ $todayOrders }}</strong> pesanan masuk hari ini
                    </div>
                </div>
                <div class="activity-item">
                    <div class="activity-icon bg-green-lt"><i class="bi bi-person-plus" style="color:#15803D;"></i></div>
                    <div class="activity-text">
                        <strong>{{ $todayNewCustomers }}</strong> pelanggan baru mendaftar
                    </div>
                </div>
                <div class="activity-item">
                    <div class="activity-icon bg-orange-lt"><i class="bi bi-credit-card-2-front" style="color:var(--orange-dk);"></i></div>
                    <div class="activity-text">
                        <strong>{{ $todayPaidTransactions }}</strong> pembayaran berhasil
                    </div>
                </div>
                <div class="activity-item">
                    <div class="activity-icon bg-yellow-lt"><i class="bi bi-check2-circle" style="color:var(--yellow-dk);"></i></div>
                    <div class="activity-text">
                        <strong>{{ $todayCompletedOrders }}</strong> pesanan selesai
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Pelanggan Teraktif --}}
    <div class="col-12 col-lg-7">
        <div class="dash-card">
            <div class="dash-card-header">
                <div class="dash-card-title"><i class="bi bi-people"></i> Pelanggan Teraktif</div>
                <span class="dash-card-badge">Top 5</span>
            </div>
            @if($topCustomers->count())
            <div class="table-responsive">
                <table class="table-vb w-100 mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Pelanggan</th>
                            <th class="text-center">Pesanan</th>
                            <th class="text-end">Total Belanja</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($topCustomers as $index => $customer)
                        <tr>
                            <td>
                                <span class="top-rank rank-{{ $index + 1 }}">{{ $index + 1 }}</span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="customer-avatar">
                                        {{ strtoupper(substr($customer->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="fw-600" style="font-size:.875rem;">{{ $customer->name }}</div>
                                        <div style="font-size:.72rem; color:var(--muted);">{{ $customer->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="sold-badge">{{ $customer->order_count ?? 0 }}×</span>
                            </td>
                            <td class="text-end fw-600" style="color:var(--orange); font-size:.875rem; white-space:nowrap;">
                                Rp {{ number_format($customer->total_spent ?? 0, 0, ',', '.') }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="text-center text-muted py-4" style="font-size:.875rem;">
                <i class="bi bi-inbox" style="font-size:2rem; display:block; margin-bottom:.5rem; opacity:.4;"></i>
                Belum ada data pelanggan
            </div>
            @endif
        </div>
    </div>
</div>

{{-- ═══ Chart.js ════════════════════════════════════════════════════════ --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    /* ── Line Chart: Penjualan Mingguan ──────────────────── */
    const salesCtx = document.getElementById('salesChart').getContext('2d');

    // Create gradient fill for area under line
    const gradient = salesCtx.createLinearGradient(0, 0, 0, 320);
    gradient.addColorStop(0, 'rgba(244, 125, 53, 0.35)');
    gradient.addColorStop(0.5, 'rgba(249, 194, 60, 0.15)');
    gradient.addColorStop(1, 'rgba(249, 194, 60, 0.02)');

    new Chart(salesCtx, {
        type: 'line',
        data: {
            labels: @json($salesChartLabels),
            datasets: [{
                label: 'Pendapatan (Rp)',
                data: @json($salesChartData),
                borderColor: '#F47D35',
                backgroundColor: gradient,
                borderWidth: 3,
                pointBackgroundColor: '#F47D35',
                pointBorderColor: '#FFFFFF',
                pointBorderWidth: 2,
                pointRadius: 6,
                pointHoverRadius: 9,
                pointHoverBackgroundColor: '#F9C23C',
                pointHoverBorderColor: '#F47D35',
                pointHoverBorderWidth: 3,
                tension: 0.35,
                fill: true,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                intersect: false,
                mode: 'index',
            },
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#1A1A1A',
                    titleFont: { family: "'Poppins', sans-serif", size: 12 },
                    bodyFont: { family: "'Poppins', sans-serif", size: 13, weight: '600' },
                    padding: 14,
                    cornerRadius: 10,
                    displayColors: false,
                    callbacks: {
                        title: function(ctx) {
                            return '📅 ' + ctx[0].label;
                        },
                        label: function(ctx) {
                            return '💰 Rp ' + ctx.raw.toLocaleString('id-ID');
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(0,0,0,0.04)', drawBorder: false },
                    border: { display: false },
                    ticks: {
                        font: { family: "'Poppins', sans-serif", size: 11 },
                        color: '#6B6B6B',
                        callback: function(v) {
                            if (v >= 1000000) return 'Rp ' + (v/1000000).toFixed(1) + 'jt';
                            if (v >= 1000) return 'Rp ' + (v/1000).toFixed(0) + 'rb';
                            return 'Rp ' + v;
                        }
                    }
                },
                x: {
                    grid: { display: false },
                    border: { display: false },
                    ticks: {
                        font: { family: "'Poppins', sans-serif", size: 11 },
                        color: '#6B6B6B',
                    }
                }
            }
        }
    });

    /* ── Donut Chart: Status Pesanan ──────────────────────── */
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    const statusData = @json(array_values($orderStatusCounts));
    const statusLabels = @json(array_keys($orderStatusCounts));
    const statusColors = ['#FBBF24', '#60A5FA', '#34D399', '#A78BFA', '#10B981', '#F87171'];

    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: statusLabels,
            datasets: [{
                data: statusData,
                backgroundColor: statusColors,
                borderWidth: 3,
                borderColor: '#FFFFFF',
                hoverOffset: 6,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '68%',
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#1A1A1A',
                    titleFont: { family: "'Poppins', sans-serif", size: 12 },
                    bodyFont: { family: "'Poppins', sans-serif", size: 12 },
                    padding: 12,
                    cornerRadius: 8,
                }
            }
        }
    });

});
</script>

@endsection
