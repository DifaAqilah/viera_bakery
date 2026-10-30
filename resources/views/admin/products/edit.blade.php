@extends('layouts.admin')
@section('title', 'Edit Produk — Admin Viera Bakery')
@section('page-title', 'Edit Produk: ' . $product->name)
@section('content')

<div class="mb-3">
    <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Kembali
    </a>
</div>

<div class="card" style="max-width:680px;">
    <div class="card-header-vb">
        <i class="bi bi-pencil-square"></i> Form Edit Produk
    </div>
    <form method="POST" action="{{ route('admin.products.update', $product) }}" enctype="multipart/form-data">
        @csrf @method('PUT')

        <div class="row g-3">
            <div class="col-md-8">
                <label class="form-label" for="name">Nama Produk <span class="text-danger">*</span></label>
                <input type="text" id="name" name="name"
                       class="form-control @error('name') is-invalid @enderror"
                       value="{{ old('name', $product->name) }}" required>
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="form-label" for="category_id">Kategori <span class="text-danger">*</span></label>
                <select id="category_id" name="category_id"
                        class="form-select @error('category_id') is-invalid @enderror" required>
                    <option value="">-- Pilih Kategori --</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}"
                            {{ old('category_id', $product->category_id) == $cat->id ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
                @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label" for="price">Harga (Rp) <span class="text-danger">*</span></label>
                <input type="number" id="price" name="price"
                       class="form-control @error('price') is-invalid @enderror"
                       value="{{ old('price', $product->price) }}" min="0" required>
                @error('price')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label" for="stock">Stok <span class="text-danger">*</span></label>
                <input type="number" id="stock" name="stock"
                       class="form-control @error('stock') is-invalid @enderror"
                       value="{{ old('stock', $product->stock) }}" min="0" required>
                @error('stock')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12">
                <label class="form-label" for="description">Deskripsi</label>
                <textarea id="description" name="description"
                          class="form-control @error('description') is-invalid @enderror"
                          rows="3">{{ old('description', $product->description) }}</textarea>
                @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12">
                <label class="form-label">Gambar Saat Ini</label>
                @if($product->image && file_exists(public_path('storage/'.$product->image)))
                    <div class="mb-2">
                        <img src="{{ asset('storage/'.$product->image) }}"
                             style="height:120px; border-radius: var(--radius-sm); object-fit:cover;">
                    </div>
                @else
                    <div class="mb-2 text-muted" style="font-size:.82rem;">Belum ada gambar</div>
                @endif
                <label class="form-label" for="image">Ganti Gambar (opsional)</label>
                <input type="file" id="image" name="image"
                       class="form-control @error('image') is-invalid @enderror"
                       accept="image/jpg,image/jpeg,image/png,image/webp">
                <div class="form-text">Biarkan kosong jika tidak ingin mengubah gambar. Maks: 2MB.</div>
                @error('image')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="d-flex gap-2 mt-4">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-circle me-2"></i>Simpan Perubahan
            </button>
            <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">Batal</a>
        </div>
    </form>
</div>

@endsection
