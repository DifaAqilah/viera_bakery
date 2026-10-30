<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Services\BestSellerService;

class HomeController extends Controller
{
    public function index()
    {
        // ── Best Seller otomatis dari service reusable ──
        $topProducts = BestSellerService::get(['limit' => 4]);

        // Konversi ke Product collection untuk tampilan beranda
        if ($topProducts->isNotEmpty()) {
            $productIds = $topProducts->pluck('product_id')->toArray();
            $bestSellers = Product::with('category')
                ->whereIn('id', $productIds)
                ->get()
                ->sortBy(function ($product) use ($productIds) {
                    return array_search($product->id, $productIds);
                })
                ->values();
        } else {
            $bestSellers = collect();
        }

        // Fallback: jika belum ada transaksi valid, tampilkan 4 produk terbaru
        if ($bestSellers->isEmpty()) {
            $bestSellers = Product::with('category')
                ->latest()
                ->take(4)
                ->get();
        }

        // Ambil kategori Hampers
        $hampersCategory = Category::where('slug', 'hampers')->first();

        // Ambil produk edisi hampers hanya dari kategori Hampers (4 produk)
        $hampers = Product::with('category')
            ->where('category_id', $hampersCategory?->id)
            ->where('stock', '>', 0)
            ->latest()
            ->take(4)
            ->get();

        // Kategori showcase untuk section foto
        $catPastry    = Category::where('slug', 'pastry')->first();
        $catKueKering = Category::where('slug', 'kue-kering')->first();
        $catBolu      = Category::where('slug', 'bolu')->first();

        return view('home', compact(
            'bestSellers', 'hampers', 'hampersCategory',
            'catPastry', 'catKueKering', 'catBolu'
        ));
    }
}
