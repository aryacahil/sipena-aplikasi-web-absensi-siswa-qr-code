<div class="header @@classList">
    <!-- navbar -->
    <nav class="navbar-classic navbar navbar-expand-lg">
        <a id="nav-toggle" href="#"><i data-feather="List" class="bi bi-list me-2 icon-xs"></i></a>
        <div class="ms-lg-3 d-none d-md-none d-lg-block">
            <!-- Form -->
            <form class="d-flex align-items-center" id="searchForm">
                <input type="search" class="form-control" placeholder="Search" id="searchInput" autocomplete="off" />
                <div id="searchResults" class="search-results-dropdown"></div>
            </form>
        </div>
        
        <ul class="navbar-nav navbar-right-wrap ms-auto d-flex nav-top-wrap">
            
            <li class="dropdown ms-2">
                <a class="rounded-circle" href="#" role="button" id="dropdownUser" data-bs-toggle="dropdown"
                    aria-haspopup="true" aria-expanded="false">
                    <div class="avatar avatar-md avatar-indicators avatar-online">
                        <img alt="avatar" src="{{ Avatar::create(Auth::user()->name)->toBase64() }}"
                            class="rounded-circle" />
                    </div>
                </a>
                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownUser">
                    <div class="px-4 pb-0 pt-2">
                        <div class="lh-1 ">
                            <h5 class="mb-1">{{ Auth::user()->name }} </h5>
                            <a href="#" class="text-inherit fs-6">Role: {{ ucfirst(Auth::user()->role) }}</a>
                        </div>
                        <div class=" dropdown-divider mt-3 mb-2"></div>
                    </div>
                    <ul class="list-unstyled">
                        <li>
                            <a class="dropdown-item" href="{{ route('logout') }}"
                                onclick="event.preventDefault(); document.getElementById('logout-form').submit();">

                                <i class="bi bi-power me-2 icon-xxs dropdown-item-icon" data-feather="Power"></i>{{ __('Logout') }}
                            </a>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                @csrf
                            </form>
                        </li>
                    </ul>

                </div>
            </li>
        </ul>
    </nav>
</div>

