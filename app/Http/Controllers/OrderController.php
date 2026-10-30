<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function checkout()
    {
        $user = Auth::user();
        $cart = Cart::where('user_id', $user->id)->firstOrFail();
        $items = $cart->items()->with('product')->get();

        if ($items->isEmpty()) {
            return redirect()->route('cart.index')->withErrors('Keranjang kosong.');
        }

        return view('orders.checkout', compact('cart', 'items'));
    }

    /**
     * Save checkout data to session, redirect to payment page.
     * Does NOT create order or clear cart.
     */
    public function place(Request $request)
    {
        $data = $request->validate([
            'delivery_method' => 'required|in:self_pickup,delivery',
            'delivery_address' => 'required_if:delivery_method,delivery|nullable|string',
            'delivery_date' => 'required_if:delivery_method,delivery|nullable|date|after:today',
            'pickup_date' => 'required_if:delivery_method,self_pickup|nullable|date|after:today',
            'customer_phone' => 'required|string|max:20',
            'note' => 'nullable|string',
        ]);

        $user = Auth::user();
        $cart = Cart::where('user_id', $user->id)->firstOrFail();
        $items = $cart->items()->with('product')->get();

        if ($items->isEmpty()) {
            return redirect()->route('cart.index')->withErrors('Keranjang kosong.');
        }

        // Check stock
        foreach ($items as $it) {
            $product = $it->product;
            if (!$product) {
                return redirect()->route('cart.index')->withErrors('Produk tidak ditemukan: ' . $it->product_id);
            }
            if ($it->quantity > $product->stock) {
                return redirect()->route('cart.index')->withErrors('Stok tidak cukup untuk produk: ' . $product->name . '. Tersedia: ' . $product->stock);
            }
        }

        $subtotal = $items->sum(fn($i) => $i->quantity * $i->price);
        $shippingCost = $data['delivery_method'] === 'delivery' ? 25000 : 0;
        $total = $subtotal + $shippingCost;

        // Save checkout data to session (not as order yet)
        $checkoutItems = $items->map(fn($i) => [
            'product_id' => $i->product_id,
            'product_name' => $i->product->name ?? '—',
            'product_image' => $i->product->image ?? null,
            'quantity' => $i->quantity,
            'price' => $i->price,
            'subtotal' => $i->quantity * $i->price,
        ])->toArray();

        session([
            'checkout_data' => [
                'delivery_method' => $data['delivery_method'],
                'delivery_address' => $data['delivery_method'] === 'delivery' ? $data['delivery_address'] : null,
                'delivery_date' => $data['delivery_method'] === 'delivery' ? $data['delivery_date'] : null,
                'pickup_date' => $data['delivery_method'] === 'self_pickup' ? $data['pickup_date'] : null,
                'customer_phone' => $data['customer_phone'],
                'note' => $data['note'] ?? null,
                'shipping_cost' => $shippingCost,
                'subtotal' => $subtotal,
                'total' => $total,
                'items' => $checkoutItems,
            ],
        ]);

        return redirect()->route('order.payment');
    }

    /**
     * Show payment page from session data (no order created yet)
     */
    public function showPayment()
    {
        $checkout = session('checkout_data');

        if (!$checkout) {
            return redirect()->route('checkout')->withErrors('Data checkout tidak ditemukan. Silakan ulangi proses checkout.');
        }

        return view('orders.payment', compact('checkout'));
    }

    /**
     * Finalize order from payment page (AJAX)
     */
    public function finalizeOrder(Request $request)
    {
        $checkout = session('checkout_data');
        if (!$checkout) {
            return response()->json(['message' => 'Data checkout tidak ditemukan.'], 422);
        }

        $data = $request->validate([
            'payment_method' => 'required|in:onsite,qris',
        ]);

        $user = Auth::user();
        $cart = Cart::where('user_id', $user->id)->first();

        // Re-check stock
        if ($cart) {
            $cartItems = $cart->items()->with('product')->get();
            foreach ($cartItems as $it) {
                if ($it->product && $it->quantity > $it->product->stock) {
                    return response()->json([
                        'message' => 'Stok tidak cukup untuk produk: ' . $it->product->name,
                    ], 422);
                }
            }
        }

        // Determine payment status based on method + delivery
        if ($data['payment_method'] === 'onsite') {
            if ($checkout['delivery_method'] === 'self_pickup') {
                $paymentStatus = 'Bayar di Tempat';
            } else {
                $paymentStatus = 'Bayar saat Diterima';
            }
            $orderStatus = 'Menunggu Diproses';
        } else {
            $paymentStatus = 'Menunggu Konfirmasi';
            $orderStatus = 'Menunggu Konfirmasi';
        }

        // Create order
        $order = Order::create([
            'user_id' => $user->id,
            'total_amount' => $checkout['total'],
            'delivery_method' => $checkout['delivery_method'],
            'delivery_address' => $checkout['delivery_address'],
            'delivery_date' => $checkout['delivery_date'],
            'pickup_date' => $checkout['pickup_date'],
            'customer_phone' => $checkout['customer_phone'],
            'shipping_cost' => $checkout['shipping_cost'],
            'note' => $checkout['note'],
            'status' => $orderStatus,
            'payment_method' => $data['payment_method'],
            'payment_status' => $paymentStatus,
            'payment_proof' => $request->has('has_proof') ? session('uploaded_proof_path') : null,
        ]);

        // Create order items
        foreach ($checkout['items'] as $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'price' => $item['price'],
                'subtotal' => $item['subtotal'],
            ]);

            // Decrement stock
            $prod = \App\Models\Product::find($item['product_id']);
            if ($prod) {
                $prod->decrement('stock', $item['quantity']);
            }
        }

        // Create transaction
        Transaction::create([
            'order_id' => $order->id,
            'transaction_code' => Str::upper(Str::random(8)),
            'amount' => $checkout['total'],
            'payment_method' => $data['payment_method'],
            'payment_status' => 'pending',
        ]);

        // Clear cart
        if ($cart) {
            $cart->items()->delete();
        }

        // Clear session
        session()->forget(['checkout_data', 'uploaded_proof_path']);

        // Send bot notification via ChatService (single room per user)
        \App\Services\ChatService::notifyOrderCreated($order);

        // Format date for response
        $dateLabel = $checkout['delivery_method'] === 'self_pickup'
            ? ($checkout['pickup_date'] ? \Carbon\Carbon::parse($checkout['pickup_date'])->format('d M Y') : '-')
            : ($checkout['delivery_date'] ? \Carbon\Carbon::parse($checkout['delivery_date'])->format('d M Y') : '-');

        return response()->json([
            'message' => 'Pesanan berhasil dibuat.',
            'order' => [
                'id' => $order->id,
                'payment_method' => $data['payment_method'] === 'onsite' ? 'Onsite' : 'QRIS',
                'payment_status' => $paymentStatus,
                'total_amount' => $order->total_amount,
                'created_at' => $order->created_at->format('d M Y'),
                'delivery_method' => $checkout['delivery_method'],
            ],
        ]);
    }

    /**
     * Upload proof during payment (session-based, AJAX)
     */
    public function uploadProofSession(Request $request)
    {
        $request->validate([
            'payment_proof' => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $path = $request->file('payment_proof')->store('payment_proofs', 'public');
        session(['uploaded_proof_path' => $path]);

        return response()->json([
            'message' => 'Bukti pembayaran berhasil diupload.',
            'payment_proof' => $path,
        ]);
    }

    public function thanks(Order $order)
    {
        return view('orders.thanks', compact('order'));
    }

    public function history()
    {
        $orders = Auth::user()->orders()->with('items.product', 'transaction')
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        return view('orders.history', compact('orders'));
    }
}
