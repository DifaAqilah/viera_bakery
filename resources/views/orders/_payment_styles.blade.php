{{-- Shared payment page styles --}}
<style>
    /* ── Progress Steps ── */
    .progress-steps { display:flex; align-items:center; justify-content:center; gap:0; padding:.75rem 1rem; background:#fff; border-radius:var(--radius); box-shadow:var(--shadow-sm); }
    .progress-step { display:flex; flex-direction:column; align-items:center; gap:.3rem; }
    .step-circle { width:32px; height:32px; border-radius:50%; background:var(--border); color:var(--muted); display:flex; align-items:center; justify-content:center; font-weight:700; font-size:.8rem; transition:all .3s; }
    .step-label { font-size:.7rem; color:var(--muted); font-weight:500; transition:all .3s; }
    .progress-step.active .step-circle { background:var(--orange); color:#fff; box-shadow:0 2px 8px rgba(244,125,53,.3); }
    .progress-step.active .step-label { color:var(--orange); font-weight:600; }
    .progress-step.completed .step-circle { background:#22C55E; color:#fff; }
    .step-line { flex:1; height:2px; background:var(--border); margin:0 .5rem; margin-bottom:1.2rem; transition:all .3s; }
    .step-line.active { background:var(--orange); }

    /* ── Payment Method Options ── */
    .payment-method-options { display:flex; gap:.75rem; }
    .payment-method-btn {
        flex:1; display:flex; align-items:center; justify-content:center; gap:.5rem;
        padding:.7rem 1rem; border-radius:var(--radius-xs); font-weight:600;
        font-size:.9rem; font-family:var(--font-body); cursor:pointer;
        border:2px solid var(--orange); background:#fff; color:var(--orange);
        transition:all .2s ease;
    }
    .payment-method-btn:hover { background:var(--orange-lt); }
    .payment-method-btn.active { background:var(--orange); color:#fff; box-shadow:0 3px 10px rgba(244,125,53,.3); }

    .payment-section { animation:fadeSlideIn .3s ease forwards; }
    @keyframes fadeSlideIn { from { opacity:0; transform:translateY(10px); } to { opacity:1; transform:translateY(0); } }

    /* ── Onsite Card ── */
    .onsite-icon-area { display:flex; align-items:center; gap:.5rem; margin-bottom:.75rem; }
    .onsite-icon { width:48px; height:48px; border-radius:12px; background:var(--orange-lt); color:var(--orange); display:flex; align-items:center; justify-content:center; font-size:1.4rem; }
    .cod-badge { background:var(--orange); color:#fff; font-size:.7rem; font-weight:700; padding:.2rem .6rem; border-radius:20px; }
    .onsite-desc { font-size:.85rem; color:var(--muted); line-height:1.6; margin-bottom:.75rem; }
    .onsite-checklist { display:flex; flex-direction:column; gap:.4rem; margin-bottom:1rem; }
    .check-item { font-size:.82rem; display:flex; align-items:center; gap:.5rem; }
    .check-item i { font-size:.9rem; }
    .onsite-info-box { background:var(--orange-lt); border:1px solid rgba(244,125,53,.15); border-radius:var(--radius-sm); padding:.75rem 1rem; margin-bottom:.75rem; }
    .onsite-info-row { display:flex; justify-content:space-between; padding:.35rem 0; border-bottom:1px solid rgba(244,125,53,.1); font-size:.82rem; }
    .onsite-info-row span:first-child { color:var(--muted); }
    .onsite-note-box { background:#FEF3C7; border:1px solid #F59E0B33; border-radius:var(--radius-xs); padding:.6rem .75rem; font-size:.78rem; color:#92400E; display:flex; align-items:flex-start; gap:.4rem; }

    /* ── QRIS Area ── */
    .payment-method-badge { display:inline-flex; align-items:center; gap:.5rem; background:var(--blue-lt); color:var(--blue-dk); font-weight:600; font-size:.8rem; padding:.4rem .9rem; border-radius:50px; margin-bottom:1rem; border:1px solid rgba(110,201,227,.3); }
    .qris-display-area { text-align:center; padding:1rem; background:#FAFAF8; border:1.5px dashed var(--orange); border-radius:var(--radius-sm); margin-bottom:1rem; }
    .qris-img { max-width:280px; width:100%; border-radius:8px; margin-bottom:.75rem; }
    .qris-placeholder { display:flex; flex-direction:column; align-items:center; justify-content:center; min-height:200px; }
    .qris-instructions { padding:.75rem; background:var(--orange-lt); border-radius:var(--radius-xs); margin-bottom:.5rem; }

    /* ── Upload ── */
    .upload-zone { border:2px dashed var(--border); border-radius:var(--radius-sm); padding:1.5rem; text-align:center; cursor:pointer; background:#FAFAF8; transition:all .2s; }
    .upload-zone:hover { border-color:var(--orange); background:var(--orange-lt); }
    .upload-zone-icon { font-size:2rem; color:var(--orange); margin-bottom:.5rem; }
    .upload-zone-text { margin-bottom:.25rem; font-size:.85rem; }
    .upload-zone-text span { display:block; font-size:.78rem; color:var(--muted); }
    .upload-zone-formats { font-size:.72rem; color:var(--muted); }
    .upload-preview { display:flex; align-items:center; gap:.75rem; padding:.75rem; background:#FAFAF8; border:1px solid var(--border); border-radius:var(--radius-xs); margin-top:.5rem; }
    .upload-preview img { width:60px; height:60px; object-fit:cover; border-radius:6px; border:1px solid var(--border); }
    .upload-preview-info { flex:1; display:flex; justify-content:space-between; align-items:center; }
    .upload-preview-info span { font-size:.82rem; font-weight:500; }
    .upload-success-msg { margin-top:.5rem; padding:.5rem .75rem; background:#D1FAE5; color:#065F46; border-radius:var(--radius-xs); font-size:.82rem; font-weight:500; display:flex; align-items:center; gap:.4rem; }
    .uploaded-proof { text-align:center; }
    .uploaded-proof img { max-width:200px; border-radius:8px; border:1px solid var(--border); margin-bottom:.5rem; }

    /* ── Buat Pesanan ── */
    .btn-place-order { padding:.75rem !important; font-size:1rem !important; font-weight:700 !important; }

    /* ── Payment Secure Note ── */
    .payment-secure-note { display:flex; align-items:center; gap:.5rem; font-size:.78rem; color:var(--muted); padding:.6rem .75rem; background:#F0FDF4; border-radius:var(--radius-xs); border:1px solid #BBF7D0; }

    /* ── Order Info ── */
    .order-info-row { display:flex; justify-content:space-between; align-items:flex-start; padding:.6rem 0; border-bottom:1px solid var(--border); font-size:.85rem; }

    /* ── Payment Items ── */
    .payment-item-row { display:flex; align-items:center; gap:.6rem; padding:.6rem 0; border-bottom:1px solid var(--border); }
    .payment-item-img { width:40px; height:40px; border-radius:8px; object-fit:cover; border:1px solid var(--border); flex-shrink:0; }
    .payment-item-img-placeholder { width:40px; height:40px; border-radius:8px; background:var(--yellow-lt); display:flex; align-items:center; justify-content:center; flex-shrink:0; }
    .payment-item-info { flex:1; }
    .payment-item-name { font-size:.82rem; font-weight:600; }
    .payment-item-qty { font-size:.72rem; color:var(--muted); }
    .payment-item-subtotal { font-size:.82rem; font-weight:600; color:var(--orange); white-space:nowrap; }
    .payment-summary-line { display:flex; justify-content:space-between; padding:.5rem 0; font-size:.85rem; border-bottom:1px solid var(--border); }
    .payment-total-line { display:flex; justify-content:space-between; padding:.75rem 0 .25rem; }
    .payment-total-amount { font-weight:800; color:var(--orange); font-size:1.1rem; font-family:var(--font-heading); }
    .payment-divider { text-align:center; position:relative; margin:1rem 0; }
    .payment-divider::before { content:''; position:absolute; top:50%; left:0; right:0; height:1px; background:var(--border); }
    .payment-divider span { position:relative; background:#fff; padding:0 1rem; font-size:.78rem; color:var(--muted); text-transform:uppercase; letter-spacing:.5px; }

    /* ── Success Modal ── */
    .order-success-overlay { position:fixed; inset:0; z-index:9999; background:rgba(0,0,0,.5); display:flex; align-items:center; justify-content:center; padding:1rem; animation:fadeIn .25s; }
    @keyframes fadeIn { from { opacity:0; } to { opacity:1; } }
    .order-success-modal { background:#fff; border-radius:20px; padding:2rem 1.75rem; width:100%; max-width:420px; box-shadow:var(--shadow-lg); text-align:center; animation:scaleIn .3s cubic-bezier(.68,-.55,.265,1.55); }
    @keyframes scaleIn { from { transform:scale(.85); opacity:0; } to { transform:scale(1); opacity:1; } }
    .order-success-title { font-family:var(--font-heading); font-weight:800; font-size:1.3rem; color:var(--text); margin-bottom:.5rem; }
    .order-success-desc { color:var(--muted); font-size:.875rem; margin-bottom:1.25rem; line-height:1.6; }
    .order-success-detail { background:var(--orange-lt); border:1px solid rgba(244,125,53,.15); border-radius:var(--radius-sm); padding:.75rem 1rem; margin-bottom:1rem; text-align:left; }
    .order-detail-row { display:flex; justify-content:space-between; padding:.4rem 0; border-bottom:1px solid rgba(244,125,53,.1); font-size:.82rem; }
    .order-detail-row span:first-child { color:var(--muted); }
    .order-success-info { font-size:.8rem; color:var(--muted); font-style:italic; margin-bottom:1rem; }
    .btn-done { height:46px; font-size:.95rem !important; font-weight:700 !important; }
    .success-checkmark { text-align:center; margin-bottom:1rem; }
    .success-checkmark-circle { width:72px; height:72px; border-radius:50%; background:linear-gradient(135deg,#22C55E,#16A34A); display:inline-flex; align-items:center; justify-content:center; box-shadow:0 6px 20px rgba(34,197,94,.35); animation:successPop .4s cubic-bezier(.68,-.55,.265,1.55); }
    .success-checkmark-circle i { font-size:2.5rem; color:#fff; }
    @keyframes successPop { 0% { transform:scale(0); opacity:0; } 100% { transform:scale(1); opacity:1; } }

    /* ── Responsive ── */
    @media (max-width:576px) {
        .payment-method-options { flex-direction:column; }
        .order-success-modal { padding:1.5rem 1.25rem; }
        .progress-steps { padding:.5rem .75rem; }
        .step-label { font-size:.6rem; }
    }
</style>
