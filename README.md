# Aplikasi Web Absensi Siswa Berbasis QR Code (Sipena)

Aplikasi Web Absensi Siswa Berbasis QR Code (Sipena) untuk sekolah dengan fitur QR Code, geolokasi, dan notifikasi WhatsApp otomatis menggunakan Laravel 10.

## 📋 Fitur Utama

### 👨‍💼 Admin & Guru
- **Manajemen Data Master**
  - Kelola data siswa, guru, dan admin
  - Manajemen jurusan dan kelas
  - Pengelompokan siswa per kelas
  - Import/export data siswa via Excel

- **QR Code Presensi**
  - Generate QR Code untuk check-in dan check-out
  - Pengaturan jadwal dan lokasi presensi
  - Validasi radius lokasi (50-1000 meter)
  - Download QR Code dalam format PNG

- **Monitoring Presensi**
  - Dashboard statistik real-time
  - Grafik kehadiran (harian, mingguan, bulanan, tahunan)
  - Filter berdasarkan kelas dan tanggal
  - Input manual untuk izin/sakit/alpha
  - Export laporan presensi ke Excel

- **Notifikasi WhatsApp**
  - Notifikasi otomatis ke orang tua saat check-in/check-out
  - Multi-device support dengan prioritas dan rotasi otomatis
  - Template pesan yang dapat dikustomisasi
  - Monitoring status pengiriman

- **Pengaturan Sistem**
  - Konfigurasi data sekolah dan logo
  - Manajemen tahun ajaran
  - Pengaturan WhatsApp API (Fonnte)

### 👨‍🎓 Siswa
- Scan QR Code untuk presensi
- Validasi lokasi otomatis
- Check-in dan check-out terpisah
- Riwayat presensi

## 📸 Tampilan Aplikasi

### 🔐 Halaman Login
![Login Page](screenshots/login.png)
*Halaman login untuk Admin, Guru, dan Siswa*

---

### 👨‍💼 Dashboard Admin

#### Dashboard Utama
![Admin Dashboard](screenshots/admin-dashboard.png)
*Dashboard dengan statistik dan grafik kehadiran*

#### Manajemen Users
![User Management](screenshots/admin-users.png)
*Kelola data Admin, Guru, dan Siswa*

![Add User](screenshots/admin-users-add.png)
*Form tambah user baru*

#### Manajemen Jurusan
![Jurusan List](screenshots/admin-jurusan.png)
*Daftar jurusan yang tersedia*

![Add Jurusan](screenshots/admin-jurusan-add.png)
*Form tambah jurusan baru*

#### Manajemen Kelas
![Kelas List](screenshots/admin-kelas.png)
*Daftar kelas dengan informasi jumlah siswa*

![Add Kelas](screenshots/admin-kelas-add.png)
*Form tambah kelas baru*

![Kelas Detail](screenshots/admin-kelas-detail.png)
*Detail kelas dengan daftar siswa*

![Add Siswa to Kelas](screenshots/admin-kelas-add-siswa.png)
*Tambah siswa ke kelas*

#### QR Code Management
![QR Code List](screenshots/admin-qrcode-list.png)
*Daftar sesi QR Code yang telah dibuat*

![Create QR Code](screenshots/admin-qrcode-create.png)
*Form pembuatan QR Code dengan pengaturan lokasi*

![QR Code Display](screenshots/admin-qrcode-display.png)
*Tampilan QR Code untuk check-in dan check-out*

#### Monitoring Presensi
![Presensi Dashboard](screenshots/admin-presensi-dashboard.png)
*Dashboard monitoring presensi per kelas*

![Presensi Kelas](screenshots/admin-presensi-kelas.png)
*Detail presensi siswa dalam satu kelas*

![Input Manual Presensi](screenshots/admin-presensi-manual.png)
*Form input manual presensi*

![Edit Presensi](screenshots/admin-presensi-edit.png)
*Edit data presensi*

#### Export & Import
![Export Import](screenshots/admin-export-import.png)
*Halaman export dan import data*

#### Pengaturan WhatsApp
![WhatsApp Settings](screenshots/admin-whatsapp-settings.png)
*Konfigurasi WhatsApp API dan device*

