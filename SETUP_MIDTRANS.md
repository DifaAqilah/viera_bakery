# Setup Midtrans untuk Pembayaran

Dokumen ini menjelaskan cara mengintegrasikan Midtrans untuk sistem pembayaran online.

## 1. Install Midtrans SDK

Jalankan command berikut untuk install Midtrans PHP SDK via Composer:

```bash
composer require midtrans/midtrans-php
```

## 2. Daftar di Midtrans

1. Buka [https://dashboard.midtrans.com](https://dashboard.midtrans.com)
2. Buat akun dan login
3. Setelah login, Anda akan mendapatkan:
   - **Server Key** (gunakan untuk backend)
   - **Client Key** (gunakan untuk frontend)
   - **Merchant ID**

## 3. Konfigurasi Environment Variables

Buka file `.env` dan tambahkan konfigurasi Midtrans:

```env
# Midtrans Configuration
MIDTRANS_SERVER_KEY=your_server_key_here
MIDTRANS_CLIENT_KEY=your_client_key_here
MIDTRANS_IS_PRODUCTION=false
```

### Penjelasan:
- **MIDTRANS_SERVER_KEY**: Server key dari Midtrans (untuk backend processing)
- **MIDTRANS_CLIENT_KEY**: Client key dari Midtrans (untuk frontend Snap integration)
- **MIDTRANS_IS_PRODUCTION**: Set ke `false` untuk sandbox/testing, `true` untuk production

## 4. Alur Pembayaran

### A. Proses Checkout ke Payment

1. Pelanggan melengkapi form checkout dengan:
   - Metode pengiriman (Ambil sendiri / Diantar)
   - Nomor telepon
   - Tanggal pengiriman (jika diantar)
   - Alamat pengiriman (jika diantar)
   - Catatan pesanan (opsional)

2. Data disimpan ke tabel `orders`
3. Sistem otomatis menambahkan ongkir Rp 25.000 jika pilih "Diantar"
4. User diarahkan ke halaman pembayaran (`/payment/{order}`)

### B. Halaman Pembayaran

1. Halaman menampilkan ringkasan pembayaran dengan Midtrans Snap
2. Pelanggan klik tombol "Lanjutkan Pembayaran"
3. Pop-up Midtrans Snap muncul dengan berbagai metode pembayaran:
   - Kartu Kredit/Debit
   - Transfer Bank
   - E-Wallet (GCash, OVO, Dana, dll)
   - Cicilan
   - dll

### C. Proses Pembayaran

1. Pelanggan memilih metode pembayaran dan melakukan transaksi
2. Midtrans memproses pembayaran
3. Berdasarkan status:
   - **Success**: User diarahkan ke halaman terima kasih
   - **Pending**: User diarahkan ke halaman pembayaran (masih menunggu)
   - **Error**: User diarahkan kembali ke halaman pembayaran dengan pesan error

### D. Webhook Notification

1. Midtrans mengirimkan notifikasi ke endpoint `/payment/callback`
2. Sistem memverifikasi notifikasi
3. Update status transaksi di database:
   - `payment_status` → `paid`, `pending`, atau `failed`
   - `payment_method` → metode yang digunakan
   - `paid_at` → waktu pembayaran (jika berhasil)
4. Update status order → `Diproses` (jika pembayaran berhasil)
5. Kirim notifikasi ke pelanggan via chat

## 5. Metode Pembayaran yang Didukung (Sandbox)

### Sandbox Testing Credentials

Untuk testing di sandbox, gunakan credential test berikut:

#### Kartu Kredit
- **Nomor Kartu**: 4011 1111 1111 1112
- **CVV**: 123
- **Expired**: 12/25
- **OTP**: 123456

#### Transfer Bank
- Gunakan nomor rekening virtual yang disediakan Midtrans

#### E-Wallet
- Sistem akan mengarahkan ke halaman simulasi

## 6. Database Structure

### Orders Table
```sql
ALTER TABLE orders ADD COLUMN delivery_date DATE NULLABLE;
ALTER TABLE orders ADD COLUMN customer_phone VARCHAR(20) NULLABLE;
ALTER TABLE orders ADD COLUMN shipping_cost DECIMAL(12,2) DEFAULT 0;
```

### Transactions Table
```sql
-- Sudah ada, struktur:
- id (primary key)
- order_id (foreign key)
- transaction_code (unique)
- amount (total pembayaran)
- payment_method (nullable, updated by webhook)
- payment_status (enum: pending, paid, failed)
- paid_at (timestamp, nullable)
- created_at, updated_at
```

## 7. Endpoints

### Public Routes
- `POST /payment/callback` - Webhook dari Midtrans (tidak perlu auth)

### Protected Routes (require auth)
- `GET /payment/{order}` - Halaman pembayaran
- `GET /payment/{order}/finish` - Redirect setelah pembayaran berhasil
- `GET /payment/{order}/error` - Redirect jika pembayaran gagal
- `GET /payment/{order}/pending` - Redirect jika pembayaran pending

## 8. Troubleshooting

### Error: "Failed to generate payment token"
- Pastikan `MIDTRANS_SERVER_KEY` dan `MIDTRANS_CLIENT_KEY` sudah diisi dengan benar di `.env`
- Pastikan Midtrans PHP SDK sudah terinstall: `composer require midtrans/midtrans-php`
- Pastikan mode sandbox/production sesuai dengan konfigurasi di Midtrans dashboard

### Webhook tidak terima notifikasi
- Pastikan endpoint `/payment/callback` bisa diakses dari internet (di production)
- Konfigurasi URL Notification di Midtrans dashboard

### Pembayaran tidak terupdate
- Lihat log di `storage/logs/laravel.log`
- Pastikan database migration sudah dijalankan: `php artisan migrate`

## 9. Langkah Berikutnya

Setelah setup:

1. **Run Migrations**
   ```bash
   php artisan migrate
   ```

2. **Test dengan Sandbox**
   - Gunakan credential sandbox di atas
   - Test seluruh flow dari checkout hingga pembayaran

3. **Setup Production** (saat siap go-live)
   - Ubah `MIDTRANS_IS_PRODUCTION=true`
   - Gunakan production keys dari Midtrans
   - Test dengan pembayaran nyata (jika diperlukan)

## 10. Support

- Dokumentasi Midtrans: https://docs.midtrans.com
- Dashboard Midtrans: https://dashboard.midtrans.com
- Support Chat: https://dashboard.midtrans.com (ada support chat di dashboard)

---

**Note**: Dokumen ini dibuat untuk integrasi Midtrans Snap. Jika ada perubahan atau update dari Midtrans, silakan kunjungi dokumentasi resmi mereka.
