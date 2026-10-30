{{-- Shared Chat Styles --}}
<style>
    .chat-card { display:flex; flex-direction:column; height:calc(100vh - 140px); min-height:500px; overflow:hidden; }

    /* Header */
    .chat-header { display:flex; align-items:center; gap:.75rem; padding:.85rem 1.25rem; border-bottom:1px solid var(--border); background:#fff; border-radius:var(--radius) var(--radius) 0 0; flex-shrink:0; }
    .chat-header-avatar { width:42px; height:42px; border-radius:50%; background:var(--orange-lt); color:var(--orange); display:flex; align-items:center; justify-content:center; font-size:1.2rem; flex-shrink:0; }
    .chat-header-name { font-weight:700; font-family:var(--font-heading); font-size:.95rem; }
    .chat-header-status { font-size:.72rem; color:var(--muted); display:flex; align-items:center; gap:.35rem; }
    .status-dot { width:7px; height:7px; border-radius:50%; background:#22C55E; display:inline-block; }

    /* Order Card */
    .chat-order-card { padding:.6rem 1rem; background:var(--orange-lt); border-bottom:1px solid rgba(244,125,53,.12); display:flex; align-items:center; gap:.75rem; flex-wrap:wrap; flex-shrink:0; font-size:.78rem; }
    .chat-order-detail { flex:1; color:var(--muted); min-width:0; }
    .chat-order-btn { font-size:.7rem; font-weight:600; color:var(--orange); text-decoration:none; border:1px solid var(--orange); padding:.2rem .6rem; border-radius:20px; white-space:nowrap; transition:all .15s; }
    .chat-order-btn:hover { background:var(--orange); color:#fff; }

    /* Chat Box */
    .chat-box { flex:1; overflow-y:auto; padding:1rem 1.25rem; display:flex; flex-direction:column; gap:.5rem; background:#FAFAF8; }

    /* Empty State */
    .chat-empty { text-align:center; padding:3rem 1rem; }
    .chat-empty-icon { font-size:3rem; color:var(--muted); margin-bottom:.75rem; }
    .chat-empty-title { font-weight:700; font-family:var(--font-heading); margin-bottom:.35rem; }
    .chat-empty-desc { font-size:.82rem; color:var(--muted); }

    /* Date Separator */
    .chat-date-separator { text-align:center; margin:.75rem 0 .5rem; }
    .chat-date-separator span { background:var(--border); color:var(--muted); font-size:.68rem; padding:.2rem .75rem; border-radius:20px; font-weight:500; }

    /* Bubbles */
    .bubble-wrap { display:flex; gap:.5rem; max-width:80%; }
    .bubble-wrap.me { align-self:flex-end; justify-content:flex-end; }
    .bubble-wrap.them { align-self:flex-start; }
    .bubble-avatar { width:30px; height:30px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:.8rem; flex-shrink:0; margin-top:1.2rem; }
    .bubble-avatar.bot { background:#E0F2FE; color:#0284C7; }
    .bubble-avatar.admin { background:var(--orange-lt); color:var(--orange); }
    .bubble-avatar.customer { background:#E8E8E8; color:#666; }
    .bubble-sender { font-size:.68rem; font-weight:600; color:var(--muted); margin-bottom:.15rem; padding-left:.25rem; }
    .bubble { padding:.55rem .85rem; border-radius:14px; font-size:.85rem; line-height:1.5; word-wrap:break-word; max-width:100%; }
    .bubble.me { background:var(--orange); color:#fff; border-bottom-right-radius:4px; }
    .bubble.them { background:#fff; color:var(--text); border:1px solid var(--border); border-bottom-left-radius:4px; }
    .bubble.bot-bubble { background:#EFF6FF; border-color:#BFDBFE; color:#1E3A5F; }
    .bubble-meta { font-size:.65rem; color:var(--muted); margin-top:.15rem; padding:0 .25rem; }

    /* Input */
    .chat-input-area { padding:.75rem 1rem; border-top:1px solid var(--border); background:#fff; border-radius:0 0 var(--radius) var(--radius); flex-shrink:0; }
    .chat-input-form { display:flex; gap:.5rem; align-items:flex-end; }
    .chat-input { flex:1; border:1.5px solid var(--border); border-radius:12px; padding:.5rem .85rem; font-size:.85rem; resize:none; outline:none; font-family:var(--font-body); transition:border-color .2s; max-height:120px; line-height:1.5; }
    .chat-input:focus { border-color:var(--orange); }
    .chat-send-btn { width:40px; height:40px; border-radius:50%; border:none; background:var(--orange); color:#fff; display:flex; align-items:center; justify-content:center; cursor:pointer; transition:all .15s; flex-shrink:0; }
    .chat-send-btn:hover { background:var(--orange-dk); transform:scale(1.05); }
    .chat-send-btn:disabled { opacity:.5; cursor:not-allowed; }

    @media (max-width:576px) {
        .chat-card { height:calc(100vh - 100px); }
        .bubble-wrap { max-width:90%; }
        .chat-order-card { flex-direction:column; align-items:flex-start; }
    }
</style>
