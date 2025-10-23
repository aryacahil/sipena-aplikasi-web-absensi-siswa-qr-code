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
                 <a class="nav-link" href="{{ route('siswa.home') }}">
                     <i data-feather="House door" class="bi bi-house-door icon-xs me-2"></i>Dashboard
                 </a>

             </li>
             <!-- Nav item -->
            <li class="nav-item">
                 <div class="navbar-heading">Absensi</div>
             </li>
            </li>
            
            <li class="nav-item">
                <a class="nav-link has-arrow " href="{{ route('siswa.presensi.scan') }}"
                    aria-controls="navPages">
                    <i data-feather="QR code scan" class="bi bi-qr-code-scan icon-xs me-2">
                    </i> Scan Absensi
                </a>

            {{-- <li class="nav-item">
                <a class="nav-link has-arrow " href="#!"
                    aria-controls="navPages">
                    <i data-feather="File text" class="bi bi-file-text icon-xs me-2">
                    </i> Absensi Siswa
                </a> --}}

            {{-- <li class="nav-item">
                 <div class="navbar-heading">Laporan</div>
             </li>
            </li>
            <li class="nav-item">
                <a class="nav-link has-arrow " href="#!"
                    aria-controls="navPages">
                    <i data-feather="File earmark text" class="bi bi-file-earmark-text icon-xs me-2">
                    </i> Export Excel / PDF
                </a> --}}

             
             <!-- <li class="nav-item">
                 <div class="navbar-heading">Pengaturan</div>
             </li>

            <li class="nav-item">
                <a class="nav-link has-arrow " href="#!"
                    aria-controls="navPages">
                    <i data-feather="Building" class="bi bi-building icon-xs me-2">
                    </i> Profil Sekolah
                </a>

            <li class="nav-item">
                 <a class="nav-link has-arrow  " href="#!"
                     data-bs-toggle="collapse" data-bs-target="#navAuthentication" aria-expanded="false"
                     aria-controls="navAuthentication">
                     <i data-feather="Lock" class="bi bi-lock icon-xs me-2">
                     </i> Autentikasi
                 </a>
                 <div id="navAuthentication" class="collapse  "
                     data-bs-parent="#sideNavbar">
                     <ul class="nav flex-column">
                         <li class="nav-item">
                             <a class="nav-link  "
                                 href="pages/sign-in.html"> Sign In</a>
                         </li>
                         <li class="nav-item">
                             <a class="nav-link  "
                                 href="pages/sign-up.html"> Sign Up</a>
                         </li>
                         <li class="nav-item">
                             <a class="nav-link  "
                                 href="pages/forget-password.html">
                                 Forget Password
                             </a>
                         </li>

                     </ul>
                 </div> -->
             </li>
         </ul>
     </div>
 </nav>
