<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use App\Services\BestSellerService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // ── Stat Cards (existing) ──────────────────────────────
        $totalOrders    = Order::count();
        $waitingOrders  = Order::where('status', 'Menunggu Konfirmasi')->count();
        $totalCustomers = User::where('role', 'pelanggan')->count();
        $totalRevenue   = Transaction::where('payment_status', 'paid')->sum('amount');

        // ── 1. Ringkasan Pendapatan ────────────────────────────
        $revenueToday = Transaction::where('payment_status', 'paid')
            ->whereDate('created_at', Carbon::today())
            ->sum('amount');

        $revenueThisWeek = Transaction::where('payment_status', 'paid')
            ->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
            ->sum('amount');

        $revenueThisMonth = Transaction::where('payment_status', 'paid')
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->sum('amount');

        // ── 2. Grafik Penjualan Mingguan (7 hari terakhir) ────
        $salesChartLabels = [];
        $salesChartData   = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $salesChartLabels[] = $date->translatedFormat('D, d M');
            $dayPaidIds = Order::where('status', '!=', 'Dibatalkan')
                ->whereDate('created_at', $date)
                ->whereHas('transaction', fn($q) => $q->where('payment_status', 'paid'))
                ->pluck('id');
            $salesChartData[] = (int) Transaction::whereIn('order_id', $dayPaidIds)
                ->where('payment_status', 'paid')
                ->sum('amount');
        }

        // ── 3. Produk Terlaris (Top 5) — dari service reusable ──
        $topProducts = BestSellerService::get(['limit' => 5]);

        // ── 4. Stok Menipis (≤ 5) ─────────────────────────────
        $lowStockProducts = Product::where('stock', '<=', 10)
            ->orderBy('stock', 'asc')
            ->take(8)
            ->get();

        // ── 5. Status Pesanan (for donut chart) ────────────────
        $statuses = ['Menunggu Konfirmasi', 'Diproses', 'Siap Ambil', 'Dalam Perjalanan', 'Selesai', 'Dibatalkan'];
        $orderStatusCounts = [];
        foreach ($statuses as $status) {
            $orderStatusCounts[$status] = Order::where('status', $status)->count();
        }

        // ── 6. Aktivitas Hari Ini ──────────────────────────────
        $todayOrders      = Order::whereDate('created_at', Carbon::today())->count();
        $todayNewCustomers = User::where('role', 'pelanggan')
            ->whereDate('created_at', Carbon::today())
            ->count();
        $todayPaidTransactions = Transaction::where('payment_status', 'paid')
            ->whereDate('created_at', Carbon::today())
            ->count();
        $todayCompletedOrders = Order::where('status', 'Selesai')
            ->whereDate('updated_at', Carbon::today())
            ->count();

        // ── 7. Pelanggan Teraktif (Top 5) ──────────────────────
        $topCustomers = User::where('role', 'pelanggan')
            ->withCount(['orders as order_count' => function ($q) {
                $q->where('status', '!=', 'Dibatalkan');
            }])
            ->withSum(['orders as total_spent' => function ($q) {
                $q->where('status', '!=', 'Dibatalkan');
            }], 'total_amount')
            ->orderByDesc('order_count')
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'totalOrders',
            'waitingOrders',
            'totalCustomers',
            'totalRevenue',
            'revenueToday',
            'revenueThisWeek',
            'revenueThisMonth',
            'salesChartLabels',
            'salesChartData',
            'topProducts',
            'lowStockProducts',
            'orderStatusCounts',
            'todayOrders',
            'todayNewCustomers',
            'todayPaidTransactions',
            'todayCompletedOrders',
            'topCustomers'
        ));
    }
}
