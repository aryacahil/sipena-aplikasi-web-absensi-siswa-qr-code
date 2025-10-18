@extends('layouts.admin')

@section('content')
<div class="bg-primary pt-10 pb-21"></div>
<div class="container-fluid mt-n22 px-6">
    <div class="row">
        <div class="col-lg-12 col-md-12 col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div class="mb-2 mb-lg-0">
                    <h3 class="mb-0 text-white">Tambah Jurusan</h3>
                </div>
                <div>
                    <a href="{{ route('admin.jurusan.index') }}" class="btn btn-white">
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
                    <h4 class="mb-0">Form Tambah Jurusan</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.jurusan.store') }}" method="POST">
                        @csrf
                        
                        <div class="row">
                            <!-- Kode Jurusan -->
                            <div class="col-md-6 mb-3">
                                <label for="kode_jurusan" class="form-label">Kode Jurusan <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control @error('kode_jurusan') is-invalid @enderror" 
                                       id="kode_jurusan" 
                                       name="kode_jurusan" 
                                       value="{{ old('kode_jurusan') }}" 
                                       placeholder="Contoh: TKJ"
                                       required>
                                @error('kode_jurusan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Nama Jurusan -->
                            <div class="col-md-6 mb-3">
                                <label for="nama_jurusan" class="form-label">Nama Jurusan <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control @error('nama_jurusan') is-invalid @enderror" 
                                       id="nama_jurusan" 
                                       name="nama_jurusan" 
                                       value="{{ old('nama_jurusan') }}" 
                                       placeholder="Contoh: Teknik Komputer dan Jaringan"
                                       required>
                                @error('nama_jurusan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Deskripsi -->
                        <div class="mb-3">
                            <label for="deskripsi" class="form-label">Deskripsi</label>
                            <textarea class="form-control @error('deskripsi') is-invalid @enderror" 
                                      id="deskripsi" 
                                      name="deskripsi" 
                                      rows="4"
                                      placeholder="Deskripsi singkat tentang jurusan">{{ old('deskripsi') }}</textarea>
                            @error('deskripsi')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-2"></i>Simpan
                            </button>
                            <a href="{{ route('admin.jurusan.index') }}" class="btn btn-secondary">
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