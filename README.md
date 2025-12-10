# <div align="center">🔥 **Sipena — Sistem Absensi Siswa Berbasis QR Code** 🔥</div>

<div align="center">
  <img src="https://img.shields.io/badge/Laravel-10-red?style=for-the-badge">
  <img src="https://img.shields.io/badge/PHP-8.1-blue?style=for-the-badge">
  <img src="https://img.shields.io/badge/MySQL-DB-orange?style=for-the-badge">
  <img src="https://img.shields.io/badge/License-MIT-success?style=for-the-badge">
  <img src="https://img.shields.io/github/stars/aryacahil/sipena?style=for-the-badge">
</div>

<br>

<div align="center">
  <img src="https://dummyimage.com/900x240/181818/ffffff&text=Sipena+QR+Attendance+System" width="100%">
</div>

<br>

Aplikasi **Sipena** adalah sistem absensi siswa berbasis **QR Code**, lengkap dengan **validasi geolokasi**, **dashboard real-time**, serta **notifikasi otomatis ke WhatsApp** menggunakan API Fonnte.
Dibangun menggunakan **Laravel 10**, dirancang agar **ringan, modern, dan mudah digunakan** oleh sekolah.

---

# 📌 **Contributor**

<div align="center">

### 👤 **Created & Maintained by:**

# ⭐ *aryacahil*

</div>

---

# 🎴 **Fitur Utama**

## 🧩 Admin & Guru

<div align="center">
  <img src="https://img.shields.io/badge/ROLE-ADMIN-green?style=flat-square">
</div>

### Manajemen Data Master

* Data siswa, guru, admin
* Jurusan & kelas
* Import/export Excel

### QR Code

* Generate QR check-in/check-out
* Pengaturan radius lokasi (50–1000m)
* Download PNG

### Monitoring

* Dashboard real-time
* Grafik harian/bulanan
* Input manual izin/sakit/alpha
* Export Laporan

### Notifikasi WhatsApp

* Kirim otomatis saat check-in/out
* Multi-device
* Template dinamis

---

## 👨‍🎓 Siswa

* Scan QR
* Validasi lokasi otomatis
* Riwayat presensi

---

# 🖼️ **Screenshots**

### 🔐 Login

<div align="center">
  <img src="screenshots/login.png" width="430">
  <p><i>Login untuk admin, guru, dan siswa</i></p>
</div>

### 📊 Dashboard Admin

<div align="center">
  <img src="screenshots/admin-dashboard.png" width="430">
</div>

### 👥 Manajemen User

<div align="center">
  <img src="screenshots/admin-users.png" width="430">
  <img src="screenshots/admin-users-add.png" width="430">
</div>

### 🏫 Jurusan / Kelas

<div align="center">
  <img src="screenshots/admin-jurusan.png" width="430">
  <img src="screenshots/admin-kelas.png" width="430">
</div>

### 🧾 QR Code

<div align="center">
  <img src="screenshots/admin-qrcode-list.png" width="430">
  <img src="screenshots/admin-qrcode-display.png" width="430">
</div>

### 👨‍🏫 Dashboard Guru

<div align="center">
  <img src="screenshots/guru-dashboard.png" width="430">
</div>

### 👨‍🎓 Dashboard Siswa

<div align="center">
  <img src="screenshots/siswa-dashboard.png" width="430">
</div>

---

# 🛠️ Teknologi

| Layer    | Teknologi                                |
| -------- | ---------------------------------------- |
| Backend  | Laravel 10                               |
| DB       | MySQL                                    |
| Frontend | Bootstrap 5, jQuery                      |
| QR Code  | SimpleSoftwareIO / chillerlan/php-qrcode |
| Excel    | Maatwebsite/Excel                        |
| WhatsApp | Fonnte API                               |
| UI       | SweetAlert2, Chart.js                    |

---

# 📦 Instalasi

```bash
git clone <repository>
cd <folder>
composer install
cp .env.example .env
php artisan key:generate
```

Isi database di `.env`.

```bash
php artisan migrate --seed
php artisan storage:link
php artisan serve
```

Akses: **[http://localhost:8000](http://localhost:8000)**

---

# 👤 Default Login

| Role  | Email/NIS                                 | Password |
| ----- | ----------------------------------------- | -------- |
| Admin | [admin@admin.com](mailto:admin@admin.com) | password |
| Guru  | [guru@guru.com](mailto:guru@guru.com)     | password |
| Siswa | 233307037                                 | password |

---

# 📱 WhatsApp konfigurasi (Fonnte)

* Tambah API Key
* Tambah device
* Test koneksi

**Variabel:**

```
{student_name}
{nis}
{class_name}
{checkin_time}
{checkout_time}
{date}
```

---

# 🧱 Struktur Database

* users
* jurusans
* kelas
* presensis
* qr_codes
* fonnte_devices
* settings
* academic_years
* school_settings

---

# 🧪 Fitur Teknis

* QR unik
* Validasi lokasi Haversine
* Auto-expire session
* Soft delete
* Multi device WA

---
