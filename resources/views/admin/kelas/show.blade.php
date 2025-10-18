@extends('layouts.admin')

@section('content')
<div class="bg-primary pt-10 pb-21"></div>
<div class="container-fluid mt-n22 px-6">
    <div class="row">
        <div class="col-lg-12 col-md-12 col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div class="mb-2 mb-lg-0">
                    <h3 class="mb-0 text-white">Detail Kelas</h3>
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
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Informasi Kelas</h5>
                    <div>
                        <a href="{{ route('admin.kelas.edit', $kela->id) }}" class="btn btn-primary btn-sm">
                            <i class="bi bi-pencil me-1"></i>Edit
                        </a>
                        <form action="{{ route('admin.kelas.destroy', $kela->id) }}" 
                              method="POST" 
                              class="d-inline"
                              onsubmit="return confirm('Yakin ingin menghapus kelas ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">
                                <i class="bi bi-trash me-1"></i>Hapus
                            </button>
                        </form>
                    </div>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tbody>
                            <tr>
                                <td width="30%" class="fw-bold">Kode Kelas</td>
                                <td><span class="badge bg-secondary fs-6">{{ $kela->kode_kelas }}</span></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Nama Kelas</td>
                                <td>{{ $kela->nama_kelas }}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Jurusan</td>
                                <td>
                                    <span class="badge bg-primary">{{ $kela->jurusan->kode_jurusan }}</span>
                                    {{ $kela->jurusan->nama_jurusan }}
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Tingkat</td>
                                <td><span class="badge bg-success fs-6">Kelas {{ $kela->tingkat }}</span></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Wali Kelas</td>
                                <td>{{ $kela->waliKelas->name ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Jumlah Siswa</td>
                                <td><span class="badge bg-info">{{ $kela->siswa->count() }} Siswa</span></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Dibuat</td>
                                <td>{{ $kela->created_at->format('d M Y H:i') }}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Terakhir Update</td>
                                <td>{{ $kela->updated_at->format('d M Y H:i') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Daftar Siswa -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Daftar Siswa</h5>
                </div>
                <div class="card-body">
                    @if($kela->siswa->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Foto</th>
                                    <th>Nama</th>
                                    <th>Email</th>
                                    <th>No. Telp Orang Tua</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($kela->siswa as $index => $siswa)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        <img src="{{ Avatar::create($siswa->name)->toBase64() }}" 
                                             alt="{{ $siswa->name }}" 
                                             class="rounded-circle" 
                                             width="40" 
                                             height="40">
                                    </td>
                                    <td>{{ $siswa->name }}</td>
                                    <td>{{ $siswa->email }}</td>
                                    <td>{{ $siswa->parent_phone ?? '-' }}</td>
                                    <td>
                                        @if($siswa->status == 'active')
                                            <span class="badge bg-success">Aktif</span>
                                        @else
                                            <span class="badge bg-secondary">Nonaktif</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-4">
                        <i class="bi bi-inbox fs-1 text-muted"></i>
                        <p class="text-muted mt-2">Belum ada siswa pada kelas ini</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection