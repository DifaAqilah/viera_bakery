@extends('layouts.admin')
@section('title', 'Tambah Produk — Admin Viera Bakery')
@section('page-title', 'Tambah Produk Baru')
@section('content')

<div class="mb-3">
    <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Kembali
    </a>
</div>

<div class="card" style="max-width:680px;">
    <div class="card-header-vb">
        <i class="bi bi-plus-circle"></i> Form Tambah Produk
    </div>
    <form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data">
        @csrf

        <div class="row g-3">
            <div class="col-md-8">
                <label class="form-label" for="name">Nama Produk <span class="text-danger">*</span></label>
                <input type="text" id="name" name="name"
                       class="form-control @error('name') is-invalid @enderror"
                       value="{{ old('name') }}" required placeholder="Nama produk">
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="form-label" for="category_id">Kategori <span class="text-danger">*</span></label>
                <select id="category_id" name="category_id"
                        class="form-select @error('category_id') is-invalid @enderror" required>
                    <option value="">-- Pilih Kategori --</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
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
                       value="{{ old('price') }}" min="0" required placeholder="50000">
                @error('price')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label" for="stock">Stok <span class="text-danger">*</span></label>
                <input type="number" id="stock" name="stock"
                       class="form-control @error('stock') is-invalid @enderror"
                       value="{{ old('stock', 0) }}" min="0" required placeholder="10">
                @error('stock')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12">
                <label class="form-label" for="description">Deskripsi</label>
                <textarea id="description" name="description"
                          class="form-control @error('description') is-invalid @enderror"
                          rows="3" placeholder="Deskripsi singkat produk...">{{ old('description') }}</textarea>
                @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12">
                <label class="form-label" for="image">Gambar Produk</label>
                <input type="file" id="image" name="image"
                       class="form-control @error('image') is-invalid @enderror"
                       accept="image/jpg,image/jpeg,image/png,image/webp">
                <div class="form-text">Format: JPG, PNG, WebP. Maks: 2MB.</div>
                @error('image')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="d-flex gap-2 mt-4">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-circle me-2"></i>Simpan Produk
            </button>
            <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">Batal</a>
        </div>
    </form>
</div>

@endsection
