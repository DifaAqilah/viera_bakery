@extends('layouts.admin')
@section('title', 'Chat — Admin Viera Bakery')
@section('page-title', 'Chat dengan ' . ($chat->user->name ?? 'Pelanggan'))
@section('content')

<div class="mb-3">
    <a href="{{ route('admin.chat.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Kembali
    </a>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card p-0 chat-card-admin">

            {{-- Header --}}
            <div class="chat-header">
                <div class="chat-list-avatar">
                    {{ strtoupper(substr($chat->user->name ?? '?', 0, 1)) }}
                </div>
                <div class="chat-header-info">
                    <div class="chat-header-name">{{ $chat->user->name ?? '—' }}</div>
                    <div class="chat-header-status">{{ $chat->user->email ?? '' }}</div>
                </div>
            </div>

            {{-- Order Info --}}
            @if($latestOrder)
            <div class="chat-order-card">
                <div class="chat-order-status">
                    <span class="badge-status badge-menunggu" style="font-size:.68rem;">{{ $latestOrder->status }}</span>
                </div>
                <div class="chat-order-detail">
                    <span>#{{ $latestOrder->id }}</span>
                    <span class="mx-1">·</span>
                    <span>{{ $latestOrder->items->count() }} produk</span>
                    <span class="mx-1">·</span>
                    <span class="fw-600" style="color:var(--orange);">Rp {{ number_format($latestOrder->total_amount, 0, ',', '.') }}</span>
                    @if($latestOrder->payment_method)
                    <span class="mx-1">·</span>
                    <span>{{ strtoupper($latestOrder->payment_method) }}</span>
                    @endif
                </div>
                <a href="{{ route('admin.orders.show', $latestOrder) }}" class="chat-order-btn">Detail</a>
            </div>
            @endif

            {{-- Messages --}}
            <div class="chat-box" id="chatBox">
                @if($messages->isEmpty())
                    <div class="chat-empty">
                        <div class="chat-empty-icon"><i class="bi bi-chat-text"></i></div>
                        <div class="chat-empty-title">Belum ada pesan</div>
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
                                    <div class="bubble-sender">BOT · Pesan Otomatis</div>
                                    <div class="bubble them bot-bubble">{!! nl2br(e($msg->message)) !!}</div>
                                    <div class="bubble-meta">{{ $msg->created_at->timezone('Asia/Jakarta')->format('H:i') }}</div>
                                </div>
                            </div>
                        @elseif($msg->sender_type === 'customer' || $msg->sender_role === 'pelanggan')
                            <div class="bubble-wrap them">
                                <div class="bubble-avatar customer"><i class="bi bi-person"></i></div>
                                <div>
                                    <div class="bubble-sender">{{ $chat->user->name ?? 'Pelanggan' }}</div>
                                    <div class="bubble them">{!! nl2br(e($msg->message)) !!}</div>
                                    <div class="bubble-meta">{{ $msg->created_at->timezone('Asia/Jakarta')->format('H:i') }}</div>
                                </div>
                            </div>
                        @else
                            <div class="bubble-wrap me">
                                <div>
                                    <div class="bubble-sender text-end">Admin</div>
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
                    <textarea id="chatInput" class="chat-input" placeholder="Tulis balasan..." rows="1" maxlength="1000"></textarea>
                    <button type="submit" class="chat-send-btn" id="chatSendBtn">
                        <i class="bi bi-send-fill"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Info Pelanggan --}}
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header-vb">
                <i class="bi bi-person"></i> Info Pelanggan
            </div>
            <div class="d-flex align-items-center gap-3 mb-3">
                <div class="chat-list-avatar" style="width:48px; height:48px; font-size:1.2rem;">
                    {{ strtoupper(substr($chat->user->name ?? '?', 0, 1)) }}
                </div>
                <div>
                    <div class="fw-600">{{ $chat->user->name ?? '—' }}</div>
                    <div style="font-size:.78rem; color: var(--muted);">{{ $chat->user->email ?? '' }}</div>
                </div>
            </div>
            <div style="font-size:.82rem; color: var(--muted);">
                Bergabung: {{ $chat->user->created_at->format('d F Y') ?? '—' }}
            </div>
            <hr style="border-color: var(--border);">
            @if($latestOrder)
                <a href="{{ route('admin.orders.show', $latestOrder) }}?from=chat&chat_id={{ $chat->id }}" class="btn btn-outline-primary btn-sm w-100">
                    <i class="bi bi-bag me-1"></i>Lihat Pesanan
                </a>
            @else
                <button type="button" class="btn btn-outline-secondary btn-sm w-100" onclick="alert('Pelanggan ini belum memiliki pesanan.')" title="Pelanggan belum memiliki pesanan">
                    <i class="bi bi-bag me-1"></i>Lihat Pesanan
                </button>
            @endif
        </div>
    </div>