<style>
.search-results-dropdown {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border: 1px solid #ddd;
    border-radius: 0.25rem;
    margin-top: 0.25rem;
    max-height: 300px;
    overflow-y: auto;
    z-index: 1000;
    display: none;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.search-results-dropdown.show {
    display: block;
}

.search-result-item {
    padding: 0.75rem 1rem;
    cursor: pointer;
    border-bottom: 1px solid #f0f0f0;
    transition: background-color 0.2s;
}

.search-result-item:hover {
    background-color: #f8f9fa;
}

.search-result-item:last-child {
    border-bottom: none;
}

.search-result-title {
    font-weight: 500;
    color: #333;
    margin-bottom: 0.25rem;
}

.search-result-description {
    font-size: 0.875rem;
    color: #6c757d;
}

.no-results {
    padding: 1rem;
    text-align: center;
    color: #6c757d;
}

#searchForm {
    position: relative;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const searchResults = document.getElementById('searchResults');
    const searchForm = document.getElementById('searchForm');
    const userRole = '{{ Auth::user()->role }}';

    // Daftar menu berdasarkan role
    const menuItemsByRole = {
    'admin': [
        { 
            title: 'Dashboard', 
            url: '{{ route("admin.home") }}', 
            keywords: ['dashboard', 'home', 'beranda', 'utama', 'halaman utama']
        },
        // MANAJEMEN
        { 
            title: 'Manajemen User', 
            url: '{{ route("admin.users.index") }}', 
            keywords: ['manajemen user', 'user', 'pengguna', 'users', 'kelola user', 'daftar user', 'data user', 'manage user']
        },
        { 
            title: 'Manajemen Jurusan', 
            url: '{{ route("admin.jurusan.index") }}', 
            keywords: ['manajemen jurusan', 'jurusan', 'program studi', 'prodi', 'kelola jurusan', 'daftar jurusan', 'data jurusan', 'major', 'department']
        },
        { 
            title: 'Manajemen Kelas', 
            url: '{{ route("admin.kelas.index") }}', 
            keywords: ['manajemen kelas', 'kelas', 'class', 'ruang kelas', 'kelola kelas', 'daftar kelas', 'data kelas', 'classroom']
        },
        { 
            title: 'Manajemen Absensi', 
            url: '{{ route("admin.presensi.index") }}', 
            keywords: ['manajemen absensi', 'absensi', 'attendance', 'presensi', 'kehadiran', 'kelola absensi', 'data absensi', 'absen']
        },
        // ABSENSI
        { 
            title: 'Generate QR Code', 
            url: '{{ route("admin.qrcode.index") }}', 
            keywords: ['generate qr code', 'qr code', 'qr', 'barcode', 'buat qr', 'generate qr', 'kode qr', 'bikin qr']
        },
        // LAPORAN
        { 
            title: 'Ekspor & Impor', 
            url: '{{ route("admin.export-import.index") }}', 
            keywords: ['ekspor impor', 'ekspor', 'impor', 'export', 'import', 'export import', 'download', 'upload', 'laporan']
        },
        // PENGATURAN
        { 
            title: 'Notifikasi Whatsapp', 
            url: '{{ route("admin.settings.whatsapp.index") }}', 
            keywords: ['notifikasi whatsapp', 'whatsapp', 'wa', 'notif wa', 'pesan whatsapp', 'pengaturan whatsapp', 'setting wa']
        },
        { 
            title: 'Profil Sekolah', 
            url: '{{ route("admin.settings.index") }}', 
            keywords: ['profil sekolah', 'profil', 'profile', 'sekolah', 'school', 'data sekolah', 'info sekolah', 'pengaturan sekolah']
        },
    ],
    'guru': [
        { 
            title: 'Dashboard', 
            url: '{{ route("guru.home") }}', 
            keywords: ['dashboard', 'home', 'beranda', 'utama', 'halaman utama']
        },
        { 
            title: 'Manajemen Absensi', 
            url: '{{ route("guru.presensi.index") }}', 
            keywords: ['manajemen absensi', 'absensi', 'attendance', 'presensi', 'kehadiran', 'kelola absensi', 'data absensi', 'absen']
        },
        { 
            title: 'Generate QR Code', 
            url: '{{ route("guru.qrcode.index") }}', 
            keywords: ['generate qr code', 'qr code', 'qr', 'barcode', 'buat qr', 'generate qr', 'kode qr', 'bikin qr']
        },
        { 
            title: 'Ekspor & Impor', 
            url: '{{ route("guru.export-import.index") }}', 
            keywords: ['ekspor impor', 'ekspor', 'impor', 'export', 'import', 'export import', 'download', 'upload', 'laporan']
        },
    ],
    'siswa': [
        { 
            title: 'Dashboard', 
            url: '{{ route("siswa.home") }}', 
            keywords: ['dashboard', 'home', 'beranda', 'utama', 'halaman utama']
        },
        { 
            title: 'Scan QR Code', 
            url: '{{ route("siswa.presensi.index") }}', 
            keywords: ['scan qr code', 'scan qr', 'qr code', 'qr', 'scan', 'pindai qr', 'absen qr']
        },
    ]
};

    // Ambil menu sesuai role user
    const menuItems = menuItemsByRole[userRole] || [];

    searchInput.addEventListener('input', function() {
        const query = this.value.toLowerCase().trim();
        
        if (query.length === 0) {
            searchResults.classList.remove('show');
            return;
        }

        // Filter menu berdasarkan query
        const filteredItems = menuItems.filter(item => {
            return item.keywords.some(keyword => keyword.includes(query)) ||
                   item.title.toLowerCase().includes(query);
        });

        // Tampilkan hasil
        if (filteredItems.length > 0) {
            let html = '';
            filteredItems.forEach(item => {
                html += `
                    <div class="search-result-item" data-url="${item.url}">
                        <div class="search-result-title">${item.title}</div>
                    </div>
                `;
            });
            searchResults.innerHTML = html;
            searchResults.classList.add('show');

            // Add click handlers
            document.querySelectorAll('.search-result-item').forEach(el => {
                el.addEventListener('click', function() {
                    window.location.href = this.dataset.url;
                });
            });
        } else {
            searchResults.innerHTML = '<div class="no-results">Tidak ada hasil ditemukan</div>';
            searchResults.classList.add('show');
        }
    });

    // Handle form submit
    searchForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const query = searchInput.value.toLowerCase().trim();
        
        // Cari item pertama yang cocok
        const matchedItem = menuItems.find(item => {
            return item.keywords.some(keyword => keyword.includes(query)) ||
                   item.title.toLowerCase().includes(query);
        });

        if (matchedItem) {
            window.location.href = matchedItem.url;
        }
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!searchForm.contains(e.target)) {
            searchResults.classList.remove('show');
        }
    });

    // Handle keyboard navigation
    searchInput.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            searchResults.classList.remove('show');
            searchInput.blur();
        }
    });
});
</script>