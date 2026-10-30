@extends('layouts.admin')
@section('title', 'Edit Kategori — Admin')
@section('page-title', 'Edit Kategori: ' . $category->name)
@section('content')

<div class="mb-3">
    <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Kembali
    </a>
</div>

<div class="card" style="max-width:520px;">
    <div class="card-header-vb"><i class="bi bi-pencil-square"></i> Form Edit Kategori</div>
    <form method="POST" action="{{ route('admin.categories.update', $category) }}">
        @csrf @method('PUT')
        <div class="mb-3">
            <label class="form-label" for="name">Nama Kategori <span class="text-danger">*</span></label>
            <input type="text" id="name" name="name"
                   class="form-control @error('name') is-invalid @enderror"
                   value="{{ old('name', $category->name) }}" required>
            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
            <label class="form-label" for="slug">Slug <span class="text-danger">*</span></label>
            <input type="text" id="slug" name="slug"
                   class="form-control @error('slug') is-invalid @enderror"
                   value="{{ old('slug', $category->slug) }}" required>
            @error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="mb-4">
            <label class="form-label" for="description">Deskripsi</label>
            <textarea id="description" name="description"
                      class="form-control" rows="2">{{ old('description', $category->description) }}</textarea>
        </div>
        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-circle me-2"></i>Simpan Perubahan
            </button>
            <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary">Batal</a>
        </div>
    </form>
</div>

@endsection