![WhatsApp Device](screenshots/admin-whatsapp-device.png)
*Manajemen WhatsApp device*

![WhatsApp Template](screenshots/admin-whatsapp-template.png)
*Customisasi template pesan WhatsApp*

#### Pengaturan Sistem
![School Settings](screenshots/admin-settings-school.png)
*Pengaturan data sekolah dan logo*

![Academic Year](screenshots/admin-settings-academic.png)
*Manajemen tahun ajaran*

---

### 👨‍🏫 Dashboard Guru

#### Dashboard Utama
![Guru Dashboard](screenshots/guru-dashboard.png)
*Dashboard guru dengan statistik*

#### QR Code Management
![Guru QR Code](screenshots/guru-qrcode.png)
*Generate dan kelola QR Code presensi*

#### Monitoring Presensi
![Guru Presensi](screenshots/guru-presensi.png)
*Monitor presensi siswa*

---

### 👨‍🎓 Dashboard Siswa

#### Dashboard Utama
![Siswa Dashboard](screenshots/siswa-dashboard.png)
*Dashboard siswa dengan informasi presensi*

#### Scan QR Code
![Siswa Scan QR](screenshots/siswa-scan-qr.png)
*Halaman scan QR Code untuk presensi*

![Siswa Camera](screenshots/siswa-camera.png)
*Tampilan kamera saat scan QR Code*

![Siswa Validasi](screenshots/siswa-validasi.png)
*Proses validasi lokasi dan waktu*

![Siswa Success](screenshots/siswa-success.png)
*Notifikasi berhasil presensi*

#### Riwayat Presensi
![Siswa History](screenshots/siswa-history.png)
*Riwayat presensi siswa*

---

### 📱 Notifikasi WhatsApp

#### Notifikasi Check-in
![WA Check-in](screenshots/wa-notif-checkin.png)
*Contoh notifikasi WhatsApp saat check-in*

#### Notifikasi Check-out
![WA Check-out](screenshots/wa-notif-checkout.png)
*Contoh notifikasi WhatsApp saat check-out*

---

### 📊 Laporan & Export

#### Excel Export - Data Siswa
![Export Siswa](screenshots/export-siswa.png)
*Contoh hasil export data siswa*

#### Excel Export - Data Presensi
![Export Presensi](screenshots/export-presensi.png)
*Contoh hasil export data presensi*

---

## 🛠️ Teknologi

- **Backend**: Laravel 10
- **Database**: MySQL
- **Frontend**: Bootstrap 5, jQuery
- **QR Code**: SimpleSoftwareIO/simple-qrcode, chillerlan/php-qrcode
- **Excel**: Maatwebsite/Excel
- **WhatsApp API**: Fonnte
- **Lainnya**: SweetAlert2, Chart.js

## 📦 Instalasi

### Persyaratan Sistem
- PHP >= 8.1
- Composer
- MySQL >= 5.7
- Node.js & NPM (opsional)

### Langkah Instalasi

1. **Clone Repository**
```bash
git clone <repository-url>
cd <project-folder>
```

2. **Install Dependencies**
```bash
composer install
```

3. **Konfigurasi Environment**
```bash
cp .env.example .env
php artisan key:generate
```

4. **Konfigurasi Database**
Edit file `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nama_database
DB_USERNAME=root
DB_PASSWORD=
```

5. **Migrasi Database**
```bash
php artisan migrate --seed
```

6. **Buat Folder Screenshots**
```bash
mkdir public/screenshots
```

7. **Storage Link**
```bash
php artisan storage:link
```

8. **Jalankan Server**
```bash
php artisan serve
```

Aplikasi dapat diakses di: `http://localhost:8000`

## 👤 Default Login

Setelah seeding, gunakan kredensial berikut:

**Admin**
- Email: `admin@admin.com`
- Password: `password`

**Guru**
- Email: `guru@guru.com`
- Password: `password`

**Siswa**
- NIS: `233307037`
- Password: `password`

## 📱 Konfigurasi WhatsApp (Opsional)

