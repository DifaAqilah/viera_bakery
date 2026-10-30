<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Admin\ChatController as AdminChatController;
use App\Http\Controllers\Admin\PelangganController;

use App\Http\Controllers\HomeController;

// ─── Midtrans Webhook (tidak perlu auth) ────────────────────────────────────

Route::post('/payment/callback', [PaymentController::class, 'callback'])->name('payment.callback');

// ─── Halaman Publik ─────────────────────────────────────────────────────────

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');

// ─── Auth ────────────────────────────────────────────────────────────────────

Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// ─── Area Pelanggan (auth + role pelanggan) ──────────────────────────────────

Route::middleware(['auth', 'is_pelanggan'])->group(function () {

    // Keranjang
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
    Route::post('/cart/item/{item}/update', [CartController::class, 'update'])->name('cart.update');
    Route::post('/cart/item/{item}/update-qty', [CartController::class, 'updateQuantity'])->name('cart.updateQty');
    Route::delete('/cart/item/{item}', [CartController::class, 'remove'])->name('cart.remove');

    // Pemesanan
    Route::get('/checkout', [OrderController::class, 'checkout'])->name('checkout');
    Route::post('/order/place', [OrderController::class, 'place'])->name('order.place');
    Route::get('/order/payment', [OrderController::class, 'showPayment'])->name('order.payment');
    Route::post('/order/finalize', [OrderController::class, 'finalizeOrder'])->name('order.finalize');
    Route::post('/order/upload-proof', [OrderController::class, 'uploadProofSession'])->name('order.uploadProofSession');
    Route::get('/orders/thanks/{order}', [OrderController::class, 'thanks'])->name('orders.thanks');
    Route::get('/orders/history', [OrderController::class, 'history'])->name('orders.history');

    // Pembayaran (existing order-based)
    Route::get('/payment/{order}', [PaymentController::class, 'show'])->name('payment.show');
    Route::post('/payment/{order}/upload-proof', [PaymentController::class, 'uploadProof'])->name('payment.uploadProof');
    Route::post('/payment/{order}/place-onsite', [PaymentController::class, 'placeOnsite'])->name('payment.placeOnsite');
    Route::post('/payment/{order}/place-qris', [PaymentController::class, 'placeQris'])->name('payment.placeQris');
    Route::post('/payment/{order}/update-status', [PaymentController::class, 'updateStatus'])->name('payment.updateStatus');
    Route::get('/payment/{order}/finish', [PaymentController::class, 'finish'])->name('payment.finish');
    Route::get('/payment/{order}/error', [PaymentController::class, 'error'])->name('payment.error');
    Route::get('/payment/{order}/pending', [PaymentController::class, 'pending'])->name('payment.pending');

    // Transaksi
    Route::get('/transactions/{transaction}', [TransactionController::class, 'show'])->name('transactions.show');
    Route::post('/transactions/{transaction}/cancel', [TransactionController::class, 'cancel'])->name('transactions.cancel');

    // Chat
    Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
    Route::post('/chat/send', [ChatController::class, 'sendMessage'])->name('chat.send');
    Route::get('/chat/messages', [ChatController::class, 'fetchMessages'])->name('chat.messages');
    Route::get('/chat/unread-count', [ChatController::class, 'unreadCount'])->name('chat.unreadCount');

    // Profil (tanpa upload foto)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');
});

// ─── Area Admin ──────────────────────────────────────────────────────────────

Route::prefix('admin')->name('admin.')->middleware(['auth', 'is_admin'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Kategori (CRUD)
    Route::resource('/categories', AdminCategoryController::class)->names('categories');

    // Produk (CRUD)
    Route::resource('/products', AdminProductController::class)->names('products');

    // Pelanggan
    Route::get('/pelanggan', [PelangganController::class, 'index'])->name('pelanggan.index');

    // Pesanan
    Route::get('/orders/new-count', [AdminOrderController::class, 'newCount'])->name('orders.newCount');
    Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.updateStatus');
    Route::post('/orders/{order}/payment-status', [AdminOrderController::class, 'updatePaymentStatus'])->name('orders.updatePaymentStatus');

    // Laporan
    Route::get('/reports', [AdminReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export', [AdminReportController::class, 'export'])->name('reports.export');

    // Chat
    Route::get('/chat', [AdminChatController::class, 'index'])->name('chat.index');
    Route::get('/chat/unread-count', [AdminChatController::class, 'unreadCount'])->name('chat.unreadCount');
    Route::get('/chat/{chat}', [AdminChatController::class, 'show'])->name('chat.show');
    Route::post('/chat/{chat}/reply', [AdminChatController::class, 'reply'])->name('chat.reply');
    Route::get('/chat/{chat}/messages', [AdminChatController::class, 'fetchMessages'])->name('chat.messages');
});
