@extends('layouts.app')
@section('title', 'Chat — Viera Bakery')
@section('content')
<div class="container py-4">

<div class="row g-4">
    <div class="col-lg-8 mx-auto">
        <div class="card p-0 chat-card">

            {{-- Header --}}
            <div class="chat-header">
                <div class="chat-header-avatar">
                    <i class="bi bi-shop"></i>
                </div>
                <div class="chat-header-info">
                    <div class="chat-header-name">Viera Bakery</div>
                    <div class="chat-header-status"><span class="status-dot"></span> Admin siap membantu</div>
                </div>
            </div>

            {{-- Order Info Card --}}
            @if($latestOrder)
            <div class="chat-order-card">
                <div class="chat-order-status">
                    @php
                        $chatStatusCls = match($latestOrder->status) {
                            'Menunggu Konfirmasi', 'Menunggu Diproses' => 'badge-menunggu',
                            'Diproses' => 'badge-diproses',
                            'Siap Ambil' => 'badge-siap-ambil',
                            'Dalam Perjalanan' => 'badge-perjalanan',
                            'Selesai' => 'badge-selesai',
                            'Dibatalkan' => 'badge-dibatalkan',
                            default => 'badge-menunggu',
                        };
                    @endphp
                    <span class="badge-status {{ $chatStatusCls }}" style="font-size:.68rem;">{{ $latestOrder->status }}</span>
                </div>
                <div class="chat-order-detail">
                    <span>{{ $latestOrder->items->count() }} produk</span>
                    <span class="mx-1">·</span>
                    <span>Total: <strong style="color:var(--orange);">Rp {{ number_format($latestOrder->total_amount, 0, ',', '.') }}</strong></span>
                    <span class="mx-1">·</span>
                    <span>No. Pesanan: <strong>#{{ $latestOrder->id }}</strong></span>
                </div>
                @if($latestOrder->transaction)
                <a href="{{ route('transactions.show', $latestOrder->transaction) }}?from=chat" class="chat-order-btn">Rincian</a>
                @endif
            </div>
            @endif

            {{-- Chat Messages --}}
            <div class="chat-box" id="chatBox">
                @if($messages->isEmpty())
                    <div class="chat-empty">
                        <div class="chat-empty-icon"><i class="bi bi-chat-text"></i></div>
                        <div class="chat-empty-title">Belum ada percakapan</div>
                        <div class="chat-empty-desc">Percakapan akan muncul setelah Anda membuat pesanan atau mengirim pesan.</div>
                    </div>
                @else
                    @php $lastDate = null; @endphp
                    @foreach($messages as $msg)
                        @php
                            $msgDate = $msg->created_at->format('Y-m-d');
                            $today = now()->format('Y-m-d');
                            $yesterday = now()->subDay()->format('Y-m-d');
                        @endphp
                        @if($msgDate !== $lastDate)
                            <div class="chat-date-separator">
                                <span>
                                    @if($msgDate === $today) Hari Ini
                                    @elseif($msgDate === $yesterday) Kemarin
                                    @else {{ $msg->created_at->format('d M Y') }}
                                    @endif
                                </span>
                            </div>
                            @php $lastDate = $msgDate; @endphp
                        @endif

                        @if($msg->sender_type === 'bot' || $msg->is_bot)
                            <div class="bubble-wrap them">
                                <div class="bubble-avatar bot"><i class="bi bi-robot"></i></div>
                                <div>
                                    <div class="bubble-sender">Bot Viera</div>
                                    <div class="bubble them bot-bubble">{!! nl2br(e($msg->message)) !!}</div>
                                    <div class="bubble-meta">{{ $msg->created_at->timezone('Asia/Jakarta')->format('H:i') }}</div>
                                </div>
                            </div>
                        @elseif($msg->sender_type === 'admin')
                            <div class="bubble-wrap them">
                                <div class="bubble-avatar admin"><i class="bi bi-headset"></i></div>
                                <div>
                                    <div class="bubble-sender">Admin</div>
                                    <div class="bubble them">{!! nl2br(e($msg->message)) !!}</div>
                                    <div class="bubble-meta">{{ $msg->created_at->timezone('Asia/Jakarta')->format('H:i') }}</div>
                                </div>
                            </div>
                        @else
                            <div class="bubble-wrap me">
                                <div>
                                    <div class="bubble me">{!! nl2br(e($msg->message)) !!}</div>
                                    <div class="bubble-meta text-end">{{ $msg->created_at->timezone('Asia/Jakarta')->format('H:i') }}</div>
                                </div>
                            </div>
                        @endif
                    @endforeach
                @endif
            </div>

            {{-- Input --}}
            <div class="chat-input-area">
                <form id="chatForm" class="chat-input-form">
                    @csrf
                    <textarea id="chatInput" class="chat-input" placeholder="Tulis pesan..." rows="1" maxlength="1000"></textarea>
                    <button type="submit" class="chat-send-btn" id="chatSendBtn">
                        <i class="bi bi-send-fill"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

