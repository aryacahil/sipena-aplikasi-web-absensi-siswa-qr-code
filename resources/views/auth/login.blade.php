@extends('layouts.auth')
@section('content')
    <div class="container d-flex flex-column">
        <div class="row align-items-center justify-content-center g-0 min-vh-100">
            <div class="col-12 col-md-8 col-lg-6 col-xxl-4 py-8 py-xl-0">
                <!-- Card -->
                <div class="card smooth-shadow-md">
                    <!-- Card body -->
                    <div class="card-body p-6">
                        <!-- Logo & Judul -->
                        <div class="text-center mb-4">
                            <img src="{{ \App\Models\SchoolSetting::get()->logo_url }}"
                                 alt="Logo Sekolah"
                                 class="rounded-circle shadow-sm mb-3"
                                 width="100">
                            <h4 class="fw-bold mb-1">{{ \App\Models\SchoolSetting::get()->school_name ?? 'SMKN 1 BENDO MAGETAN' }}</h4>
                            <p class="text-muted small">Aplikasi Web Absensi Berbasis QR CODE</p>
                        </div>

                        <!-- Alert Error -->
                        @if(session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <strong>{{ session('error') }}</strong>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        @if(session('info'))
                            <div class="alert alert-info alert-dismissible fade show" role="alert">
                                <strong>{{ session('info') }}</strong>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <!-- Form Login -->
                        <form method="POST" action="{{ route('login') }}">
                            @csrf
                            
                            <!-- Email / NIS -->
                            <div class="mb-3">
                                <label for="login" class="form-label">Email / NIS</label>
                                <input id="login" type="text"
                                    class="form-control @error('login') is-invalid @enderror"
                                    name="login" 
                                    value="{{ old('login') }}" 
                                    placeholder="Masukkan Email atau NIS"
                                    autocomplete="username" 
                                    autofocus 
                                    required>
                                @error('login')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <!-- Password -->
                            <div class="mb-3">
                                <label for="password" class="form-label">{{ __('Password') }}</label>
                                <input id="password" type="password"
                                    class="form-control @error('password') is-invalid @enderror"
                                    name="password" 
                                    placeholder="Masukkan Password"
                                    autocomplete="current-password"
                                    required>
                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <!-- Button -->
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fe fe-log-in me-2"></i>{{ __('Login') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                <!-- End Card -->
            </div>
        </div>
    </div>
@endsection