1. Daftar akun di [Fonnte.com](https://fonnte.com)
2. Dapatkan API Key dari dashboard Fonnte
3. Login sebagai Admin
4. Masuk ke **Pengaturan > WhatsApp**
5. Tambahkan device baru dengan API Key
6. Test koneksi device
7. Aktifkan notifikasi WhatsApp

### Template Variabel Pesan

**Check-in:**
- `{student_name}` - Nama siswa
- `{nis}` - NIS siswa
- `{class_name}` - Nama kelas
- `{checkin_time}` - Waktu check-in
- `{date}` - Tanggal presensi

**Check-out:**
- Semua variabel check-in +
- `{checkout_time}` - Waktu check-out

## 📊 Struktur Database

### Tabel Utama

- **users** - Data pengguna (admin, guru, siswa)
- **jurusans** - Data jurusan
- **kelas** - Data kelas
- **presensi_sessions** - Sesi presensi dengan QR Code
- **qr_codes** - QR Code untuk check-in/out
- **presensis** - Data presensi siswa
- **fonnte_devices** - Device WhatsApp
- **settings** - Pengaturan sistem
- **school_settings** - Data sekolah
- **academic_years** - Tahun ajaran

## 🔧 Fitur Teknis

### QR Code System
- QR Code unik untuk setiap sesi (check-in & check-out terpisah)
- Validasi waktu dan tanggal
- Auto-expire berdasarkan jadwal
- Soft delete untuk history

### Geolokasi
- Validasi radius menggunakan Haversine formula
- Akurasi GPS tracking
- Support koordinat check-in dan check-out berbeda
- Radius dapat dikustomisasi per sesi (50-1000m)

### WhatsApp Integration
- Multi-device dengan load balancing
- Priority-based rotation
- Automatic failover
- Rate limiting dan monitoring
- Customizable message templates

### Export/Import
- Export data siswa dan presensi ke Excel
- Import siswa dari Excel (batch)
- Template Excel disediakan
- Validasi data saat import

## 🔐 Role & Permission

### Admin (role: 1)
- Full access ke semua fitur
- Manajemen users, kelas, jurusan
- Generate QR Code
- Monitoring dan laporan
- Pengaturan sistem dan WhatsApp

### Guru (role: 0)
- Generate QR Code untuk kelas
- Input manual presensi
- Export/import data siswa
- Monitoring presensi

### Siswa (role: 2)
- Scan QR Code presensi
- Lihat history presensi

## 📝 Cara Penggunaan

### Membuat Sesi Presensi

1. Login sebagai Admin/Guru
2. Menu **QR Code > Buat QR Code**
3. Pilih kelas dan tanggal
4. Tentukan jadwal:
   - Jam check-in (mulai - selesai)
   - Jam check-out (mulai - selesai)
5. Set lokasi dan radius untuk check-in
6. (Opsional) Set lokasi berbeda untuk check-out
7. Generate QR Code
8. Download atau tampilkan QR Code

### Siswa Melakukan Presensi

1. Login sebagai Siswa
2. Menu **Presensi**
3. Klik "Scan QR Code"
4. Izinkan akses kamera dan lokasi
5. Scan QR Code yang ditampilkan
6. Sistem akan validasi:
   - Waktu presensi
   - Lokasi (radius)
   - Status presensi
7. Presensi berhasil, notifikasi WhatsApp terkirim otomatis

### Input Manual Presensi

1. Login sebagai Admin/Guru
2. Menu **Presensi > Pilih Kelas**
3. Pilih tanggal
4. Klik "Tambah Manual"
5. Pilih siswa dan status (hadir/izin/sakit/alpha)
6. Tambahkan keterangan jika perlu
7. Submit

## 🐛 Troubleshooting

### QR Code tidak terbaca
- Pastikan kamera berfungsi
- Periksa pencahayaan
- Gunakan browser yang support (Chrome/Safari)

### Notifikasi WhatsApp tidak terkirim
- Cek status device di menu WhatsApp
- Pastikan API key valid
- Periksa nomor orang tua siswa
- Test koneksi device

### Error saat import Excel
- Gunakan template yang disediakan
- Pastikan format data sesuai
- Cek NIS tidak duplikat
- Pastikan nama kelas sudah ada

### Lokasi tidak valid
- Aktifkan GPS di device
- Izinkan akses lokasi di browser
- Periksa radius yang ditentukan
- Pastikan berada di area yang ditentukan