@extends('layouts.app')
@section('title', 'Login — Viera Bakery')
@section('content')

<div class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-logo">Viera Bakery</div>
        <p class="auth-subtitle">Masuk ke akun Anda untuk mulai memesan</p>

        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="mb-3">
                <label class="form-label" for="email">Alamat Email</label>
                <input type="email" id="email" name="email"
                       class="form-control @error('email') is-invalid @enderror"
                       value="{{ old('email') }}"
                       placeholder="nama@email.com" required autofocus>
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label" for="password">Password</label>
                <input type="password" id="password" name="password"
                       class="form-control @error('password') is-invalid @enderror"
                       placeholder="••••••••" required>
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-4">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember">
                    <label class="form-check-label" for="remember" style="font-size:.875rem;">
                        Ingat saya
                    </label>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 mb-3">
                <i class="bi bi-box-arrow-in-right me-2"></i>Masuk
            </button>
        </form>

        <div class="auth-divider">atau</div>

        <p class="text-center mb-0" style="font-size:.875rem; color: var(--muted);">
            Belum punya akun?
            <a href="{{ route('register') }}" class="fw-600" style="color: var(--orange);">Daftar di sini</a>
        </p>
    </div>
</div>

@endsection
