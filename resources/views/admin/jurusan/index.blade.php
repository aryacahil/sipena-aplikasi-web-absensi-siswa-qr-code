@extends('layouts.admin')

@section('content')
<div class="bg-primary pt-10 pb-21"></div>
<div class="container-fluid mt-n22 px-6">
    <div class="row">
        <div class="col-lg-12 col-md-12 col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div class="mb-2 mb-lg-0">
                    <h3 class="mb-0 text-white">Manajemen Jurusan</h3>
                </div>
                <div>
                    <a href="{{ route('admin.jurusan.create') }}" class="btn btn-white">
                        <i class="bi bi-plus-circle me-2"></i>Tambah Jurusan
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-6">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">Daftar Jurusan</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Kode Jurusan</th>
                                    <th>Nama Jurusan</th>
                                    <th>Deskripsi</th>
                                    <th>Jumlah Kelas</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($jurusans as $index => $jurusan)
                                <tr>
                                    <td>{{ $jurusans->firstItem() + $index }}</td>
                                    <td><span class="badge bg-primary">{{ $jurusan->kode_jurusan }}</span></td>
                                    <td><strong>{{ $jurusan->nama_jurusan }}</strong></td>
                                    <td>{{ Str::limit($jurusan->deskripsi ?? '-', 50) }}</td>
                                    <td>
                                        <span class="badge bg-info">{{ $jurusan->kelas_count }} Kelas</span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('admin.jurusan.show', $jurusan->id) }}" 
                                               class="btn btn-sm btn-info" 
                                               title="Detail">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.jurusan.edit', $jurusan->id) }}" 
                                               class="btn btn-sm btn-warning" 
                                               title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form action="{{ route('admin.jurusan.destroy', $jurusan->id) }}" 
                                                  method="POST" 
                                                  class="d-inline"
                                                  onsubmit="return confirm('Yakin ingin menghapus jurusan ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="btn btn-sm btn-danger" 
                                                        title="Hapus">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4">
                                        <i class="bi bi-inbox fs-1 text-muted"></i>
                                        <p class="text-muted mt-2">Tidak ada data jurusan</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-4">
                        {{ $jurusans->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection