@extends('layouts.admin')
@section('title', 'Kategori — Admin Viera Bakery')
@section('page-title', 'Manajemen Kategori')
@section('content')

<div class="d-flex justify-content-end mb-3">
    <a href="{{ route('admin.categories.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle me-2"></i>Tambah Kategori
    </a>
</div>

<div class="card p-0">
    <div class="table-responsive">
        <table class="table-vb w-100">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nama Kategori</th>
                    <th>Slug</th>
                    <th>Deskripsi</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($categories as $cat)
                <tr>
                    <td class="text-muted" style="font-size:.82rem;">{{ $loop->iteration }}</td>
                    <td class="fw-600" style="font-size:.875rem;">{{ $cat->name }}</td>
                    <td style="font-size:.82rem; color: var(--muted);">{{ $cat->slug }}</td>
                    <td style="font-size:.82rem; color: var(--muted);">
                        {{ Str::limit($cat->description, 60) }}
                    </td>
                    <td class="text-center">
                        <div class="d-flex gap-1 justify-content-center">
                            <a href="{{ route('admin.categories.edit', $cat) }}"
                               class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form method="POST" action="{{ route('admin.categories.destroy', $cat) }}">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm"
                                        onclick="return confirm('Hapus kategori ini?')">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center text-muted py-5">
                        Belum ada kategori. <a href="{{ route('admin.categories.create') }}">Tambah sekarang</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
