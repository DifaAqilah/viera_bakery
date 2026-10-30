@extends('layouts.app')
@section('title', 'Edit Profil — Viera Bakery')
@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">

            <h2 class="section-title mb-4">Edit Profil</h2>

            {{-- Notifikasi error --}}
            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert"
                     style="border-left: 4px solid #dc3545; background: #fff5f5;">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i><strong>Terdapat kesalahan:</strong>
                    <ul class="mb-0 mt-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
                </div>
            @endif

            {{-- Avatar (display only, no upload) --}}
            <div class="card mb-4 text-center">
                <div class="py-4">
                    <div style="width:80px; height:80px; border-radius:50%; background:var(--orange-lt); color:var(--orange);
                                display:flex; align-items:center; justify-content:center;
                                font-size:2rem; font-weight:700; margin:0 auto 1rem;">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                    <div class="fw-700" style="font-size:1.1rem;">{{ $user->name }}</div>
                    <div style="font-size:.82rem; color:var(--muted);">{{ $user->email }}</div>
                </div>
            </div>

            {{-- Form Edit Profil --}}
            <div class="card">
                <div class="card-header-vb"><i class="bi bi-person-gear"></i> Informasi Profil</div>

                <form method="POST" action="{{ route('profile.update') }}">
                    @csrf

                    {{-- Nama --}}
                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name', $user->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Email (readonly — tidak dikirim ke server, backend pakai Auth::user()->email) --}}
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control"
                               value="{{ $user->email }}" readonly
                               style="background-color: var(--bg-muted, #f0f0f0); cursor: not-allowed; opacity: .75;">
                        <small class="form-text text-muted">Email tidak dapat diubah.</small>
                    </div>

                    {{-- Nomor HP --}}
                    <div class="mb-3">
                        <label class="form-label">Nomor HP</label>
                        <input type="text" name="phone_number" class="form-control @error('phone_number') is-invalid @enderror"
                               value="{{ old('phone_number', $user->phone_number) }}" placeholder="Contoh: 08123456789">
                        @error('phone_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Alamat --}}
                    <div class="mb-3">
                        <label class="form-label">Alamat</label>
                        <textarea name="address" class="form-control @error('address') is-invalid @enderror"
                                  rows="3" placeholder="Alamat lengkap untuk pengiriman">{{ old('address', $user->address) }}</textarea>
                        @error('address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <hr style="border-color:var(--border);">

                    {{-- Password --}}
                    <div class="mb-3">
                        <label class="form-label">Password Baru <span style="font-size:.78rem; color:var(--muted);">(kosongkan jika tidak ingin mengubah)</span></label>
                        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
                               placeholder="Minimal 6 karakter">
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Konfirmasi Password --}}
                    <div class="mb-4">
                        <label class="form-label">Konfirmasi Password Baru</label>
                        <input type="password" name="password_confirmation" class="form-control"
                               placeholder="Ulangi password baru">
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-check-circle me-2"></i>Simpan Perubahan
                    </button>
                </form>
            </div>

        </div>
    </div>
</div>
@endsection
