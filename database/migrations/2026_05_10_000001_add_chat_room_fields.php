<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add fields to chats table
        Schema::table('chats', function (Blueprint $table) {
            $table->text('last_message')->nullable()->after('subject');
            $table->timestamp('last_message_at')->nullable()->after('last_message');
        });

        // Add fields to chat_messages table
        Schema::table('chat_messages', function (Blueprint $table) {
            $table->string('sender_type', 20)->default('customer')->after('chat_id');
            $table->unsignedBigInteger('order_id')->nullable()->after('sender_type');
            $table->boolean('is_auto')->default(false)->after('is_bot');
            $table->timestamp('read_by_customer_at')->nullable()->after('is_auto');
            $table->timestamp('read_by_admin_at')->nullable()->after('read_by_customer_at');
        });

        // Migrate existing sender_role data to sender_type
        DB::table('chat_messages')->where('sender_role', 'pelanggan')->update(['sender_type' => 'customer']);
        DB::table('chat_messages')->where('sender_role', 'admin')->where('is_bot', false)->update(['sender_type' => 'admin']);
        DB::table('chat_messages')->where('is_bot', true)->update(['sender_type' => 'bot', 'is_auto' => true]);

        // Update last_message for existing chats
        $chats = DB::table('chats')->get();
        foreach ($chats as $chat) {
            $lastMsg = DB::table('chat_messages')
                ->where('chat_id', $chat->id)
                ->orderBy('created_at', 'desc')
                ->first();
            if ($lastMsg) {
                DB::table('chats')->where('id', $chat->id)->update([
                    'last_message' => \Illuminate\Support\Str::limit($lastMsg->message, 100),
                    'last_message_at' => $lastMsg->created_at,
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('chat_messages', function (Blueprint $table) {
            $table->dropColumn(['sender_type', 'order_id', 'is_auto', 'read_by_customer_at', 'read_by_admin_at']);
        });

        Schema::table('chats', function (Blueprint $table) {
            $table->dropColumn(['last_message', 'last_message_at']);
        });
    }
};
