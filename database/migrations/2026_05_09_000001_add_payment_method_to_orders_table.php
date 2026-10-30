<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Tambah kolom payment_method dan payment_status ke orders
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'payment_method')) {
                $table->string('payment_method')->nullable()->after('payment_proof');
            }
            if (!Schema::hasColumn('orders', 'payment_status')) {
                $table->string('payment_status')->nullable()->after('payment_method');
            }
        });

        // Tambah 'Menunggu Diproses' ke enum status
        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('Menunggu Konfirmasi','Menunggu Diproses','Diproses','Siap Ambil','Dalam Perjalanan','Selesai','Dibatalkan') DEFAULT 'Menunggu Konfirmasi'");
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['payment_method', 'payment_status']);
        });

        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('Menunggu Konfirmasi','Diproses','Siap Ambil','Dalam Perjalanan','Selesai','Dibatalkan') DEFAULT 'Menunggu Konfirmasi'");
    }
};
