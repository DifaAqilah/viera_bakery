<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\Order;
use App\Services\ChatService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChatController extends Controller
{
    /**
     * List chat rooms — 1 row per customer (use first room per user, matching firstOrCreate).
     */
    public function index()
    {
        // Use MIN(id) to match ChatService::getOrCreateRoom which uses firstOrCreate (oldest room)
        $primaryChatIds = Chat::select(DB::raw('MIN(id) as id'))
            ->groupBy('user_id')
            ->pluck('id');

        $chats = Chat::with('user')
            ->whereIn('id', $primaryChatIds)
            ->orderByDesc('last_message_at')
            ->paginate(20);

        // Add unread count per room
        foreach ($chats as $chat) {
            $chat->unread_count = ChatService::getAdminUnreadCountForRoom($chat->id);
        }

        return view('admin.chat.index', compact('chats'));
    }

    /**
     * Show a chat room with all messages.
     */
    public function show(Chat $chat)
    {
        $chat->load('user');

        // Mark customer messages as read by admin
        ChatService::markReadByAdmin($chat);

        $messages = $chat->messages()->orderBy('created_at', 'asc')->get();

        // Get latest order for this customer
        $latestOrder = Order::where('user_id', $chat->user_id)
            ->with('items.product')
            ->latest()
            ->first();

        return view('admin.chat.show', compact('chat', 'messages', 'latestOrder'));
    }

    /**
     * Admin reply (AJAX).
     */
    public function reply(Request $request, Chat $chat)
    {
        $data = $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $msg = ChatService::sendAdminMessage($chat, $data['message']);

        return response()->json([
            'id' => $msg->id,
            'message' => e($msg->message),
            'sender_type' => 'admin',
            'created_at' => $msg->created_at->timezone('Asia/Jakarta')->format('H:i'),
            'created_at_full' => $msg->created_at->timezone('Asia/Jakarta')->format('Y-m-d'),
        ]);
    }

    /**
     * Fetch messages for polling (AJAX).
     */
    public function fetchMessages(Request $request, Chat $chat)
    {
        // Mark customer messages as read
        ChatService::markReadByAdmin($chat);

        $afterId = $request->query('after_id', 0);
        $messages = $chat->messages()
            ->where('id', '>', $afterId)
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(fn($m) => [
                'id' => $m->id,
                'message' => e($m->message),
                'sender_type' => $m->sender_type,
                'sender_role' => $m->sender_role,
                'is_bot' => $m->is_bot,
                'is_auto' => $m->is_auto,
                'created_at' => $m->created_at->timezone('Asia/Jakarta')->format('H:i'),
                'created_at_full' => $m->created_at->timezone('Asia/Jakarta')->format('Y-m-d'),
                'user_name' => $chat->user->name ?? 'Pelanggan',
            ]);

        return response()->json(['messages' => $messages]);
    }

    /**
     * Admin unread count (AJAX).
     */
    public function unreadCount()
    {
        $count = ChatService::getAdminTotalUnreadCount();
        return response()->json(['count' => $count]);
    }
}
