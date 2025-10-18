@extends('layouts.admin')

@section('content')
<div class="bg-primary pt-10 pb-21"></div>
<div class="container-fluid mt-n22 px-6">
    <div class="row">
        <div class="col-lg-12 col-md-12 col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div class="mb-2 mb-lg-0">
                    <h3 class="mb-0 text-white">Detail Jurusan</h3>
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
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Informasi Jurusan</h5>
                    <div>
                        <a href="{{ route('admin.jurusan.edit', $jurusan->id) }}" class="btn btn-primary btn-sm">
                            <i class="bi bi-pencil me-1"></i>Edit
                        </a>
                        <form action="{{ route('admin.jurusan.destroy', $jurusan->id) }}" 
                              method="POST" 
                              class="d-inline"
                              onsubmit="return confirm('Yakin ingin menghapus jurusan ini?')">
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
                                <td width="30%" class="fw-bold">Kode Jurusan</td>
                                <td><span class="badge bg-primary fs-6">{{ $jurusan->kode_jurusan }}</span></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Nama Jurusan</td>
                                <td>{{ $jurusan->nama_jurusan }}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Deskripsi</td>
                                <td>{{ $jurusan->deskripsi ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Jumlah Kelas</td>
                                <td><span class="badge bg-info">{{ $jurusan->kelas->count() }} Kelas</span></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Dibuat</td>
                                <td>{{ $jurusan->created_at->format('d M Y H:i') }}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Terakhir Update</td>
                                <td>{{ $jurusan->updated_at->format('d M Y H:i') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Daftar Kelas -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Daftar Kelas</h5>
                </div>
                <div class="card-body">
                    @if($jurusan->kelas->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Kode Kelas</th>
                                    <th>Nama Kelas</th>
                                    <th>Tingkat</th>
                                    <th>Wali Kelas</th>
                                    <th>Jumlah Siswa</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($jurusan->kelas as $index => $kelas)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td><span class="badge bg-secondary">{{ $kelas->kode_kelas }}</span></td>
                                    <td>{{ $kelas->nama_kelas }}</td>
                                    <td>{{ $kelas->tingkat }}</td>
                                    <td>{{ $kelas->waliKelas->name ?? '-' }}</td>
                                    <td><span class="badge bg-success">{{ $kelas->siswa->count() }} Siswa</span></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-4">
                        <i class="bi bi-inbox fs-1 text-muted"></i>
                        <p class="text-muted mt-2">Belum ada kelas pada jurusan ini</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection