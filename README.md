<div align="center">🔥 **Sipena — QR Code Based Student Attendance System** 🔥</div>

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

**Sipena** is a **QR Code–based student attendance system** designed for schools, featuring **geolocation validation**, **real-time monitoring dashboard**, and **automatic WhatsApp notifications** via the **Fonnte API**.  
Built with **Laravel 10**, Sipena focuses on **simplicity, performance, and reliability** for daily school operations.

---

## 📌 Contributor

<div align="center">

### Created & Maintained by  
# ⭐ **aryacahil**

</div>

---

## 🎴 Key Features

### 🧩 Admin & Teacher

#### 📚 Master Data Management
- Manage **Admin, Teacher, and Student** accounts
- Manage **Departments (Jurusan) and Classes**
- Automatic student grouping by class
- Import and export student data using **Excel**
- Basic data validation (unique NIS, valid class assignment)

---

#### 🔳 QR Code Attendance
- Generate QR Codes for **Check-In and Check-Out**
- Attendance scheduling (start and end time)
- Geolocation validation with configurable radius (**50–1000 meters**)
- Optional separate locations for check-in and check-out
- Unique QR Code per attendance session
- Automatic QR Code expiration based on schedule
- Download QR Code as **PNG**

---

#### 📊 Attendance Monitoring
- Real-time attendance dashboard
- Attendance statistics and charts:
  - Daily
  - Weekly
  - Monthly
  - Yearly
- Filter attendance data by **class and date**
- Manual attendance input:
  - Present
  - Excused
  - Sick
  - Absent
- Export attendance reports to **Excel**

---

#### 📱 WhatsApp Notifications
- Automatic notifications to parents during:
  - Check-in
  - Check-out
- Integration with **Fonnte WhatsApp API**
- Multi-device support with priority and rotation
- Customizable message templates
- Delivery status monitoring

---

#### ⚙️ System Settings
- School profile configuration (name, logo, address)
- Academic year management
- WhatsApp API configuration
- Attendance system preferences

---

### 👨‍🎓 Student
- Scan QR Code to record attendance
- Automatic geolocation validation
- Separate check-in and check-out records
- Personal attendance history
- Attendance status overview

---

## 🖼️ Application Screenshots

### 🔐 Login Page
![Login Page](screenshots/login.png)
*Login page for Admin, Teacher, and Student roles*

---

## 👨‍💼 Admin Dashboard

### Dashboard Overview
![Admin Dashboard](screenshots/admin-dashboard.png)
*Attendance summary and statistics*

---

### User Management
![User Management](screenshots/admin-users.png)
*Manage Admin, Teacher, and Student accounts*

![Add User](screenshots/admin-users-add.png)
*Add new user form*

---

### Department Management
![Department List](screenshots/admin-jurusan.png)
*List of available departments*

![Add Department](screenshots/admin-jurusan-add.png)
*Create a new department*

---

### Class Management
![Class List](screenshots/admin-kelas.png)
*Class list with student count*

![Add Class](screenshots/admin-kelas-add.png)
*Create a new class*

![Class Detail](screenshots/admin-kelas-detail.png)
*Class details and student list*

![Add Student to Class](screenshots/admin-kelas-add-siswa.png)
*Assign students to a class*

---

### QR Code Management
![QR Code List](screenshots/admin-qrcode-list.png)
*Attendance session list*

![Create QR Code](screenshots/admin-qrcode-create.png)
*QR Code creation with location settings*

![QR Code Display](screenshots/admin-qrcode-display.png)
*QR Code for check-in and check-out*

---

### Attendance Monitoring
![Attendance Dashboard](screenshots/admin-presensi-dashboard.png)
*Attendance monitoring by class*

![Attendance Class Detail](screenshots/admin-presensi-kelas.png)
*Student attendance details*

---

### Import & Export
![Export Import](screenshots/admin-export-import.png)
*Import and export attendance data*

---

### WhatsApp Configuration
![WhatsApp Settings](screenshots/admin-whatsapp-settings.png)
*WhatsApp API and device configuration*

---

### School Settings
![School Settings](screenshots/admin-settings-school.png)
*School profile and system settings*

---

## 👨‍🏫 Teacher Dashboard
![Teacher Dashboard](screenshots/guru-dashboard.png)
*Teacher dashboard overview*

![Teacher QR Code](screenshots/guru-qrcode.png)
*Manage attendance QR Codes*

![Teacher Attendance](screenshots/guru-presensi.png)
*Monitor student attendance*

---

## 👨‍🎓 Student Dashboard
![Student Dashboard](screenshots/siswa-dashboard.png)
*Student attendance overview*

![Student Scan QR](screenshots/siswa-scan-qr.png)
*Scan QR Code to record attendance*

---

## 🛠️ Tech Stack

| Layer     | Technology                               |
|-----------|-------------------------------------------|
| Backend   | Laravel 10                                |
| Database  | MySQL                                     |
| Frontend  | Bootstrap 5, jQuery                       |
| QR Code   | SimpleSoftwareIO / chillerlan/php-qrcode  |
| Excel     | Maatwebsite/Excel                         |
| WhatsApp  | Fonnte API                                |
| UI        | SweetAlert2, Chart.js                     |

---

## 📦 Installation

### Requirements
- PHP >= 8.1
- Composer
- MySQL >= 5.7
- Node.js & NPM (optional)

### Setup

```bash
git clone <repository-url>
cd <project-folder>
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
php artisan serve
````

Access the application at: **[http://localhost:8000](http://localhost:8000)**

---

## 👤 Default Credentials

| Role    | Email / NIS                               | Password |
| ------- | ----------------------------------------- | -------- |
| Admin   | [admin@admin.com](mailto:admin@admin.com) | password |
| Teacher | [guru@guru.com](mailto:guru@guru.com)     | password |
| Student | 233307037                                 | password |

---

## 📱 WhatsApp Message Variables

```
{student_name}
{nis}
{class_name}
{checkin_time}
{checkout_time}
{date}
```

---

## 🧪 Technical Highlights

* Unique QR Code per attendance session
* Time-based QR Code expiration
* Geolocation validation using Haversine formula
* Multi-device WhatsApp messaging
* Soft delete for historical data
* Excel import/export support

---