</div>

@include('chat._chat_styles')

<script>
var csrfToken = '{{ csrf_token() }}';
var lastMsgId = {{ $messages->last()->id ?? 0 }};

// Auto-resize textarea
var input = document.getElementById('chatInput');
input.addEventListener('input', function() {
    this.style.height = 'auto';
    this.style.height = Math.min(this.scrollHeight, 120) + 'px';
});

// Enter to send, Shift+Enter for new line
input.addEventListener('keydown', function(e) {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        document.getElementById('chatForm').dispatchEvent(new Event('submit'));
    }
});

// Send message
document.getElementById('chatForm').addEventListener('submit', function(e) {
    e.preventDefault();
    var msg = input.value.trim();
    if (!msg) return;

    var btn = document.getElementById('chatSendBtn');
    btn.disabled = true;

    // Optimistic UI
    appendBubble({ message: msg, sender_type: 'customer', created_at: new Date().toLocaleTimeString('id-ID', {hour:'2-digit', minute:'2-digit'}) });
    input.value = '';
    input.style.height = 'auto';
    scrollToBottom();

    fetch('{{ route("chat.send") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        body: JSON.stringify({ message: msg })
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        btn.disabled = false;
        if (data.id) lastMsgId = Math.max(lastMsgId, data.id);
    })
    .catch(function() { btn.disabled = false; });
});

function appendBubble(msg) {
    var box = document.getElementById('chatBox');
    var empty = box.querySelector('.chat-empty');
    if (empty) empty.remove();

    var wrap = document.createElement('div');
    wrap.className = 'bubble-wrap ' + (msg.sender_type === 'customer' ? 'me' : 'them');

    var inner = '<div>';
    if (msg.sender_type === 'bot') {
        wrap.innerHTML = '<div class="bubble-avatar bot"><i class="bi bi-robot"></i></div><div><div class="bubble-sender">Bot Viera</div><div class="bubble them bot-bubble">' + msg.message.replace(/\n/g, '<br>') + '</div><div class="bubble-meta">' + msg.created_at + '</div></div>';
    } else if (msg.sender_type === 'admin') {
        wrap.innerHTML = '<div class="bubble-avatar admin"><i class="bi bi-headset"></i></div><div><div class="bubble-sender">Admin</div><div class="bubble them">' + msg.message.replace(/\n/g, '<br>') + '</div><div class="bubble-meta">' + msg.created_at + '</div></div>';
    } else {
        wrap.innerHTML = '<div><div class="bubble me">' + msg.message.replace(/\n/g, '<br>') + '</div><div class="bubble-meta text-end">' + msg.created_at + '</div></div>';
    }

    box.appendChild(wrap);
}

function scrollToBottom() {
    var box = document.getElementById('chatBox');
    box.scrollTop = box.scrollHeight;
}

// Polling every 5 seconds
setInterval(function() {
    fetch('{{ route("chat.messages") }}?after_id=' + lastMsgId, {
        headers: { 'Accept': 'application/json' }
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.messages && data.messages.length > 0) {
            // Remove optimistic customer messages that server confirmed
            data.messages.forEach(function(m) {
                if (m.sender_type !== 'customer') {
                    appendBubble(m);
                }
                lastMsgId = Math.max(lastMsgId, m.id);
            });
            scrollToBottom();
        }
    })
    .catch(function() {});
}, 5000);

// Initial scroll
scrollToBottom();
</script>

@endsection
