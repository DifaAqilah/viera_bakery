<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Transaction;
use App\Services\BestSellerService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $dateFrom       = $request->query('date_from');
        $dateTo         = $request->query('date_to');
        $filterPayment  = $request->query('payment_status');
        $filterOrder    = $request->query('order_status');
        $filterMethod   = $request->query('delivery_method');
        $filterPayMethod = $request->query('payment_method');
        $filterCustomer = $request->query('customer');

        // ── Base query for ALL orders (for stats) ──
        $allOrdersQuery = Order::with('user', 'transaction', 'items.product');

        if ($dateFrom) {
            $allOrdersQuery->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $allOrdersQuery->whereDate('created_at', '<=', $dateTo);
        }
        if ($filterOrder) {
            $allOrdersQuery->where('status', $filterOrder);
        }
        if ($filterMethod) {
            $allOrdersQuery->where('delivery_method', $filterMethod);
        }
        if ($filterCustomer) {
            $allOrdersQuery->whereHas('user', fn($q) => $q->where('name', 'like', "%{$filterCustomer}%"));
        }
        if ($filterPayment) {
            $allOrdersQuery->whereHas('transaction', fn($q) => $q->where('payment_status', $filterPayment));
        }
        if ($filterPayMethod) {
            $allOrdersQuery->whereHas('transaction', fn($q) => $q->where('payment_method', $filterPayMethod));
        }

        // ── Stats cards ──
        $totalOrders = (clone $allOrdersQuery)->count();

        // Total Pendapatan: only from paid transactions
        $paidOrderIds = (clone $allOrdersQuery)->whereHas('transaction', fn($q) => $q->where('payment_status', 'paid'))->pluck('id');
        $totalRevenue = Transaction::whereIn('order_id', $paidOrderIds)->where('payment_status', 'paid')->sum('amount');
        $transactionsCount = $paidOrderIds->count();

        $pendingOrders = (clone $allOrdersQuery)->whereIn('status', ['Menunggu Konfirmasi', 'Menunggu Diproses'])->count();
        $cancelledOrders = (clone $allOrdersQuery)->where('status', 'Dibatalkan')->count();

        // Produk terjual (hanya dari pesanan paid)
        $productsSold = OrderItem::whereIn('order_id', $paidOrderIds)->sum('quantity');

        // Rata-rata transaksi
        $avgTransaction = $transactionsCount > 0 ? round($totalRevenue / $transactionsCount) : 0;

        // ── Grafik: pendapatan per hari (30 hari terakhir) ──
        $chartDays = 14;
        $chartLabels = [];
        $chartRevenue = [];
        $chartOrders = [];

        for ($i = $chartDays - 1; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $chartLabels[] = $date->format('d/m');

            $dayPaidIds = Order::whereDate('created_at', $date)
                ->whereHas('transaction', fn($q) => $q->where('payment_status', 'paid'))
                ->pluck('id');

            $chartRevenue[] = (int) Transaction::whereIn('order_id', $dayPaidIds)->where('payment_status', 'paid')->sum('amount');
            $chartOrders[] = (int) Order::whereDate('created_at', $date)->count();
        }

        // ── Grafik: metode pemesanan ──
        $pickupCount = (clone $allOrdersQuery)->where('delivery_method', 'self_pickup')->count();
        $deliveryCount = (clone $allOrdersQuery)->where('delivery_method', 'delivery')->count();

        // ── Grafik: status pembayaran ──
        $paymentPaid = (clone $allOrdersQuery)->whereHas('transaction', fn($q) => $q->where('payment_status', 'paid'))->count();
        $paymentPending = (clone $allOrdersQuery)->whereHas('transaction', fn($q) => $q->where('payment_status', 'pending'))->count();
        $paymentFailed = (clone $allOrdersQuery)->whereHas('transaction', fn($q) => $q->where('payment_status', 'failed'))->count();

        // ── Produk terlaris (dari service reusable, dengan filter) ──
        $topProducts = BestSellerService::get([
            'limit'     => 10,
            'order_ids' => $paidOrderIds,
        ]);

        // ── Rincian transaksi lunas (tabel) ──
        $paidTransQuery = Transaction::with('order.user', 'order.items.product')
            ->where('payment_status', 'paid');

        if ($dateFrom) {
            $paidTransQuery->whereDate('paid_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $paidTransQuery->whereDate('paid_at', '<=', $dateTo);
        }
        if ($filterOrder) {
            $paidTransQuery->whereHas('order', fn($q) => $q->where('status', $filterOrder));
        }
        if ($filterMethod) {
            $paidTransQuery->whereHas('order', fn($q) => $q->where('delivery_method', $filterMethod));
        }
        if ($filterPayMethod) {
            $paidTransQuery->where('payment_method', $filterPayMethod);
        }
        if ($filterCustomer) {
            $paidTransQuery->whereHas('order.user', fn($q) => $q->where('name', 'like', "%{$filterCustomer}%"));
        }

        $transactions = $paidTransQuery->orderBy('paid_at', 'desc')->paginate(15)->withQueryString();

        return view('admin.reports.index', compact(
            'transactions', 'totalRevenue', 'transactionsCount',
            'dateFrom', 'dateTo', 'filterPayment', 'filterOrder',
            'filterMethod', 'filterPayMethod', 'filterCustomer',
            'totalOrders', 'pendingOrders', 'cancelledOrders',
            'productsSold', 'avgTransaction',
            'chartLabels', 'chartRevenue', 'chartOrders',
            'pickupCount', 'deliveryCount',
            'paymentPaid', 'paymentPending', 'paymentFailed',
            'topProducts'
        ));
    }

    public function export(Request $request)
    {
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');

        $baseQuery = Transaction::with('order.user', 'order.items.product')
            ->where('payment_status', 'paid');

        if ($dateFrom) {
            $baseQuery->whereDate('paid_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $baseQuery->whereDate('paid_at', '<=', $dateTo);
        }

        $transactions = $baseQuery->orderBy('paid_at', 'desc')->get();

        // ── Create Spreadsheet ──
        $spreadsheet = new Spreadsheet();

        // ============================================
        // SHEET 1: Ringkasan Penjualan
        // ============================================
        $sheet1 = $spreadsheet->getActiveSheet();
        $sheet1->setTitle('Ringkasan Penjualan');

        // Header styles
        $headerFill = [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['rgb' => 'E67E22'],
        ];
        $headerFont = [
            'bold' => true,
            'color' => ['rgb' => 'FFFFFF'],
            'size' => 11,
        ];
        $titleFont = [
            'bold' => true,
            'size' => 14,
            'color' => ['rgb' => '8B4513'],
        ];
        $borderStyle = [
            'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CCCCCC']],
        ];

        // Title
        $sheet1->setCellValue('A1', 'LAPORAN PENJUALAN VIERA BAKERY');
        $sheet1->mergeCells('A1:M1');
        $sheet1->getStyle('A1')->getFont()->applyFromArray($titleFont);
        $sheet1->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Period
        $periodText = ($dateFrom && $dateTo)
            ? "Periode: " . Carbon::parse($dateFrom)->format('d/m/Y') . " - " . Carbon::parse($dateTo)->format('d/m/Y')
            : "Periode: Semua Data";
        $sheet1->setCellValue('A2', $periodText);
        $sheet1->mergeCells('A2:M2');
        $sheet1->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet1->getStyle('A2')->getFont()->setItalic(true);

        // Summary stats
        $totalRevenue = $transactions->sum('amount');
        $totalTransactions = $transactions->count();
        $totalShipping = $transactions->sum(fn($t) => $t->order->shipping_cost ?? 0);
        $totalProductsSold = 0;
        foreach ($transactions as $t) {
            $totalProductsSold += $t->order->items->sum('quantity');
        }

        $sheet1->setCellValue('A4', 'Total Pendapatan:');
        $sheet1->setCellValue('B4', $totalRevenue);
        $sheet1->getStyle('B4')->getNumberFormat()->setFormatCode('#,##0');
        $sheet1->getStyle('A4')->getFont()->setBold(true);

        $sheet1->setCellValue('C4', 'Transaksi Lunas:');
        $sheet1->setCellValue('D4', $totalTransactions);
        $sheet1->getStyle('C4')->getFont()->setBold(true);

        $sheet1->setCellValue('E4', 'Total Ongkir:');
        $sheet1->setCellValue('F4', $totalShipping);
        $sheet1->getStyle('F4')->getNumberFormat()->setFormatCode('#,##0');
        $sheet1->getStyle('E4')->getFont()->setBold(true);

        $sheet1->setCellValue('G4', 'Produk Terjual:');
        $sheet1->setCellValue('H4', $totalProductsSold);
        $sheet1->getStyle('G4')->getFont()->setBold(true);

        // Column headers (row 6)
        $columns = ['No', 'Kode Pesanan', 'Tanggal Pesanan', 'Tanggal Bayar', 'Nama Pelanggan',
            'Email Pelanggan', 'Metode Pemesanan', 'Metode Pembayaran', 'Status Pesanan',
            'Status Pembayaran', 'Ongkir', 'Total Produk', 'Total Pembayaran'];

        $colLetters = ['A','B','C','D','E','F','G','H','I','J','K','L','M'];

        foreach ($columns as $idx => $col) {
            $cell = $colLetters[$idx] . '6';
            $sheet1->setCellValue($cell, $col);
        }

        // Style header row
        $headerRange = 'A6:M6';
        $sheet1->getStyle($headerRange)->getFill()->applyFromArray($headerFill);
        $sheet1->getStyle($headerRange)->getFont()->applyFromArray($headerFont);
        $sheet1->getStyle($headerRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Data rows
        $row = 7;
        $no = 1;
        foreach ($transactions as $t) {
            $order = $t->order;
            $user = $order->user;

            $sheet1->setCellValue("A{$row}", $no);
            $sheet1->setCellValue("B{$row}", $t->transaction_code);
            $sheet1->setCellValue("C{$row}", $order->created_at ? Carbon::parse($order->created_at)->timezone('Asia/Jakarta')->format('d/m/Y H:i') : '-');
            $sheet1->setCellValue("D{$row}", $t->paid_at ? Carbon::parse($t->paid_at)->timezone('Asia/Jakarta')->format('d/m/Y H:i') : '-');
            $sheet1->setCellValue("E{$row}", $user->name ?? '-');
            $sheet1->setCellValue("F{$row}", $user->email ?? '-');
            $sheet1->setCellValue("G{$row}", $order->delivery_method === 'self_pickup' ? 'Pickup' : 'Delivery');
            $sheet1->setCellValue("H{$row}", ucfirst(str_replace('_', ' ', $t->payment_method ?? '-')));
            $sheet1->setCellValue("I{$row}", $order->status ?? '-');
            $sheet1->setCellValue("J{$row}", ucfirst($t->payment_status));
            $sheet1->setCellValue("K{$row}", $order->shipping_cost ?? 0);
            $sheet1->getStyle("K{$row}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet1->setCellValue("L{$row}", $order->items->sum('quantity'));
            $sheet1->setCellValue("M{$row}", $t->amount);
            $sheet1->getStyle("M{$row}")->getNumberFormat()->setFormatCode('#,##0');

            $no++;
            $row++;
        }

        // Borders for data area
        if ($row > 7) {
            $dataRange = "A6:M" . ($row - 1);
            $sheet1->getStyle($dataRange)->getBorders()->applyFromArray($borderStyle);
        }

        // Auto width
        foreach ($colLetters as $col) {
            $sheet1->getColumnDimension($col)->setAutoSize(true);
        }

        // Freeze pane
        $sheet1->freezePane('A7');

        // Auto filter
        if ($row > 7) {
            $sheet1->setAutoFilter("A6:M" . ($row - 1));
        }

        // ============================================
        // SHEET 2: Detail Produk Terjual
        // ============================================
        $sheet2 = $spreadsheet->createSheet();
        $sheet2->setTitle('Detail Produk Terjual');

        // Title
        $sheet2->setCellValue('A1', 'DETAIL PRODUK TERJUAL — VIERA BAKERY');
        $sheet2->mergeCells('A1:I1');
        $sheet2->getStyle('A1')->getFont()->applyFromArray($titleFont);
        $sheet2->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Period
        $sheet2->setCellValue('A2', $periodText);
        $sheet2->mergeCells('A2:I2');
        $sheet2->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet2->getStyle('A2')->getFont()->setItalic(true);

        // Column headers
        $cols2 = ['No', 'Kode Pesanan', 'Tanggal Pesanan', 'Nama Pelanggan', 'Nama Produk',
            'Kategori Produk', 'Harga Satuan', 'Jumlah', 'Subtotal'];
        $colLetters2 = ['A','B','C','D','E','F','G','H','I'];

        foreach ($cols2 as $idx => $col) {
            $cell = $colLetters2[$idx] . '4';
            $sheet2->setCellValue($cell, $col);
        }

        $headerRange2 = 'A4:I4';
        $sheet2->getStyle($headerRange2)->getFill()->applyFromArray($headerFill);
        $sheet2->getStyle($headerRange2)->getFont()->applyFromArray($headerFont);
        $sheet2->getStyle($headerRange2)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Data rows
        $row2 = 5;
        $no2 = 1;
        foreach ($transactions as $t) {
            $order = $t->order;
            foreach ($order->items as $item) {
                $sheet2->setCellValue("A{$row2}", $no2);
                $sheet2->setCellValue("B{$row2}", $t->transaction_code);
                $sheet2->setCellValue("C{$row2}", $order->created_at ? Carbon::parse($order->created_at)->timezone('Asia/Jakarta')->format('d/m/Y H:i') : '-');
                $sheet2->setCellValue("D{$row2}", $order->user->name ?? '-');
                $sheet2->setCellValue("E{$row2}", $item->product->name ?? '-');
                $sheet2->setCellValue("F{$row2}", $item->product->category->name ?? '-');
                $sheet2->setCellValue("G{$row2}", $item->price);
                $sheet2->getStyle("G{$row2}")->getNumberFormat()->setFormatCode('#,##0');
                $sheet2->setCellValue("H{$row2}", $item->quantity);
                $sheet2->setCellValue("I{$row2}", $item->subtotal);
                $sheet2->getStyle("I{$row2}")->getNumberFormat()->setFormatCode('#,##0');

                $no2++;
                $row2++;
            }
        }

        // Borders
        if ($row2 > 5) {
            $sheet2->getStyle("A4:I" . ($row2 - 1))->getBorders()->applyFromArray($borderStyle);
        }

        // Auto width
        foreach ($colLetters2 as $col) {
            $sheet2->getColumnDimension($col)->setAutoSize(true);
        }

        // Freeze pane
        $sheet2->freezePane('A5');

        // Auto filter
        if ($row2 > 5) {
            $sheet2->setAutoFilter("A4:I" . ($row2 - 1));
        }

        // ── Generate filename ──
        $datePart = ($dateFrom && $dateTo)
            ? Carbon::parse($dateFrom)->format('Y-m-d') . '-sampai-' . Carbon::parse($dateTo)->format('Y-m-d')
            : 'semua-data';
        $fileName = "laporan-penjualan-viera-bakery-{$datePart}.xlsx";

        // ── Return response ──
        $spreadsheet->setActiveSheetIndex(0);

        $writer = new Xlsx($spreadsheet);

        $tempFile = tempnam(sys_get_temp_dir(), 'viera_report_');
        $writer->save($tempFile);

        return response()->download($tempFile, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }
}
