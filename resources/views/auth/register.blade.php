@extends('layouts.app')
@section('title', 'Daftar Akun — Viera Bakery')
@section('content')

<div class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-logo">Viera Bakery</div>
        <p class="auth-subtitle">Daftar akun baru dan mulai pesan kue favoritmu</p>

        <form method="POST" action="{{ route('register') }}">
            @csrf

            <div class="mb-3">
                <label class="form-label" for="name">Nama Lengkap</label>
                <input type="text" id="name" name="name"
                       class="form-control @error('name') is-invalid @enderror"
                       value="{{ old('name') }}"
                       placeholder="Nama lengkap Anda" required autofocus>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label" for="email">Alamat Email</label>
                <input type="email" id="email" name="email"
                       class="form-control @error('email') is-invalid @enderror"
                       value="{{ old('email') }}"
                       placeholder="nama@email.com" required>
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label" for="password">Password</label>
                <input type="password" id="password" name="password"
                       class="form-control @error('password') is-invalid @enderror"
                       placeholder="Minimal 6 karakter" required>
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-4">
                <label class="form-label" for="password_confirmation">Konfirmasi Password</label>
                <input type="password" id="password_confirmation" name="password_confirmation"
                       class="form-control"
                       placeholder="Ulangi password" required>
            </div>

            <button type="submit" class="btn btn-primary w-100 mb-3">
                <i class="bi bi-person-plus me-2"></i>Buat Akun
            </button>
        </form>

        <div class="auth-divider">atau</div>

        <p class="text-center mb-0" style="font-size:.875rem; color: var(--muted);">
            Sudah punya akun?
            <a href="{{ route('login') }}" style="color: var(--orange); font-weight:600;">Masuk di sini</a>
        </p>
    </div>
</div>

@endsection
