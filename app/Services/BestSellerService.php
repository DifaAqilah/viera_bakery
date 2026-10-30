<?php

namespace App\Services;

use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;

class BestSellerService
{
    /**
     * Get top-selling products based on valid (paid) transactions.
     *
     * @param array $filters  Optional filters:
     *   - 'date_from'    (string|null)  Start date
     *   - 'date_to'      (string|null)  End date
     *   - 'limit'        (int)          Number of products to return (default 5)
     *   - 'order_ids'    (array|null)   Specific order IDs to filter by
     * @return \Illuminate\Support\Collection  Collection of OrderItem aggregates
     */
    public static function get(array $filters = [])
    {
        $limit   = $filters['limit'] ?? 5;
        $dateFrom = $filters['date_from'] ?? null;
        $dateTo   = $filters['date_to'] ?? null;
        $orderIds = $filters['order_ids'] ?? null;

        $query = OrderItem::select(
                'product_id',
                DB::raw('SUM(quantity) as total_sold'),
                DB::raw('SUM(subtotal) as total_revenue')
            )
            ->with('product.category');

        // Filter: hanya dari pesanan dengan transaksi lunas (paid)
        if ($orderIds !== null) {
            // Jika order_ids sudah diberikan (sudah difilter sebelumnya), gunakan langsung
            $query->whereIn('order_id', $orderIds);
        } else {
            // Default: hanya pesanan yang transaksinya lunas
            $query->whereHas('order.transaction', function ($q) {
                $q->where('payment_status', 'paid');
            });

            // Filter tanggal berdasarkan tanggal pesanan
            if ($dateFrom) {
                $query->whereHas('order', function ($q) use ($dateFrom) {
                    $q->whereDate('created_at', '>=', $dateFrom);
                });
            }
            if ($dateTo) {
                $query->whereHas('order', function ($q) use ($dateTo) {
                    $q->whereDate('created_at', '<=', $dateTo);
                });
            }
        }

        return $query
            ->groupBy('product_id')
            ->orderByDesc('total_sold')
            ->orderByDesc('total_revenue')
            ->take($limit)
            ->get();
    }
}
