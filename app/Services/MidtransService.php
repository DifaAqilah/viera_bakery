<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Transaction;

class MidtransService
{
    /**
     * Configure Midtrans credentials from config (backend only)
     */
    public static function configure(): void
    {
        \Midtrans\Config::$serverKey = config('services.midtrans.server_key');
        \Midtrans\Config::$isProduction = config('services.midtrans.is_production');
        \Midtrans\Config::$isSanitized = config('services.midtrans.is_sanitized', true);
        \Midtrans\Config::$is3ds = config('services.midtrans.is_3ds', true);
    }

    /**
     * Get the Midtrans Snap URL based on environment
     */
    public static function getSnapUrl(): string
    {
        return config('services.midtrans.is_production')
            ? 'https://app.midtrans.com/snap/snap.js'
            : 'https://app.sandbox.midtrans.com/snap/snap.js';
    }

    /**
     * Get client key for frontend use (safe to expose)
     */
    public static function getClientKey(): string
    {
        return config('services.midtrans.client_key');
    }

    /**
     * Generate Midtrans Snap Token for an order
     */
    public static function generateSnapToken(Order $order, Transaction $transaction): string
    {
        self::configure();

        $order->load('items.product', 'user');

        $transactionDetails = [
            'order_id' => 'ORDER-' . $order->id . '-' . time(),
            'gross_amount' => (int) $order->total_amount,
        ];

        $customerDetails = [
            'first_name' => $order->user->name,
            'email' => $order->user->email,
            'phone' => $order->customer_phone,
        ];

        $items = [];
        foreach ($order->items as $item) {
            $items[] = [
                'id' => 'PRODUCT-' . $item->product_id,
                'price' => (int) $item->price,
                'quantity' => (int) $item->quantity,
                'name' => substr($item->product->name ?? 'Product', 0, 50),
            ];
        }

        // Tambahkan shipping cost sebagai item jika ada
        if ($order->shipping_cost > 0) {
            $items[] = [
                'id' => 'SHIPPING',
                'price' => (int) $order->shipping_cost,
                'quantity' => 1,
                'name' => 'Ongkos Kirim',
            ];
        }

        $payload = [
            'transaction_details' => $transactionDetails,
            'customer_details' => $customerDetails,
            'item_details' => $items,
            'callbacks' => [
                'finish' => route('payment.finish', $order->id),
                'error' => route('payment.error', $order->id),
                'pending' => route('payment.pending', $order->id),
            ],
        ];

        try {
            return \Midtrans\Snap::getSnapToken($payload);
        } catch (\Exception $e) {
            throw new \RuntimeException('Gagal membuat token pembayaran: ' . $e->getMessage());
        }
    }

    /**
     * Verify and process Midtrans notification
     */
    public static function handleNotification(string $orderId): array
    {
        self::configure();

        $transactionStatus = \Midtrans\Transaction::status($orderId);

        return [
            'transaction_status' => $transactionStatus->transaction_status ?? null,
            'payment_type' => $transactionStatus->payment_type ?? 'midtrans',
            'fraud_status' => $transactionStatus->fraud_status ?? null,
        ];
    }
}
