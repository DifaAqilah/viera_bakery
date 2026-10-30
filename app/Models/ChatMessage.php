<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'chat_id', 'sender_role', 'sender_type', 'message',
        'is_bot', 'is_auto', 'order_id',
        'read_by_customer_at', 'read_by_admin_at',
    ];

    protected function casts(): array
    {
        return [
            'is_bot' => 'boolean',
            'is_auto' => 'boolean',
            'read_by_customer_at' => 'datetime',
            'read_by_admin_at' => 'datetime',
        ];
    }

    public function chat()
    {
        return $this->belongsTo(Chat::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
