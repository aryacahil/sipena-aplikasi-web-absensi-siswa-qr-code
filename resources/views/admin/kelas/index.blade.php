@extends('layouts.admin')

@section('content')
<div class="bg-primary pt-10 pb-21"></div>
<div class="container-fluid mt-n22 px-6">
    <div class="row">
        <div class="col-lg-12 col-md-12 col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div class="mb-2 mb-lg-0">
                    <h3 class="mb-0 text-white">Manajemen Kelas</h3>
                </div>
                <div>
                    <a href="{{ route('admin.kelas.create') }}" class="btn btn-white">
                        <i class="bi bi-plus-circle me-2"></i>Tambah Kelas
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-6">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">Daftar Kelas</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Tingkat</th>
                                    <th>Nama Kelas</th>
                                    <th>Jurusan</th>
                                    <th>Wali Kelas</th>
                                    <th>Kapasitas</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($kelas as $index => $k)
                                <tr>
                                    <td>{{ $kelas->firstItem() + $index }}</td>
                                    <td><span class="badge bg-primary">{{ $k->tingkat }}</span></td>
                                    <td>{{ $k->nama_kelas }}</td>
                                    <td>
                                        <span class="badge bg-info">{{ $k->jurusan->kode_jurusan }}</span>
                                        {{ $k->jurusan->nama_jurusan }}
                                    </td>
                                    <td>{{ $k->waliKelas->name ?? '-' }}</td>
                                    <td>{{ $k->kapasitas }} Siswa</td>
                                    <td>
                                        @if($k->status == 'active')
                                            <span class="badge bg-success">Aktif</span>
                                        @else
                                            <span class="badge bg-secondary">Nonaktif</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('admin.kelas.show', $k->id) }}" 
                                               class="btn btn-sm btn-info" 
                                               title="Detail">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.kelas.edit', $k->id) }}" 
                                               class="btn btn-sm btn-warning" 
                                               title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form action="{{ route('admin.kelas.destroy', $k->id) }}" 
                                                  method="POST" 
                                                  class="d-inline"
                                                  onsubmit="return confirm('Yakin ingin menghapus kelas ini?')">
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
                                    <td colspan="8" class="text-center py-4">
                                        <i class="bi bi-inbox fs-1 text-muted"></i>
                                        <p class="text-muted mt-2">Tidak ada data kelas</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-4">
                        {{ $kelas->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    @if(session('success'))
        alert('{{ session("success") }}');
    @endif

    @if(session('error'))
        alert('{{ session("error") }}');
    @endif
</script>
@endpush
@endsection