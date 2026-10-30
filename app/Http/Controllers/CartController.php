<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $cart = Cart::firstOrCreate(['user_id' => $user->id]);
        $items = $cart->items()->with('product.category')->get();
        return view('cart.index', compact('cart', 'items'));
    }

    public function add(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $user = Auth::user();
        $cart = Cart::firstOrCreate(['user_id' => $user->id]);
        $product = Product::findOrFail($data['product_id']);

        $item = $cart->items()->where('product_id', $product->id)->first();
        if ($item) {
            $item->quantity += $data['quantity'];
            $item->price = $product->price;
            $item->save();
        } else {
            $cart->items()->create([
                'product_id' => $product->id,
                'quantity' => $data['quantity'],
                'price' => $product->price,
            ]);
        }

        return redirect()->route('cart.index')->with('success', 'Produk berhasil ditambahkan ke keranjang!');
    }

    public function remove(Request $request, CartItem $item)
    {
        // Pastikan item milik user yang sedang login
        $cart = Cart::where('user_id', Auth::id())->first();
        if (!$cart || $item->cart_id !== $cart->id) {
            abort(403, 'Anda tidak memiliki izin untuk menghapus item ini.');
        }

        $item->delete();

        return redirect()->route('cart.index')->with('success', 'Item berhasil dihapus dari keranjang.');
    }

    public function update(Request $request, CartItem $item)
    {
        $cart = Cart::where('user_id', Auth::id())->first();
        if (!$cart || $item->cart_id !== $cart->id) {
            abort(403, 'Anda tidak memiliki izin untuk mengubah item ini.');
        }

        $data = $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $product = $item->product()->first();
        if (! $product) {
            return back()->withErrors('Produk tidak ditemukan');
        }

        if ($data['quantity'] > $product->stock) {
            return back()->withErrors(['quantity' => 'Stok tidak mencukupi. Tersedia: ' . $product->stock]);
        }

        $item->quantity = $data['quantity'];
        $item->price = $product->price;
        $item->save();

        return back()->with('success', 'Jumlah item berhasil diperbarui.');
    }

    /**
     * AJAX update quantity for +/- controls
     */
    public function updateQuantity(Request $request, CartItem $item)
    {
        $cart = Cart::where('user_id', Auth::id())->first();
        if (!$cart || $item->cart_id !== $cart->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $data = $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $product = $item->product;
        if (!$product) {
            return response()->json(['message' => 'Produk tidak ditemukan'], 404);
        }

        if ($data['quantity'] > $product->stock) {
            return response()->json(['message' => 'Stok tidak mencukupi. Tersedia: ' . $product->stock], 422);
        }

        $item->quantity = $data['quantity'];
        $item->price = $product->price;
        $item->save();

        // Recalculate cart totals
        $allItems = $cart->items()->get();
        $totalItems = $allItems->sum('quantity');
        $totalPrice = $allItems->sum(fn($i) => $i->quantity * $i->price);

        return response()->json([
            'item_subtotal' => $item->quantity * $item->price,
            'item_qty' => $item->quantity,
            'total_items' => $totalItems,
            'total_price' => $totalPrice,
        ]);
    }
}
