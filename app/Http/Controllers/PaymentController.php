<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Transaction;
use App\Services\MidtransService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    /**
     * Show payment page with Midtrans Snap
     */
    public function show(Order $order)
    {
        // Ensure user can only access their own orders
        if ($order->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        // Get transaction
        $transaction = $order->transaction;
        if (!$transaction) {
            abort(404, 'Transaction not found');
        }

        // Load order items with products
        $order->load('items.product', 'user');

        // Check if Midtrans keys are configured
        $serverKey = config('services.midtrans.server_key');
        $clientKey = config('services.midtrans.client_key');

        $snapToken = null;
        $midtransReady = true;

        if (!$serverKey || !$clientKey ||
            str_starts_with($serverKey, 'your_') ||
            str_starts_with($clientKey, 'your_')) {
            $midtransReady = false;
        }

        // Generate Midtrans Snap Token only if keys are valid and payment not yet completed
        if ($midtransReady && !in_array($transaction->payment_status, ['paid'])) {
            try {
                $snapToken = MidtransService::generateSnapToken($order, $transaction);
            } catch (\Exception $e) {
                $midtransReady = false;
            }
        }

        $snapUrl = MidtransService::getSnapUrl();
        $clientKeyValue = MidtransService::getClientKey();

        return view('payments.show', compact(
            'order', 'transaction', 'snapToken',
            'midtransReady', 'snapUrl', 'clientKeyValue'
        ));
    }

    /**
     * Upload payment proof (supports AJAX)
     */
    public function uploadProof(Request $request, Order $order)
    {
        // Ensure user can only upload for their own orders
        if ($order->user_id !== Auth::id()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
            abort(403, 'Unauthorized');
        }

        $request->validate([
            'payment_proof' => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        // Store the file
        $path = $request->file('payment_proof')->store('payment_proofs', 'public');

        // Update order with payment proof path
        $order->update(['payment_proof' => $path]);

        // Update transaction status to menunggu konfirmasi
        $transaction = $order->transaction;
        if ($transaction && $transaction->payment_status !== 'paid') {
            $transaction->update([
                'payment_status' => 'pending',
                'payment_method' => 'transfer_manual',
            ]);
        }

        // Update order status
        if ($order->status === 'Menunggu Konfirmasi') {
            $order->update(['status' => 'Menunggu Konfirmasi']);
        }

        // Notify via chat
        $chat = $order->user->chats()->where('subject', 'like', "%Pesanan #{$order->id}%")->first();
        if ($chat) {
            $chat->messages()->create([
                'sender_role' => 'admin',
                'message' => "[BOT] Bukti pembayaran untuk Pesanan #{$order->id} telah diupload. Menunggu konfirmasi admin.",
                'is_bot' => true,
            ]);
        }

        // Support AJAX response
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'message' => 'Bukti pembayaran berhasil diupload.',
                'payment_proof' => $path,
            ]);
        }

        return back()->with('success', 'Bukti pembayaran berhasil diupload. Menunggu konfirmasi admin.');
    }

    /**
     * Place order with Onsite payment method
     */
    public function placeOnsite(Request $request, Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Update order
        $order->update([
            'payment_method' => 'onsite',
            'payment_status' => 'Bayar di Tempat',
            'status' => 'Menunggu Diproses',
        ]);

        // Update transaction
        $transaction = $order->transaction;
        if ($transaction) {
            $transaction->update([
                'payment_method' => 'onsite',
                'payment_status' => 'pending',
            ]);
        }

        // Notify via chat
        $chat = $order->user->chats()->where('subject', 'like', "%Pesanan #{$order->id}%")->first();
        if ($chat) {
            $chat->messages()->create([
                'sender_role' => 'admin',
                'message' => "[BOT] Pesanan #{$order->id} dibuat dengan pembayaran Onsite (Bayar di Tempat). Menunggu diproses.",
                'is_bot' => true,
            ]);
        }

        return response()->json([
            'message' => 'Pesanan berhasil dibuat.',
            'order' => [
                'id' => $order->id,
                'payment_method' => 'Onsite',
                'payment_status' => 'Bayar di Tempat',
                'total_amount' => $order->total_amount,
                'created_at' => $order->created_at->format('d M Y'),
            ],
        ]);
    }

    /**
     * Place order with QRIS payment method
     */
    public function placeQris(Request $request, Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Validate payment proof exists
        if (!$order->payment_proof) {
            return response()->json(['message' => 'Silakan upload bukti pembayaran terlebih dahulu.'], 422);
        }

        // Update order
        $order->update([
            'payment_method' => 'qris',
            'payment_status' => 'Menunggu Konfirmasi',
            'status' => 'Menunggu Konfirmasi',
        ]);

        // Update transaction
        $transaction = $order->transaction;
        if ($transaction) {
            $transaction->update([
                'payment_method' => 'qris',
                'payment_status' => 'pending',
            ]);
        }

        // Notify via chat
        $chat = $order->user->chats()->where('subject', 'like', "%Pesanan #{$order->id}%")->first();
        if ($chat) {
            $chat->messages()->create([
                'sender_role' => 'admin',
                'message' => "[BOT] Pesanan #{$order->id} dibuat dengan pembayaran QRIS. Bukti pembayaran telah dikirim, menunggu konfirmasi admin.",
                'is_bot' => true,
            ]);
        }

        return response()->json([
            'message' => 'Pesanan berhasil dibuat.',
            'order' => [
                'id' => $order->id,
                'payment_method' => 'QRIS',
                'payment_status' => 'Menunggu Konfirmasi',
                'total_amount' => $order->total_amount,
                'created_at' => $order->created_at->format('d M Y'),
            ],
        ]);
    }

    /**
     * Handle payment status update via AJAX from frontend Snap callback
     */
    public function updateStatus(Request $request, Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $transaction = $order->transaction;
        if (!$transaction) {
            return response()->json(['message' => 'Transaction not found'], 404);
        }

        $data = $request->validate([
            'status' => 'required|in:success,pending,error',
            'payment_type' => 'nullable|string',
        ]);

        if ($data['status'] === 'success' || $data['status'] === 'pending') {
            // Set ke "menunggu konfirmasi" — jangan langsung paid
            // Hanya callback Midtrans yang boleh set paid
            if ($transaction->payment_status !== 'paid') {
                $transaction->update([
                    'payment_status' => 'pending',
                    'payment_method' => $data['payment_type'] ?? 'midtrans',
                ]);
            }

            // Update order status
            if ($order->status === 'Menunggu Konfirmasi') {
                $order->update(['status' => 'Menunggu Konfirmasi']);
            }

            // Notify via chat
            $chat = $order->user->chats()->where('subject', 'like', "%Pesanan #{$order->id}%")->first();
            if ($chat) {
                $chat->messages()->create([
                    'sender_role' => 'admin',
                    'message' => "[BOT] Pembayaran untuk Pesanan #{$order->id} telah dikirim. Status: menunggu konfirmasi admin.",
                    'is_bot' => true,
                ]);
            }

            return response()->json(['message' => 'Status pembayaran diperbarui. Menunggu konfirmasi admin.']);
        }

        return response()->json(['message' => 'Status tidak valid'], 400);
    }

    /**
     * Handle payment finish (redirect from Snap)
     */
    public function finish(Order $order)
    {
        return redirect()->route('orders.history')->with('success', 'Pembayaran berhasil dikirim! Status pesanan Anda menunggu konfirmasi admin.');
    }

    /**
     * Handle payment error (redirect from Snap)
     */
    public function error(Order $order)
    {
        return redirect()->route('payment.show', $order->id)->with('error', 'Pembayaran gagal. Silakan coba lagi.');
    }

    /**
     * Handle payment pending (redirect from Snap)
     */
    public function pending(Order $order)
    {
        return redirect()->route('payment.show', $order->id)->with('info', 'Pembayaran sedang diproses...');
    }

    /**
     * Handle Midtrans webhook notification (server-to-server)
     */
    public function callback(Request $request)
    {
        MidtransService::configure();

        $json = $request->getContent();
        $notification = json_decode($json);

        if (!$notification || !isset($notification->order_id)) {
            return response()->json(['message' => 'Invalid notification'], 400);
        }

        try {
            $statusData = MidtransService::handleNotification($notification->order_id);
            $orderId = (int) explode('-', $notification->order_id)[1];
            $order = Order::findOrFail($orderId);
            $transaction = $order->transaction;

            if (!$transaction) {
                return response()->json(['message' => 'Transaction not found'], 404);
            }

            $txStatus = $statusData['transaction_status'];

            // Update transaction based on Midtrans status
            if ($txStatus == 'capture' || $txStatus == 'settlement') {
                $transaction->update([
                    'payment_status' => 'paid',
                    'payment_method' => $statusData['payment_type'],
                    'paid_at' => now(),
                ]);

                // Update order status
                $order->update(['status' => 'Diproses']);

                // Send notification
                $chat = $order->user->chats()->where('subject', 'like', "%Pesanan #{$order->id}%")->first();
                if ($chat) {
                    $chat->messages()->create([
                        'sender_role' => 'admin',
                        'message' => "[BOT] Pembayaran untuk Pesanan #{$order->id} berhasil diterima via Midtrans. Pesanan sedang diproses.",
                        'is_bot' => true,
                    ]);
                }
            } elseif ($txStatus == 'pending') {
                $transaction->update([
                    'payment_status' => 'pending',
                    'payment_method' => $statusData['payment_type'],
                ]);
            } elseif (in_array($txStatus, ['deny', 'cancel', 'expire'])) {
                $transaction->update([
                    'payment_status' => 'failed',
                    'payment_method' => $statusData['payment_type'],
                ]);

                // Send notification
                $chat = $order->user->chats()->where('subject', 'like', "%Pesanan #{$order->id}%")->first();
                if ($chat) {
                    $chat->messages()->create([
                        'sender_role' => 'admin',
                        'message' => "[BOT] Pembayaran untuk Pesanan #{$order->id} gagal atau dibatalkan. Silakan coba lagi.",
                        'is_bot' => true,
                    ]);
                }
            }

            return response()->json(['message' => 'Webhook processed']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error: ' . $e->getMessage()], 500);
        }
    }
}