</div>

@include('chat._chat_styles')
<style>
    .chat-card-admin { display:flex; flex-direction:column; height:calc(100vh - 200px); min-height:450px; overflow:hidden; }
    .chat-list-avatar {
        width:36px; height:36px; border-radius:50%;
        background:var(--orange-lt); color:var(--orange);
        display:flex; align-items:center; justify-content:center;
        font-weight:700; font-size:.85rem; flex-shrink:0;
    }
</style>

<script>
var csrfToken = '{{ csrf_token() }}';
var lastMsgId = {{ $messages->last()->id ?? 0 }};
var chatId = {{ $chat->id }};

var input = document.getElementById('chatInput');
input.addEventListener('input', function() {
    this.style.height = 'auto';
    this.style.height = Math.min(this.scrollHeight, 120) + 'px';
});

input.addEventListener('keydown', function(e) {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        document.getElementById('chatForm').dispatchEvent(new Event('submit'));
    }
});

document.getElementById('chatForm').addEventListener('submit', function(e) {
    e.preventDefault();
    var msg = input.value.trim();
    if (!msg) return;

    var btn = document.getElementById('chatSendBtn');
    btn.disabled = true;

    appendBubble({ message: msg, sender_type: 'admin', created_at: new Date().toLocaleTimeString('id-ID', {hour:'2-digit', minute:'2-digit'}) });
    input.value = '';
    input.style.height = 'auto';
    scrollToBottom();

    fetch('/admin/chat/' + chatId + '/reply', {
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
    wrap.className = 'bubble-wrap ' + (msg.sender_type === 'admin' ? 'me' : 'them');

    if (msg.sender_type === 'bot') {
        wrap.innerHTML = '<div class="bubble-avatar bot"><i class="bi bi-robot"></i></div><div><div class="bubble-sender">BOT · Pesan Otomatis</div><div class="bubble them bot-bubble">' + msg.message.replace(/\n/g, '<br>') + '</div><div class="bubble-meta">' + msg.created_at + '</div></div>';
    } else if (msg.sender_type === 'customer') {
        wrap.innerHTML = '<div class="bubble-avatar customer"><i class="bi bi-person"></i></div><div><div class="bubble-sender">Pelanggan</div><div class="bubble them">' + msg.message.replace(/\n/g, '<br>') + '</div><div class="bubble-meta">' + msg.created_at + '</div></div>';
    } else {
        wrap.innerHTML = '<div><div class="bubble-sender text-end">Admin</div><div class="bubble me">' + msg.message.replace(/\n/g, '<br>') + '</div><div class="bubble-meta text-end">' + msg.created_at + '</div></div>';
    }
    box.appendChild(wrap);
}

function scrollToBottom() {
    var box = document.getElementById('chatBox');
    box.scrollTop = box.scrollHeight;
}

// Polling
setInterval(function() {
    fetch('/admin/chat/' + chatId + '/messages?after_id=' + lastMsgId, {
        headers: { 'Accept': 'application/json' }
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.messages && data.messages.length > 0) {
            data.messages.forEach(function(m) {
                if (m.sender_type !== 'admin') {
                    appendBubble(m);
                }
                lastMsgId = Math.max(lastMsgId, m.id);
            });
            scrollToBottom();
        }
    })
    .catch(function() {});
}, 5000);

scrollToBottom();
</script>

@endsection
