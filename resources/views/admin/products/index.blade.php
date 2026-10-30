@extends('layouts.admin')
@section('title', 'Produk — Admin Viera Bakery')
@section('page-title', 'Manajemen Produk')
@section('content')

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <span class="text-muted" style="font-size:.85rem;">Total: {{ $products->total() }} produk</span>
    <a href="{{ route('admin.products.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle me-2"></i>Tambah Produk
    </a>
</div>

<div class="card p-0">
    <div class="table-responsive">
        <table class="table-vb w-100">
            <thead>
                <tr>
                    <th>Gambar</th>
                    <th>Nama Produk</th>
                    <th>Kategori</th>
                    <th class="text-center">Harga</th>
                    <th class="text-center">Stok</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                <tr>
                    <td style="width:60px;">
                        @if($product->image && file_exists(public_path('storage/'.$product->image)))
                            <img src="{{ asset('storage/'.$product->image) }}"
                                 style="width:48px; height:48px; object-fit:cover; border-radius:8px;">
                        @else
                            <div style="width:48px; height:48px; background: var(--yellow-lt);
                                        border-radius:8px; display:flex; align-items:center;
                                        justify-content:center; font-size:1.4rem;"><i class="bi bi-image" style="color:var(--muted);"></i></div>
                        @endif
                    </td>
                    <td class="fw-600" style="font-size:.875rem;">{{ $product->name }}</td>
                    <td style="font-size:.82rem; color: var(--muted);">
                        {{ $product->category->name ?? '—' }}
                    </td>
                    <td class="text-center fw-600" style="color: var(--orange); white-space:nowrap;">
                        Rp {{ number_format($product->price, 0, ',', '.') }}
                    </td>
                    <td class="text-center">
                        <span class="badge-status {{ $product->stock > 0 ? 'badge-selesai' : 'badge-dibatalkan' }}">
                            {{ $product->stock }}
                        </span>
                    </td>
                    <td class="text-center">
                        <div class="d-flex gap-1 justify-content-center">
                            <a href="{{ route('admin.products.edit', $product) }}"
                               class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form method="POST" action="{{ route('admin.products.destroy', $product) }}">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm"
                                        onclick="return confirm('Hapus produk ini?')">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-5">
                        Belum ada produk. <a href="{{ route('admin.products.create') }}">Tambah sekarang</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($products->hasPages())
        <div class="px-4 py-3">{{ $products->links() }}</div>
    @endif
</div>

@endsection
