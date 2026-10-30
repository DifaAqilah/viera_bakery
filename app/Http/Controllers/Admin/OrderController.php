     <?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\ChatService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::with('user', 'transaction')->latest()->paginate(20);
        return view('admin.orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        $order->load('items.product', 'user', 'transaction');
        return view('admin.orders.show', compact('order'));
    }

    /**
     * Return count of new/pending orders for sidebar badge (AJAX).
     */
    public function newCount()
    {
        $count = Order::whereIn('status', ['Menunggu Konfirmasi', 'Menunggu Diproses'])->count();
        return response()->json(['count' => $count]);
    }

    public function updateStatus(Request $request, Order $order)
    {
        $data = $request->validate([
            'status' => 'required|in:Menunggu Konfirmasi,Menunggu Diproses,Diproses,Siap Ambil,Dalam Perjalanan,Selesai,Dibatalkan',
        ]);

        $oldStatus = $order->status;
        $newStatus = $data['status'];

        // Prevent reactivating cancelled orders
        if ($oldStatus === 'Dibatalkan' && $newStatus !== 'Dibatalkan') {
            return back()->with('error', 'Pesanan yang sudah dibatalkan tidak dapat diproses kembali.');
        }

        $order->status = $newStatus;
        $order->save();

        // Send bot notification via ChatService (only if status actually changed)
        ChatService::notifyOrderStatusChanged($order, $oldStatus, $newStatus);

        return back()->with('success', 'Status pesanan berhasil diperbarui.');
    }

    public function updatePaymentStatus(Request $request, Order $order)
    {
        $data = $request->validate([
            'payment_status' => 'required|in:pending,paid,failed',
        ]);

        $transaction = $order->transaction;
        if (!$transaction) {
            return back()->with('error', 'Transaksi tidak ditemukan.');
        }

        $oldStatus = $transaction->payment_status;
        $newStatus = $data['payment_status'];

        $transaction->payment_status = $newStatus;
        if ($newStatus === 'paid' && !$transaction->paid_at) {
            $transaction->paid_at = now();
        }
        $transaction->save();

        // Send bot notification
        if ($oldStatus !== $newStatus) {
            $statusLabels = [
                'paid' => 'Lunas',
                'pending' => 'Menunggu',
                'failed' => 'Gagal',
            ];
            $label = $statusLabels[$newStatus] ?? $newStatus;
            $msg = "Status pembayaran pesanan #{$order->id} diubah menjadi: {$label}.\n\n[Ini adalah pesan otomatis]";
            ChatService::sendBotMessage($order->user_id, $msg, $order->id);
        }

        return back()->with('success', 'Status pembayaran berhasil diperbarui.');
    }
}
