@extends('layouts.admin')
@section('title', 'Chat Pelanggan — Admin Viera Bakery')
@section('page-title', 'Chat Pelanggan')
@section('content')

<div class="card p-0">
    <div class="d-flex align-items-center justify-content-between px-4 pt-4 pb-3">
        <div class="card-header-vb mb-0 border-0 pb-0">
            <i class="bi bi-chat-dots"></i> Daftar Chat Pelanggan
        </div>
    </div>
    <div class="table-responsive">
        <table class="table-vb w-100">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Pelanggan</th>
                    <th>Pesan Terakhir</th>
                    <th class="text-center">Belum Dibaca</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($chats as $chat)
                <tr>
                    <td class="text-muted" style="font-size:.82rem;">{{ $chat->id }}</td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="chat-list-avatar">
                                {{ strtoupper(substr($chat->user->name ?? '?', 0, 1)) }}
                            </div>
                            <div>
                                <div class="fw-600" style="font-size:.875rem;">{{ $chat->user->name ?? '—' }}</div>
                                <div style="font-size:.72rem; color: var(--muted);">{{ $chat->user->email ?? '' }}</div>
                            </div>
                        </div>
                    </td>
                    <td style="font-size:.82rem; color: var(--muted); max-width:250px;">
                        @if($chat->last_message)
                            @php
                                $lastMsg = $chat->last_message;
                                // Determine prefix from the latest message
                                $latestMsg = $chat->messages()->latest()->first();
                                $prefix = '';
                                if ($latestMsg) {
                                    if ($latestMsg->sender_type === 'bot') $prefix = '[BOT] ';
                                    elseif ($latestMsg->sender_type === 'admin') $prefix = '[ADMIN] ';
                                }
                            @endphp
                            <div style="overflow:hidden; text-overflow:ellipsis; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical;">
                                @if($prefix)<span class="fw-600" style="font-size:.72rem; color:var(--orange);">{{ $prefix }}</span>@endif{{ Str::limit($lastMsg, 80) }}
                            </div>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @if($chat->unread_count > 0)
                            <span class="unread-badge">{{ $chat->unread_count > 99 ? '99+' : $chat->unread_count }}</span>
                        @else
                            <span class="text-muted" style="font-size:.78rem;">—</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <a href="{{ route('admin.chat.show', $chat) }}"
                           class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-chat-text me-1"></i>Balas
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center text-muted py-5">
                        <i class="bi bi-chat-dots" style="font-size:2rem; display:block; margin-bottom:.5rem;"></i>
                        Belum ada chat dari pelanggan
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($chats->hasPages())
        <div class="px-4 py-3">{{ $chats->links() }}</div>
    @endif
</div>

<style>
    .chat-list-avatar {
        width:36px; height:36px; border-radius:50%;
        background:var(--orange-lt); color:var(--orange);
        display:flex; align-items:center; justify-content:center;
        font-weight:700; font-size:.85rem; flex-shrink:0;
    }
    .unread-badge {
        display:inline-flex; align-items:center; justify-content:center;
        min-width:22px; height:22px; padding:0 6px;
        background:#EF4444; color:#fff; border-radius:20px;
        font-size:.7rem; font-weight:700;
    }
</style>

@endsection
