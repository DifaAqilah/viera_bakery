# 🔧 PERBAIKAN HALAMAN PEMBAYARAN - Panduan Setup

## ✅ Masalah yang Sudah Diperbaiki

### 1. **Midtrans SDK Tidak Terinstall** ❌ → ✅
- Ditambahkan ke `composer.json`: `"midtrans/midtrans-php": "^2.4"`
- Sudah dijalankan: `composer require midtrans/midtrans-php`
- Package versi 2.6.2 sudah terinstall

### 2. **Environment Variables Belum Dikonfigurasi** ❌ → ✅
- Updated `.env.example` dengan Midtrans keys
- `.env` sudah memiliki placeholder untuk Midtrans configuration

### 3. **CSRF Token Validation** ❌ → ✅
- Added CSRF exception untuk `/payment/callback` di `bootstrap/app.php`
- Webhook dari Midtrans sekarang tidak akan reject karena CSRF

### 4. **Missing Configuration Validation** ❌ → ✅
- PaymentController sekarang check apakah Midtrans keys sudah dikonfigurasi
- Error message yang jelas jika keys masih placeholder

---

## 📋 Langkah-Langkah Setup

### Step 1: Run Database Migrations
```bash
php artisan migrate
```

Ini akan membuat field baru di database:
- `users`: `phone_number`, `profile_photo`
- `orders`: `delivery_date`, `customer_phone`, `shipping_cost`

### Step 2: Konfigurasi Midtrans (Sandbox untuk Testing)

1. Buka [https://dashboard.midtrans.com](https://dashboard.midtrans.com)
2. Daftar akun jika belum punya
3. Login ke dashboard
4. Buka menu **Settings > Access Keys**
5. Copy **Server Key** dan **Client Key** dari sandbox
6. Edit file `.env`:

```env
MIDTRANS_SERVER_KEY=SB-Mid-xxxxxxxxxxxxxxxxxxxxxxxx
MIDTRANS_CLIENT_KEY=SB-Mid-xxxxxxxxxxxxxxxxxxxxxxxx
MIDTRANS_IS_PRODUCTION=false
```

### Step 3: Setup Symbolic Link untuk Storage (untuk foto profile)
```bash
php artisan storage:link
```

Ini membuat symbolic link dari `storage/app/public` ke `public/storage`

### Step 4: Clear Cache
```bash
php artisan config:clear
php artisan cache:clear
```

---

## 🧪 Testing Halaman Pembayaran

### Alur Testing:

1. **Login** sebagai pelanggan
2. **Add produk ke cart** dan ke checkout
3. **Isi form checkout**:
   - Pilih "Diantar" untuk test dengan ongkir
   - Isi nomor telepon
   - Pilih tanggal pengiriman
   - Isi alamat pengiriman
4. **Click "Buat Pesanan"** → Akan redirect ke halaman pembayaran
5. **Click "Lanjutkan Pembayaran"** → Pop-up Midtrans Snap muncul
6. **Pilih metode pembayaran** (untuk sandbox, gunakan test card)

### Sandbox Test Card:
```
Nomor Kartu  : 4011 1111 1111 1112
Nama         : Nama Apapun
CVV          : 123
Expired      : 12/25
OTP          : 123456
```

### Expected Flow:
- ✅ Halaman pembayaran tampil dengan ringkasan pesanan
- ✅ Midtrans Snap pop-up muncul saat klik "Lanjutkan Pembayaran"
- ✅ Setelah pembayaran (finish/pending/error), redirect sesuai status
- ✅ Order status terupdate (jika berhasil → "Diproses")
- ✅ Chat notification terkirim ke pelanggan

---

## 🚨 Troubleshooting

### Error: "Midtrans belum dikonfigurasi dengan benar"
**Penyebab**: MIDTRANS_SERVER_KEY atau MIDTRANS_CLIENT_KEY masih placeholder

**Solusi**:
1. Pastikan sudah daftar di [https://dashboard.midtrans.com](https://dashboard.midtrans.com)
2. Copy real Server Key dan Client Key dari dashboard
3. Update `.env` dengan nilai yang sebenarnya
4. Run `php artisan config:clear`

### Error: "Call to undefined function Midtrans\Snap::getSnapToken()"
**Penyebab**: Midtrans SDK belum terinstall

**Solusi**:
```bash
composer require midtrans/midtrans-php
php artisan config:clear
```

### Error: "Class 'Midtrans\Config' not found"
**Penyebab**: Autoloader belum di-regenerate setelah install package

**Solusi**:
```bash
composer dump-autoload
```

### Halaman pembayaran blank atau error 500
**Penyebab**: Banyak kemungkinan (lihat error log)

**Solusi**:
1. Check log: `tail -f storage/logs/laravel.log`
2. Verify Midtrans keys di `.env`
3. Run migrations jika belum: `php artisan migrate`
4. Clear cache: `php artisan config:clear && php artisan cache:clear`

### Webhook dari Midtrans tidak terproses
**Untuk Production saja** - pastikan:
1. URL Notification di Midtrans dashboard sudah set ke: `https://yourdomain.com/payment/callback`
2. Server Key sudah benar dan match antara `.env` dan Midtrans dashboard
3. Check log untuk melihat error dari webhook

---

## 📁 Files yang Sudah Dimodifikasi/Dibuat

### Modified:
- ✅ `composer.json` - Ditambah Midtrans package
- ✅ `.env.example` - Ditambah Midtrans config
- ✅ `.env` - Sudah punya Midtrans placeholder
- ✅ `bootstrap/app.php` - CSRF exception untuk `/payment/callback`
- ✅ `app/Http/Controllers/PaymentController.php` - Added validation
- ✅ `resources/views/payments/show.blade.php` - Halaman pembayaran
- ✅ `routes/web.php` - Payment routes sudah ada
- ✅ `config/services.php` - Midtrans config

### Database Changes:
```sql
-- Users table
ALTER TABLE users ADD COLUMN phone_number VARCHAR(20) NULLABLE;
ALTER TABLE users ADD COLUMN profile_photo VARCHAR(255) NULLABLE;

-- Orders table
ALTER TABLE orders ADD COLUMN delivery_date DATE NULLABLE;
ALTER TABLE orders ADD COLUMN customer_phone VARCHAR(20) NULLABLE;
ALTER TABLE orders ADD COLUMN shipping_cost DECIMAL(12,2) DEFAULT 0;
```

---

## ✨ Next Steps untuk Production

Ketika siap go-live:

1. **Setup Production Keys** di Midtrans
2. **Update `.env`**:
   ```env
   MIDTRANS_IS_PRODUCTION=true
   MIDTRANS_SERVER_KEY=Mid-xxxxxxxxxxxxx (production key)
   MIDTRANS_CLIENT_KEY=Mid-xxxxxxxxxxxxx (production key)
   ```

3. **Setup Webhook URL** di Midtrans Dashboard:
   - Settings > Notification & Redirection
   - HTTP Notification URL: `https://yourdomain.com/payment/callback`

4. **Test dengan real payment** (optional, jika diperlukan)

5. **Monitor error logs** di production

---

## 📞 Support

- **Midtrans Docs**: https://docs.midtrans.com
- **Midtrans Dashboard**: https://dashboard.midtrans.com
- **Laravel Docs**: https://laravel.com/docs
- **Local Development**: Gunakan sandbox mode untuk testing

---

**Status**: ✅ **Sudah Siap untuk Testing**

Semua konfigurasi sudah dalam place. Tinggal:
1. Run migrations
2. Setup Midtrans keys
3. Test flow pembayaran

Selamat! 🎉
