# Sistem Reservasi Ruangan (Room Booking System)

Sistem Reservasi Ruangan berbasis web modern yang dibangun menggunakan **Laravel + Inertia.js + Vue 3**, dirancang untuk mempermudah pengelolaan, peminjaman, persetujuan (*approval*), hingga konfirmasi kehadiran (*check-in*) fasilitas ruangan rapat dan aula secara efisien, real-time, dan terintegrasi.

---

## 🚀 Tech Stack

- **Backend Framework**: [Laravel 10 / 12](https://laravel.com) (PHP 8.1 / 8.2+)
- **Frontend Stack**: [Inertia.js](https://inertiajs.com) + [Vue 3](https://vuejs.org) (Composition API / `<script setup>`)
- **Styling**: [Tailwind CSS](https://tailwindcss.com) + Headless UI
- **Database & ORM**: MySQL 8.0 / MariaDB (Eloquent ORM)
- **Role & Permission**: [Spatie Laravel Permission](https://spatie.be/docs/laravel-permission)
- **Kalender Interaktif**: [FullCalendar Vue 3](https://fullcalendar.io)
- **Real-Time Broadcasting**: [Laravel Reverb](https://reverb.laravel.com) (WebSocket)
- **Asynchronous Queue**: Laravel Database Queue Worker (`ShouldQueue`)
- **QR Code Generator**: [SimpleSoftwareIO Simple-QRCode](https://www.simplesoftware.io/docs/simple-qrcode)
- **Export Laporan**: [Maatwebsite Excel](https://laravel-excel.com) & [Barryvdh Laravel DomPDF](https://github.com/barryvdh/laravel-dompdf)
- **Automated Testing**: [Pest PHP](https://pestphp.com)

---

## 📸 Antarmuka Aplikasi (Screenshots)

> *Catatan: Silakan tempatkan tangkapan layar antarmuka aplikasi pada direktori `docs/screenshots/` dan tautkan di bawah ini.*

### 1. Kalender Jadwal & Ketersediaan Ruangan
<!-- TAMBAHKAN SCREENSHOT DI SINI: Tampilan kalender FullCalendar dengan badge warna status reservasi -->
`![Kalender Reservasi](docs/screenshots/calendar-view.png)`

### 2. Formulir Booking & Deteksi Bentrok Jadwal
<!-- TAMBAHKAN SCREENSHOT DI SINI: Form input pemesanan ruangan beserta feedback validasi real-time -->
`![Form Pemesanan](docs/screenshots/booking-form.png)`

### 3. Halaman Persetujuan (Approval) Manajerial
<!-- TAMBAHKAN SCREENSHOT DI SINI: Tab filter persetujuan reservasi dan modal penolakan bersyarat -->
`![Manajemen Approval](docs/screenshots/approvals-index.png)`

### 4. QR Code & Public Check-In Kehadiran
<!-- TAMBAHKAN SCREENSHOT DI SINI: Modal penampil QR code dan tampilan mobile halaman scan check-in -->
`![QR Code Check-In](docs/screenshots/qr-checkin.png)`

### 5. Laporan Penggunaan Ruangan & Export
<!-- TAMBAHKAN SCREENSHOT DI SINI: Dashboard analitik penggunaan ruangan beserta tombol export Excel dan PDF -->
`![Laporan & Export](docs/screenshots/reports-analytics.png)`

---

## ✨ Fitur-Fitur Utama

1. **Manajemen Master Data Ruangan & Fasilitas**:
   - Pengelolaan data ruangan (kapasitas, lokasi, deskripsi, foto, status operasional).
   - Relasi pivot many-to-many fasilitas (*proyektor, sound system, whiteboard, wifi*).
2. **Booking Cerdas dengan Conflict Detection**:
   - Validasi jadwal bentrok (*smart overlap checking*) memastikan tidak ada reservasi ganda pada rentang waktu yang sama untuk ruangan yang sama.
   - Pengecekan otomatis hanya memperhitungkan reservasi aktif (*pending* & *approved*).
3. **Kalender Visual Real-Time**:
   - Kalender dinamis (*FullCalendar*) dengan pewarnaan berdasarkan status agenda.
   - Perubahan jadwal langsung tersinkronisasi ke seluruh browser pengguna tanpa refresh berkat **Laravel Reverb**.
4. **Alur Persetujuan Bertingkat (Approval Workflow)**:
   - Approval instan oleh Admin/Manager.
   - Catatan alasan wajib (*mandatory note*) jika reservasi ditolak (*rejected*).
5. **Notifikasi Otomatis (Email & Database)**:
   - Notifikasi ke approver saat permohonan baru dibuat.
   - Notifikasi email & in-app ke pemohon saat reservasi disetujui atau ditolak.
   - Seluruh notifikasi dikirim secara asinkron via queue worker (*ShouldQueue*).
6. **Check-In Mandiri Berbasis QR Code**:
   - Pembuatan QR token unik otomatis sesaat setelah reservasi disetujui.
   - Endpoint publik mobile-friendly untuk scan QR code di depan ruangan.
   - Validasi batas waktu kehadiran (check-in dibuka mulai 15 menit sebelum acara hingga selesai).
7. **Laporan & Ekspor Data (Excel & PDF)**:
   - Analitik penggunaan ruangan (total peminjaman bulanan & 3 ruangan terfavorit).
   - Filter rentang tanggal dan ruangan tertentu.
   - Unduh langsung dokumen laporan resmi dalam format spreadsheet Excel (`.xlsx`) dan dokumen PDF siap cetak.

---

## 👥 Struktur Role & Permission

Sistem mengimplementasikan Role-Based Access Control (RBAC) menggunakan Spatie Permission:

| Permission | Admin | Manager | Staff |
| :--- | :---: | :---: | :---: |
| `manage-rooms` | ✅ | ❌ | ❌ |
| `manage-facilities` | ✅ | ❌ | ❌ |
| `approve-reservation` | ✅ | ✅ | ❌ |
| `create-reservation` | ✅ | ✅ | ✅ |
| `view-all-reservations` | ✅ | ✅ | ❌ |
| `manage-users` | ✅ | ❌ | ❌ |
| `view-reports` | ✅ | ✅ | ❌ |

### Akun Bawaan untuk Pengujian (Seeded Users):
- **Admin**: `admin@example.com` / Password: `password`
- **Manager**: `manager@example.com` / Password: `password`
- **Staff**: `staff@example.com` / Password: `password`

---

## 🛠️ Panduan Instalasi Lokal (Step-by-Step)

### Prasyarat:
- PHP >= 8.1 (Direkomendasikan PHP 8.2) dengan ekstensi: `pdo_mysql`, `mbstring`, `gd`, `zip`, `xml`
- Composer 2.x
- Node.js >= 18.x & NPM
- MySQL 8.0 / MariaDB

### Langkah Instalasi:

1. **Clone Repository & Masuk ke Direktori**:
   ```bash
   git clone https://github.com/username/reservasi-ruangan.git
   cd reservasi-ruangan
   ```

2. **Instal Dependensi PHP (Composer)**:
   ```bash
   composer install
   ```

3. **Instal Dependensi Frontend (NPM)**:
   ```bash
   npm install
   ```

4. **Siapkan File Konfigurasi Environment**:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Sesuaikan Konfigurasi Database di `.env`**:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=reservasi_ruangan
   DB_USERNAME=root
   DB_PASSWORD=
   ```

6. **Jalankan Migrasi & Database Seeder**:
   ```bash
   php artisan migrate --seed
   ```
   *(Perintah ini akan membuat struktur tabel lengkap beserta data role, permission, ruangan contoh, fasilitas, dan 3 user pengujian).*

7. **Kompilasi Asset Frontend**:
   - Untuk mode pengembangan (*Hot Module Replacement*):
     ```bash
     npm run dev
     ```
   - Atau untuk build production:
     ```bash
     npm run build
     ```

8. **Menjalankan Server & Layanan Pendukung**:
   Buka terminal terpisah untuk masing-masing proses:

   - **Terminal 1: Web Server Laravel**:
     ```bash
     php artisan serve
     ```
     *(Akses aplikasi di browser via `http://localhost:8000`)*

   - **Terminal 2: Laravel Reverb (WebSocket Real-Time)**:
     ```bash
     php artisan reverb:start
     ```

   - **Terminal 3: Queue Worker (Pengiriman Email & Notifikasi Latar Belakang)**:
     ```bash
     php artisan queue:work
     ```

---

## 🧪 Menjalankan Automated Tests (Pest)

Seluruh pengujian unit dan fitur dapat dijalankan secara instan:

```bash
# Menjalankan seluruh test suite
php artisan test

# Atau menggunakan binary Pest secara langsung
./vendor/bin/pest

# Menjalankan pengujian spesifik
php artisan test --filter ReservationConflictTest
php artisan test --filter ApprovalFlowTest
php artisan test --filter RoomManagementTest
```

---

## 🐳 Deployment Menggunakan Docker

Telah disediakan file `Dockerfile` dan `docker-compose.yml` untuk deployment instan multi-container:

1. Salin `.env.example` menjadi `.env` dan pastikan konfigurasi `APP_KEY` terisi:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
2. Jalankan docker-compose:
   ```bash
   docker-compose up -d --build
   ```
3. Jalankan migrasi dan seeder di dalam container app:
   ```bash
   docker-compose exec app php artisan migrate --seed --force
   ```
4. Aplikasi siap diakses melalui port `80` (Web Nginx), port `8080` (Reverb WebSocket), dan worker antrean otomatis aktif di latar belakang.

---

## 📄 Lisensi

Sistem ini didistribusikan di bawah lisensi terbuka [MIT License](LICENSE).
