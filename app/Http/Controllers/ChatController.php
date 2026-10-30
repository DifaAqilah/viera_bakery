<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\Order;
use App\Services\ChatService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    /**
     * Show customer's single chat room directly.
     */
    public function index()
    {
        $user = Auth::user();
        $chat = ChatService::getOrCreateRoom($user->id);

        // Mark all admin/bot messages as read
        ChatService::markReadByCustomer($chat);

        $messages = $chat->messages()->orderBy('created_at', 'asc')->get();

        // Get latest active order for info card
        $latestOrder = Order::where('user_id', $user->id)
            ->whereNotIn('status', ['Selesai', 'Dibatalkan'])
            ->with('items.product')
            ->latest()
            ->first();

        return view('chat.index', compact('chat', 'messages', 'latestOrder'));
    }

    /**
     * Send a message (AJAX).
     */
    public function sendMessage(Request $request)
    {
        $data = $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $user = Auth::user();
        $chat = ChatService::getOrCreateRoom($user->id);

        $msg = ChatService::sendCustomerMessage($chat, $data['message']);

        return response()->json([
            'id' => $msg->id,
            'message' => e($msg->message),
            'sender_type' => 'customer',
            'created_at' => $msg->created_at->timezone('Asia/Jakarta')->format('H:i'),
            'created_at_full' => $msg->created_at->timezone('Asia/Jakarta')->format('Y-m-d'),
        ]);
    }

    /**
     * Fetch messages for polling (AJAX).
     */
    public function fetchMessages(Request $request)
    {
        $user = Auth::user();
        $chat = Chat::where('user_id', $user->id)->first();
        if (!$chat) {
            return response()->json(['messages' => []]);
        }

        // Mark as read
        ChatService::markReadByCustomer($chat);

        $afterId = $request->query('after_id', 0);
        $messages = $chat->messages()
            ->where('id', '>', $afterId)
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(fn($m) => [
                'id' => $m->id,
                'message' => e($m->message),
                'sender_type' => $m->sender_type,
                'is_bot' => $m->is_bot,
                'is_auto' => $m->is_auto,
                'created_at' => $m->created_at->timezone('Asia/Jakarta')->format('H:i'),
                'created_at_full' => $m->created_at->timezone('Asia/Jakarta')->format('Y-m-d'),
            ]);

        return response()->json(['messages' => $messages]);
    }

    /**
     * Get unread count for customer badge (AJAX).
     */
    public function unreadCount()
    {
        $count = ChatService::getCustomerUnreadCount(Auth::id());
        return response()->json(['count' => $count]);
    }
}
