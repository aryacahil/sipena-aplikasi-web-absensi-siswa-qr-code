@extends('layouts.admin')

@section('content')
<div class="bg-primary pt-10 pb-21"></div>
<div class="container-fluid mt-n22 px-6">
    <div class="row">
        <div class="col-lg-12 col-md-12 col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div class="mb-2 mb-lg-0">
                    <h3 class="mb-0 text-white">Detail User</h3>
                </div>
                <div>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-white">
                        <i class="bi bi-arrow-left me-2"></i>Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-6">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <img src="{{ Avatar::create($user->name)->toBase64() }}" 
                         alt="{{ $user->name }}" 
                         class="rounded-circle mb-3" 
                         width="150" 
                         height="150">
                    <h4 class="mb-1">{{ $user->name }}</h4>
                    <p class="text-muted mb-3">{{ $user->email }}</p>
                    
                    @if($user->role == 'admin')
                        <span class="badge bg-danger fs-6">Admin</span>
                    @elseif($user->role == 'guru')
                        <span class="badge bg-primary fs-6">Guru</span>
                    @else
                        <span class="badge bg-success fs-6">Siswa</span>
                    @endif
                    
                    @if($user->status == 'active')
                        <span class="badge bg-success fs-6 ms-2">Aktif</span>
                    @else
                        <span class="badge bg-secondary fs-6 ms-2">Nonaktif</span>
                    @endif
                    
                    @if($user->kelas)
                        <div class="mt-3">
                            <span class="badge bg-secondary fs-6">{{ $user->kelas->nama_kelas }}</span>
                        </div>
                    @endif
                    
                    <div class="mt-4">
                        <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-primary btn-sm">
                            <i class="bi bi-pencil me-1"></i>Edit
                        </a>
                        <form action="{{ route('admin.users.destroy', $user->id) }}" 
                              method="POST" 
                              class="d-inline"
                              onsubmit="return confirm('Yakin ingin menghapus user ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">
                                <i class="bi bi-trash me-1"></i>Hapus
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Informasi User</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tbody>
                            <tr>
                                <td width="30%" class="fw-bold">Nama Lengkap</td>
                                <td>{{ $user->name }}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Email</td>
                                <td>{{ $user->email }}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Role</td>
                                <td>
                                    @if($user->role == 'admin')
                                        <span class="badge bg-danger">Admin</span>
                                    @elseif($user->role == 'guru')
                                        <span class="badge bg-primary">Guru</span>
                                    @else
                                        <span class="badge bg-success">Siswa</span>
                                    @endif
                                </td>
                            </tr>
                            @if($user->role == 'siswa' && $user->kelas)
                            <tr>
                                <td class="fw-bold">Kelas</td>
                                <td>
                                    <span class="badge bg-secondary">{{ $user->kelas->kode_kelas }}</span>
                                    {{ $user->kelas->nama_kelas }}
                                    <br>
                                    <small class="text-muted">
                                        <span class="badge bg-primary mt-1">{{ $user->kelas->jurusan->kode_jurusan }}</span>
                                        {{ $user->kelas->jurusan->nama_jurusan }}
                                    </small>
                                    <br>
                                    <span class="badge bg-success mt-1">Tingkat {{ $user->kelas->tingkat }}</span>
                                </td>
                            </tr>
                            @endif
                            @if($user->role == 'siswa' && $user->kelas && $user->kelas->waliKelas)
                            <tr>
                                <td class="fw-bold">Wali Kelas</td>
                                <td>{{ $user->kelas->waliKelas->name }}</td>
                            </tr>
                            @endif
                            @if($user->role == 'siswa' && $user->parent_phone)
                            <tr>
                                <td class="fw-bold">No. Telepon Orang Tua</td>
                                <td>
                                    <i class="bi bi-whatsapp text-success me-1"></i>
                                    {{ $user->parent_phone }}
                                </td>
                            </tr>
                            @endif
                            <tr>
                                <td class="fw-bold">Status</td>
                                <td>
                                    @if($user->status == 'active')
                                        <span class="badge bg-success">Aktif</span>
                                    @else
                                        <span class="badge bg-secondary">Nonaktif</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Terdaftar Sejak</td>
                                <td>{{ $user->created_at->format('d M Y H:i') }}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Terakhir Update</td>
                                <td>{{ $user->updated_at->format('d M Y H:i') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            @if($user->role == 'siswa' && $user->kelas)
            <!-- Info Teman Sekelas -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">Teman Sekelas</h5>
                </div>
                <div class="card-body">
                    @if($user->kelas->siswa->count() > 1)
                    <div class="row">
                        @foreach($user->kelas->siswa->where('id', '!=', $user->id)->take(6) as $teman)
                        <div class="col-md-4 mb-3">
                            <div class="d-flex align-items-center">
                                <img src="{{ Avatar::create($teman->name)->toBase64() }}" 
                                     alt="{{ $teman->name }}" 
                                     class="rounded-circle me-2" 
                                     width="40" 
                                     height="40">
                                <div>
                                    <small class="fw-bold d-block">{{ $teman->name }}</small>
                                    <small class="text-muted">{{ $teman->email }}</small>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @if($user->kelas->siswa->count() > 7)
                    <div class="text-center mt-3">
                        <a href="{{ route('admin.kelas.show', $user->kelas->id) }}" class="btn btn-sm btn-outline-primary">
                            Lihat Semua ({{ $user->kelas->siswa->count() - 1 }} siswa)
                        </a>
                    </div>
                    @endif
                    @else
                    <div class="text-center py-3">
                        <i class="bi bi-people fs-1 text-muted"></i>
                        <p class="text-muted mt-2">Belum ada teman sekelas</p>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            @if($user->role == 'guru')
            <!-- Info Kelas yang Diampu -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">Kelas yang Diampu</h5>
                </div>
                <div class="card-body">
                    @php
                        $kelasWali = \App\Models\Kelas::where('wali_kelas_id', $user->id)->get();
                    @endphp
                    
                    @if($kelasWali->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Kelas</th>
                                    <th>Jurusan</th>
                                    <th>Tingkat</th>
                                    <th>Jumlah Siswa</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($kelasWali as $kelas)
                                <tr>
                                    <td>
                                        <a href="{{ route('admin.kelas.show', $kelas->id) }}">
                                            {{ $kelas->nama_kelas }}
                                        </a>
                                    </td>
                                    <td>{{ $kelas->jurusan->nama_jurusan }}</td>
                                    <td><span class="badge bg-success">Kelas {{ $kelas->tingkat }}</span></td>
                                    <td>{{ $kelas->siswa->count() }} siswa</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-3">
                        <i class="bi bi-easel fs-1 text-muted"></i>
                        <p class="text-muted mt-2">Belum menjadi wali kelas</p>
                    </div>
                    @endif
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection