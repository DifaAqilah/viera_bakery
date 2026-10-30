@extends('layouts.admin')
@section('title', 'Data Pelanggan — Admin Viera Bakery')
@section('page-title', 'Data Pelanggan')
@section('content')

<div class="card p-0">
    <div class="d-flex align-items-center justify-content-between px-4 pt-4 pb-3">
        <div class="card-header-vb mb-0 border-0 pb-0">
            <i class="bi bi-people"></i> Daftar Pelanggan
        </div>
        <span class="text-muted" style="font-size:.82rem;">
            Total: {{ $pelanggan->total() }} pelanggan
        </span>
    </div>
    <div class="table-responsive">
        <table class="table-vb w-100">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nama</th>
                    <th>Email</th>
                    <th class="text-center">Total Pesanan</th>
                    <th>Bergabung</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pelanggan as $p)
                <tr>
                    <td class="text-muted" style="font-size:.82rem;">{{ $loop->iteration }}</td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div style="width:36px; height:36px; background: var(--yellow-lt);
                                        border-radius:50%; display:flex; align-items:center;
                                        justify-content:center; font-size:1rem; flex-shrink:0;">
                                👤
                            </div>
                            <span class="fw-600" style="font-size:.875rem;">{{ $p->name }}</span>
                        </div>
                    </td>
                    <td style="font-size:.875rem; color: var(--muted);">{{ $p->email }}</td>
                    <td class="text-center">
                        <span class="badge-status {{ $p->orders_count > 0 ? 'badge-selesai' : 'badge-menunggu' }}">
                            {{ $p->orders_count }} pesanan
                        </span>
                    </td>
                    <td style="font-size:.82rem; color: var(--muted); white-space:nowrap;">
                        {{ $p->created_at->format('d M Y') }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center text-muted py-5">
                        Belum ada pelanggan terdaftar
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($pelanggan->hasPages())
        <div class="px-4 py-3">{{ $pelanggan->links() }}</div>
    @endif
</div>

@endsection
