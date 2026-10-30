<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->enum('delivery_method', ['self_pickup', 'delivery'])->default('self_pickup');
            $table->text('delivery_address')->nullable();
            $table->enum('status', ['Menunggu Konfirmasi','Diproses','Siap Ambil','Dalam Perjalanan','Selesai','Dibatalkan'])->default('Menunggu Konfirmasi');
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
