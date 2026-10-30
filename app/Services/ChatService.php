<?php

namespace App\Services;

use App\Models\Chat;
use App\Models\ChatMessage;
use Illuminate\Support\Str;

class ChatService
{
    /**
     * Get or create a single chat room for a user.
     */
    public static function getOrCreateRoom(int $userId): Chat
    {
        return Chat::firstOrCreate(
            ['user_id' => $userId],
            ['subject' => null]
        );
    }

    /**
     * Send a bot message to a user's chat room.
     */
    public static function sendBotMessage(int $userId, string $message, ?int $orderId = null): ChatMessage
    {
        $chat = self::getOrCreateRoom($userId);

        $msg = $chat->messages()->create([
            'sender_role' => 'admin',
            'sender_type' => 'bot',
            'message' => $message,
            'is_bot' => true,
            'is_auto' => true,
            'order_id' => $orderId,
        ]);

        self::updateRoomLastMessage($chat, $message, 'bot');

        return $msg;
    }

    /**
     * Send an admin message.
     */
    public static function sendAdminMessage(Chat $chat, string $message): ChatMessage
    {
        $msg = $chat->messages()->create([
            'sender_role' => 'admin',
            'sender_type' => 'admin',
            'message' => $message,
            'is_bot' => false,
            'is_auto' => false,
        ]);

        self::updateRoomLastMessage($chat, $message, 'admin');

        return $msg;
    }

    /**
     * Send a customer message.
     */
    public static function sendCustomerMessage(Chat $chat, string $message): ChatMessage
    {
        $msg = $chat->messages()->create([
            'sender_role' => 'pelanggan',
            'sender_type' => 'customer',
            'message' => $message,
            'is_bot' => false,
            'is_auto' => false,
        ]);

        self::updateRoomLastMessage($chat, $message, 'customer');

        return $msg;
    }

    /**
     * Update room's last_message fields.
     */
    public static function updateRoomLastMessage(Chat $chat, string $message, string $senderType): void
    {
        $chat->update([
            'last_message' => Str::limit($message, 100),
            'last_message_at' => now(),
        ]);
    }

    /**
     * Count unread messages for customer (messages from admin + bot).
     */
    public static function getCustomerUnreadCount(int $userId): int
    {
        $chat = Chat::where('user_id', $userId)->first();
        if (!$chat) return 0;

        return $chat->messages()
            ->whereIn('sender_type', ['admin', 'bot'])
            ->whereNull('read_by_customer_at')
            ->count();
    }

    /**
     * Count total unread customer messages across all rooms (for admin badge).
     */
    public static function getAdminTotalUnreadCount(): int
    {
        return ChatMessage::where('sender_type', 'customer')
            ->whereNull('read_by_admin_at')
            ->count();
    }

    /**
     * Count unread customer messages for a specific room.
     */
    public static function getAdminUnreadCountForRoom(int $chatId): int
    {
        return ChatMessage::where('chat_id', $chatId)
            ->where('sender_type', 'customer')
            ->whereNull('read_by_admin_at')
            ->count();
    }

    /**
     * Mark all admin/bot messages as read by customer.
     */
    public static function markReadByCustomer(Chat $chat): void
    {
        $chat->messages()
            ->whereIn('sender_type', ['admin', 'bot'])
            ->whereNull('read_by_customer_at')
            ->update(['read_by_customer_at' => now()]);
    }

    /**
     * Mark all customer messages as read by admin.
     */
    public static function markReadByAdmin(Chat $chat): void
    {
        $chat->messages()
            ->where('sender_type', 'customer')
            ->whereNull('read_by_admin_at')
            ->update(['read_by_admin_at' => now()]);
    }

    /**
     * Generate bot message for order created.
     */
    public static function notifyOrderCreated(\App\Models\Order $order): void
    {
        $total = 'Rp ' . number_format($order->total_amount, 0, ',', '.');

        if ($order->payment_method === 'qris') {
            $msg = "Pesanan Anda berhasil dibuat!\nNo. Pesanan: #{$order->id}\nStatus Pembayaran: Menunggu Konfirmasi\nTotal: {$total}\n\nBukti pembayaran Anda telah diterima dan menunggu konfirmasi admin.\n\n[Ini adalah pesan otomatis]";
        } elseif ($order->delivery_method === 'self_pickup') {
            $msg = "Pesanan Anda berhasil dibuat!\nNo. Pesanan: #{$order->id}\nStatus: Menunggu Diproses\nTotal: {$total}\n\nSilakan lakukan pembayaran langsung saat mengambil pesanan di toko.\n\n[Ini adalah pesan otomatis]";
        } else {
            $msg = "Pesanan Anda berhasil dibuat!\nNo. Pesanan: #{$order->id}\nStatus: Menunggu Diproses\nTotal: {$total}\n\nSilakan lakukan pembayaran saat pesanan diterima di lokasi tujuan.\n\n[Ini adalah pesan otomatis]";
        }

        self::sendBotMessage($order->user_id, $msg, $order->id);
    }

    /**
     * Generate bot message for order status change.
     */
    public static function notifyOrderStatusChanged(\App\Models\Order $order, string $oldStatus, string $newStatus): void
    {
        if ($oldStatus === $newStatus) return;

        $messages = [
            'Menunggu Konfirmasi' => "Pembayaran Anda sedang menunggu konfirmasi admin.\nKami akan segera memeriksa bukti pembayaran Anda.\n\n[Ini adalah pesan otomatis]",
            'Menunggu Diproses' => "Pesanan Anda sudah masuk dan menunggu diproses.\nKami akan segera menyiapkan pesanan Anda.\n\n[Ini adalah pesan otomatis]",
            'Diproses' => "Pesanan Anda sedang diproses.\nMohon tunggu, pesanan sedang kami siapkan.\n\n[Ini adalah pesan otomatis]",
            'Siap Ambil' => "Pesanan Anda sudah siap diambil!\nSilakan datang ke toko Viera Bakery sesuai waktu pengambilan.\n\n[Ini adalah pesan otomatis]",
            'Dalam Perjalanan' => "Pesanan Anda sedang dikirim.\nKurir sedang dalam perjalanan menuju lokasi tujuan.\n\n[Ini adalah pesan otomatis]",
            'Selesai' => "Pesanan Anda telah selesai.\nTerima kasih telah memesan di Viera Bakery. Selamat menikmati!\n\n[Ini adalah pesan otomatis]",
            'Dibatalkan' => "Pesanan Anda telah dibatalkan.\nJika ada pertanyaan, silakan hubungi admin melalui chat ini.\n\n[Ini adalah pesan otomatis]",
        ];

        $msg = $messages[$newStatus] ?? "Status pesanan #{$order->id} diubah menjadi: {$newStatus}.\n\n[Ini adalah pesan otomatis]";

        self::sendBotMessage($order->user_id, $msg, $order->id);
    }
}
