@extends('layouts.auth')

@section('content')
<div class="container d-flex flex-column">
    <div class="row align-items-center justify-content-center g-0
        min-vh-100">
      <div class="col-12 col-md-8 col-lg-6 col-xxl-4 py-8 py-xl-0">
        <!-- Card -->
        <div class="card smooth-shadow-md">
          <!-- Card body -->
          <div class="card-body p-6">
            <div class="text-center mb-4">
                <img src="{{ asset('admin_assets/images/brand/logo/logo_sekolah.png') }}"
                        alt="Logo SMK Bendo"
                        class="rounded-circle shadow-sm mb-3"
                        width="100">

                            <h4 class="fw-bold mb-1">SMK BENDO</h4>
                            <p class="text-muted small">Sistem Informasi Absensi</p>
                        </div>

            <!-- Form -->
            <form method="POST" action="{{ route('register') }}">
                @csrf

              <!-- Username -->
              <div class="mb-3">
                <label for="username" class="form-label">{{ __('Nama') }}</label>
                <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}"  autocomplete="name" autofocus>
                @error('name')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
              </div>
              <!-- Email -->
              <div class="mb-3">
                <label for="email" class="form-label">{{ __('Email') }}</label>
                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}"  autocomplete="email">

                @error('email')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
              </div>
              <!-- Password -->
              <div class="mb-3">
                <label for="password" class="form-label">{{ __('Password') }}</label>
                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password"  autocomplete="new-password">
                @error('password')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
              </div>
              <!-- Password -->
              <div class="mb-3">
                <label for="confirm-password" class="form-label">{{ __('Konfirmasi Password') }}</label>
                <input id="password-confirm" type="password" class="form-control" name="password_confirmation"  autocomplete="new-password">
              </div>
              <div>
                <!-- Button -->
                <div class="d-grid">
                  <button type="submit" class="btn btn-primary">
                    {{ __('Registrasi') }}
                  </button>
                </div>

                <div class="d-md-flex justify-content-between mt-4">
                  <div class="mb-2 mb-md-0">
                    <a href="{{ route('login') }}" class="fs-5">Sudah punya akun? Login </a>
                  </div>
                  <div>
                    <a href="forget-password.html" class="text-inherit
                        fs-5">Lupa Password?</a>
                  </div>

                </div>
              </div>

            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection
