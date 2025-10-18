@extends('layouts.admin')

@section('content')
<div class="bg-primary pt-10 pb-21"></div>
<div class="container-fluid mt-n22 px-6">
    <div class="row">
        <div class="col-lg-12 col-md-12 col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div class="mb-2 mb-lg-0">
                    <h3 class="mb-0 text-white">Tambah Kelas</h3>
                </div>
                <div>
                    <a href="{{ route('admin.kelas.index') }}" class="btn btn-white">
                        <i class="bi bi-arrow-left me-2"></i>Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-6">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">Form Tambah Kelas</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.kelas.store') }}" method="POST">
                        @csrf
                        
                        <div class="row">
                            <!-- Jurusan -->
                            <div class="col-md-6 mb-3">
                                <label for="jurusan_id" class="form-label">Jurusan <span class="text-danger">*</span></label>
                                <select class="form-select @error('jurusan_id') is-invalid @enderror" 
                                        id="jurusan_id" 
                                        name="jurusan_id" 
                                        required>
                                    <option value="">Pilih Jurusan</option>
                                    @foreach($jurusans as $jurusan)
                                        <option value="{{ $jurusan->id }}" {{ old('jurusan_id') == $jurusan->id ? 'selected' : '' }}>
                                            {{ $jurusan->kode_jurusan }} - {{ $jurusan->nama_jurusan }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('jurusan_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Tingkat -->
                            <div class="col-md-6 mb-3">
                                <label for="tingkat" class="form-label">Tingkat <span class="text-danger">*</span></label>
                                <select class="form-select @error('tingkat') is-invalid @enderror" 
                                        id="tingkat" 
                                        name="tingkat" 
                                        required>
                                    <option value="">Pilih Tingkat</option>
                                    <option value="10" {{ old('tingkat') == '10' ? 'selected' : '' }}>Kelas 10</option>
                                    <option value="11" {{ old('tingkat') == '11' ? 'selected' : '' }}>Kelas 11</option>
                                    <option value="12" {{ old('tingkat') == '12' ? 'selected' : '' }}>Kelas 12</option>
                                </select>
                                @error('tingkat')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <!-- Kode Kelas -->
                            <div class="col-md-6 mb-3">
                                <label for="kode_kelas" class="form-label">Kode Kelas <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control @error('kode_kelas') is-invalid @enderror" 
                                       id="kode_kelas" 
                                       name="kode_kelas" 
                                       value="{{ old('kode_kelas') }}" 
                                       placeholder="Contoh: X-TKJ-1"
                                       required>
                                @error('kode_kelas')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Nama Kelas -->
                            <div class="col-md-6 mb-3">
                                <label for="nama_kelas" class="form-label">Nama Kelas <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control @error('nama_kelas') is-invalid @enderror" 
                                       id="nama_kelas" 
                                       name="nama_kelas" 
                                       value="{{ old('nama_kelas') }}" 
                                       placeholder="Contoh: X TKJ 1"
                                       required>
                                @error('nama_kelas')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Wali Kelas -->
                        <div class="mb-3">
                            <label for="wali_kelas_id" class="form-label">Wali Kelas</label>
                            <select class="form-select @error('wali_kelas_id') is-invalid @enderror" 
                                    id="wali_kelas_id" 
                                    name="wali_kelas_id">
                                <option value="">Pilih Wali Kelas (Opsional)</option>
                                @foreach($gurus as $guru)
                                    <option value="{{ $guru->id }}" {{ old('wali_kelas_id') == $guru->id ? 'selected' : '' }}>
                                        {{ $guru->name }} - {{ $guru->email }}
                                    </option>
                                @endforeach
                            </select>
                            @error('wali_kelas_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-2"></i>Simpan
                            </button>
                            <a href="{{ route('admin.kelas.index') }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle me-2"></i>Batal
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection