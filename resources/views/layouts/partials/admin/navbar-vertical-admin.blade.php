<!-- Sidebar -->
 <nav class="navbar-vertical navbar">
     <div class="nav-scroller">
         <!-- Brand logo -->
         <a class="navbar-brand">
             <img src="{{ asset('admin_assets/images/brand/logo/logo_sekolah.png') }}" alt="" />
             <span class="ms-2 fw-semibold text-white" style="font-size:16px; letter-spacing:0.5px;">
                SMKN 1 BENDO
            </span>
         </a>
         <!-- Navbar nav -->
         <ul class="navbar-nav flex-column" id="sideNavbar">
             <li class="nav-item">
                 <a class="nav-link" href="{{ route('admin.home') }}">
                     <i data-feather="House door" class="bi bi-house-door icon-xs me-2"></i>Dashboard
                 </a>
             </li>

             <!-- Nav item -->
             <li class="nav-item">
                 <div class="navbar-heading">Manajemen</div>
             </li>

             <!-- Manajemen User -->
             <li class="nav-item">
                <a class="nav-link has-arrow" href="#!" data-bs-toggle="collapse" 
                   data-bs-target="#navUsers" aria-expanded="false" aria-controls="navUsers">
                    <i data-feather="People" class="bi bi-people icon-xs me-2"></i>
                    Manajemen User
                </a>
                <div id="navUsers" class="collapse" data-bs-parent="#sideNavbar">
                    <ul class="nav flex-column">
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.users.index', ['role' => '1']) }}">
                                Admin
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.users.index', ['role' => '0']) }}">
                                Guru
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.users.index', ['role' => '2']) }}">
                                Siswa
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

             <!-- Manajemen Jurusan -->
             <li class="nav-item">
                <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.jurusan.index') }}">
                    <i data-feather="Book" class="bi bi-book icon-xs me-2"></i>
                    Manajemen Jurusan
                </a>
            </li>

             <!-- Manajemen Kelas -->
             <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.kelas.index') }}">
                    <i data-feather="Easel" class="bi bi-easel icon-xs me-2"></i>
                    Manajemen Kelas
                </a>
            </li>

             <!-- Absensi Section -->
             <li class="nav-item">
                 <div class="navbar-heading">Absensi</div>
             </li>

             <li class="nav-item">
                <a class="nav-link" href="#">
                    <i data-feather="QR code" class="bi bi-qr-code icon-xs me-2"></i>
                    Generate QR Code
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link" href="#">
                    <i data-feather="QR code scan" class="bi bi-qr-code-scan icon-xs me-2"></i>
                    Scan Absensi
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="#">
                    <i data-feather="File text" class="bi bi-file-text icon-xs me-2"></i>
                    Absensi Siswa
                </a>
            </li>

             <!-- Laporan Section -->
             <li class="nav-item">
                 <div class="navbar-heading">Laporan</div>
             </li>

            <li class="nav-item">
                <a class="nav-link" href="#">
                    <i data-feather="File earmark text" class="bi bi-file-earmark-text icon-xs me-2"></i>
                    Export Excel / PDF
                </a>
            </li>

             <!-- Pengaturan Section -->
             <li class="nav-item">
                 <div class="navbar-heading">Pengaturan</div>
             </li>

            <li class="nav-item">
                <a class="nav-link" href="#">
                    <i data-feather="Building" class="bi bi-building icon-xs me-2"></i>
                    Profil Sekolah
                </a>
            </li>

            <li class="nav-item">
                 <a class="nav-link has-arrow" href="#!" data-bs-toggle="collapse" 
                    data-bs-target="#navAuthentication" aria-expanded="false" aria-controls="navAuthentication">
                     <i data-feather="Lock" class="bi bi-lock icon-xs me-2"></i>
                     Autentikasi
                 </a>
                 <div id="navAuthentication" class="collapse" data-bs-parent="#sideNavbar">
                     <ul class="nav flex-column">
                         <li class="nav-item">
                             <a class="nav-link" href="#">
                                <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
                             </a>
                         </li>
                         <li class="nav-item">
                             <a class="nav-link" href="#">
                                <i class="bi bi-person-plus me-2"></i>Sign Up
                             </a>
                         </li>
                         <li class="nav-item">
                             <a class="nav-link" href="#">
                                <i class="bi bi-key me-2"></i>Forget Password
                             </a>
                         </li>
                     </ul>
                 </div>
             </li>
         </ul>
     </div>
 </nav>