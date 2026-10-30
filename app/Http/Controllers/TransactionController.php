<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Services\ChatService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    public function show(Transaction $transaction)
    {
        $this->authorize('view', $transaction->order);
        return view('transactions.show', compact('transaction'));
    }

    /**
     * Cancel an order (customer-initiated).
     *
     * Rules:
     * - Order must belong to the logged-in user.
     * - Order status must be 'Menunggu Konfirmasi' or 'Menunggu Diproses'.
     * - If QRIS: must NOT have uploaded proof, must NOT have payment_status = paid,
     *   and order payment_status must NOT be 'Menunggu Konfirmasi'.
     * - Transaction payment_status must NOT be 'paid'.
     * - cancel_reason is required.
     * - If cancel_reason is 'Lainnya', cancel_reason_other is required (max 255).
     * - On success: set order status to 'Dibatalkan', payment_status to 'failed',
     *   restore stock, send bot message with reason, save cancel metadata.
     */
    public function cancel(Request $request, Transaction $transaction)
    {
        $order = $transaction->order;

        // 1. Pastikan pesanan milik user yang login
        if ($order->user_id !== Auth::id()) {
            abort(403, 'Akses tidak diizinkan.');
        }

        // 2. Status pesanan yang boleh dibatalkan
        $cancellableStatuses = ['Menunggu Konfirmasi', 'Menunggu Diproses'];
        if (!in_array($order->status, $cancellableStatuses)) {
            return back()->with('error', 'Pesanan tidak dapat dibatalkan karena sudah dalam proses.');
        }

        // 3. Cek pembayaran QRIS
        if ($order->payment_method === 'qris') {
            if ($order->payment_proof) {
                return back()->with('error', 'Pesanan QRIS yang sudah upload bukti pembayaran tidak dapat dibatalkan. Silakan hubungi admin jika membutuhkan bantuan.');
            }
            if ($transaction->payment_status === 'paid') {
                return back()->with('error', 'Pesanan yang sudah lunas tidak dapat dibatalkan.');
            }
            if ($order->payment_status === 'Menunggu Konfirmasi') {
                return back()->with('error', 'Pesanan QRIS yang sedang menunggu konfirmasi pembayaran tidak dapat dibatalkan. Silakan hubungi admin jika membutuhkan bantuan.');
            }
        }

        // 4. Cek status pembayaran transaksi (untuk semua metode)
        if ($transaction->payment_status === 'paid') {
            return back()->with('error', 'Pesanan yang sudah lunas tidak dapat dibatalkan.');
        }

        // 5. Validasi alasan pembatalan
        $request->validate([
            'cancel_reason' => 'required|string|max:255',
            'cancel_reason_other' => 'nullable|string|max:255',
        ], [
            'cancel_reason.required' => 'Silakan pilih alasan pembatalan.',
        ]);

        // Tentukan alasan final
        $reason = $request->cancel_reason;
        if ($reason === 'Lainnya') {
            if (!$request->filled('cancel_reason_other')) {
                return back()->with('error', 'Silakan isi alasan pembatalan secara manual.')->withInput();
            }
            $reason = $request->cancel_reason_other;
        }

        // 6. Eksekusi pembatalan dalam DB transaction
        DB::transaction(function () use ($order, $transaction, $reason) {
            // Update status pesanan
            $order->status = 'Dibatalkan';
            $order->cancel_reason = $reason;
            $order->cancelled_at = now();
            $order->cancelled_by = 'customer';
            $order->save();

            // Update status pembayaran transaksi → failed
            $transaction->payment_status = 'failed';
            $transaction->save();

            // Kembalikan stok produk
            foreach ($order->items as $item) {
                $product = $item->product;
                if ($product) {
                    $product->increment('stock', $item->quantity);
                }
            }

            // Kirim notifikasi bot via chat (satu pesan saja)
            ChatService::sendBotMessage(
                $order->user_id,
                "Pesanan #{$order->id} telah dibatalkan.\nAlasan: {$reason}\n\n[Ini adalah pesan otomatis]",
                $order->id
            );
        });

        // 7. Redirect kembali dengan pesan sukses
        $redirectUrl = route('transactions.show', $transaction);
        if ($request->query('from') === 'chat') {
            $redirectUrl .= '?from=chat';
        }

        return redirect($redirectUrl)->with('success', 'Pesanan berhasil dibatalkan.');
    }

    public function confirmPayment(Request $request, Transaction $transaction)
    {
        $this->authorize('view', $transaction->order);

        $transaction->payment_status = 'paid';
        $transaction->paid_at = now();
        $transaction->save();

        // update order status
        $order = $transaction->order;
        $order->status = 'Diproses';
        $order->save();

        // notify via chat
        $chat = $order->user->chats()->first() ?? $order->user->chats()->create(['subject' => 'Notifikasi Pesanan #' . $order->id]);
        $chat->messages()->create(['sender_role' => 'admin', 'message' => "[BOT] Pembayaran untuk pesanan #{$order->id} diterima.", 'is_bot' => true]);

        return redirect()->route('orders.thanks', $order->id)->with('status','Pembayaran dikonfirmasi');
    }
}
