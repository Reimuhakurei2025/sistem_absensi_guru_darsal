# Sistem Absensi Guru Berbasis Web Menggunakan Barcode
**SMP Terpadu Darussalam — Kabupaten Tangerang, Banten**

Sistem informasi absensi guru berbasis web yang menggunakan teknologi barcode (QR Code) untuk mempercepat dan meningkatkan akurasi pencatatan kehadiran tenaga pendidik.

---

## 🛠️ Tech Stack

| Komponen | Versi |
|---|---|
| Framework | Laravel 11 |
| Templating | Blade |
| Frontend | Tailwind CSS (via Vite) |
| Database | MySQL |
| Barcode (generate) | `simplesoftwareio/simple-qrcode` |
| Barcode (scan) | `html5-qrcode` (kamera browser) |
| UI Helper | Alpine.js |

---

## 👥 Role Pengguna

| Role | Hak Akses |
|---|---|
| **Kepala Sekolah** | Super admin — kelola guru, reset password, lihat laporan & ranking |
| **Admin (TU)** | Lihat daftar guru, lihat & cetak QR code tiap guru |
| **Guru** | Login, scan QR untuk absensi, lihat riwayat absensi |

---

## ✨ Fitur Utama

### Untuk Kepala Sekolah:
- ✅ Dashboard dengan statistik kehadiran real-time
- ✅ CRUD guru (tambah, edit, nonaktifkan, aktifkan)
- ✅ Reset password user mana saja (Guru/Admin/Kepsek)
- ✅ Laporan bulanan dengan opsi cetak A4 landscape
- ✅ Ranking kehadiran dengan podium 3 besar
- ✅ Konfirmasi password untuk semua aksi sensitif

### Untuk Admin TU:
- ✅ Lihat daftar guru aktif
- ✅ Generate & cetak QR code per guru
- ✅ Cetak massal QR semua guru (grid 2 kolom)

### Untuk Guru:
- ✅ Dashboard status absensi hari ini
- ✅ Scan QR Code menggunakan kamera (mobile/laptop)
- ✅ Riwayat absensi pribadi dengan filter bulan/tahun

### Validasi Keamanan Sistem Scan:
- ✅ Token QR harus ada di database
- ✅ Token harus milik guru yang sedang login (bukan QR teman)
- ✅ Cegah duplikasi absen di hari yang sama
- ✅ Auto-logout jika akun dinonaktifkan saat session aktif

---

## 🚀 Setup Instruction

### 1. Buat proyek Laravel 11 baru
```bash
composer create-project laravel/laravel:^11.0 absensi-darussalam
cd absensi-darussalam
```

### 2. Replace file dari ZIP ini
Extract ZIP, lalu **copy semua file/folder** ke proyek Laravel Anda (overwrite yang sudah ada).

### 3. Install dependencies
```bash
composer require simplesoftwareio/simple-qrcode
npm install
```

### 4. Setup environment
```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` sesuai database lokal Anda:
```env
DB_DATABASE=db_absensi_darussalam
DB_USERNAME=root
DB_PASSWORD=
```

### 5. Buat database
```bash
mysql -u root -e "CREATE DATABASE db_absensi_darussalam"
```

Atau via phpMyAdmin: buat database baru bernama `db_absensi_darussalam`.

### 6. Migrate + seed
```bash
php artisan migrate --seed
```

### 7. Setup storage symlink (untuk foto guru)
```bash
php artisan storage:link
```

### 8. Jalankan server
```bash
# Terminal 1
php artisan serve

# Terminal 2
npm run dev
```

Akses: **http://localhost:8000**

---

## 🔑 Akun Default (setelah seed)

| Role | Username | Password |
|---|---|---|
| Kepala Sekolah | `kepsek` | `kepsek123` |
| Admin (TU) | `admin` | `admin123` |
| Guru sample 1 | `ahmad.fauzi` | `guru123` |
| Guru sample 2 | `siti.nurhaliza` | `guru123` |
| Guru sample 3 | `budi.santoso` | `guru123` |

⚠️ **Wajib ganti password setelah deploy production.**

---

## 🔧 Command CLI (Khusus Administrator Sistem)

Sistem menyediakan beberapa Artisan command untuk task administratif yang **memerlukan akses langsung ke server**. Hanya orang dengan akses SSH/terminal server yang dapat menjalankannya — sesuai prinsip *Least Privilege*.

### Reset Password Kepala Sekolah

Jika Kepala Sekolah lupa password (dan tidak ada Kepsek lain yang bisa reset), administrator sistem dapat mereset via terminal:

```bash
# Mode interaktif (akan tampil daftar Kepsek + tanya username)
php artisan kepsek:reset-password

# Langsung dengan username
php artisan kepsek:reset-password kepsek
```

**Output contoh:**
```
╔════════════════════════════════════════════════════╗
║   RESET PASSWORD KEPALA SEKOLAH                    ║
║   SMP Terpadu Darussalam — Sistem Absensi Guru     ║
╚════════════════════════════════════════════════════╝

Daftar Kepala Sekolah saat ini:
+----+----------+-------------------+----------+
| ID | Username | Nama              | Status   |
+----+----------+-------------------+----------+
| 1  | kepsek   | Syamsudin, S.E    | ✓ Aktif  |
+----+----------+-------------------+----------+

Masukkan username Kepala Sekolah yang akan di-reset: kepsek
...
✅ Password berhasil di-reset!
```

Aksi ini akan **otomatis dicatat di log** (`storage/logs/laravel.log`) dengan informasi:
- ID & username Kepsek yang di-reset
- Waktu reset
- User Linux yang menjalankan command (audit trail)

### Lihat Daftar Kepala Sekolah

```bash
php artisan kepsek:list
```

---

## 🧪 Cara Test Alur Absensi

1. **Login sebagai admin** (`admin` / `admin123`)
2. Buka menu **Daftar Guru** → klik **Lihat Barcode** salah satu guru
3. Buka tab/window baru, screenshot atau cetak QR code-nya
4. **Logout**, lalu **login sebagai guru** yang sama (mis. `ahmad.fauzi` / `guru123`)
5. Buka menu **Scan Absen**
6. Izinkan akses kamera saat browser meminta
7. Arahkan kamera ke QR Code → sistem akan otomatis mencatat absensi

**Test validasi keamanan:**
- Coba scan QR guru lain → akan ditolak: "Barcode ini bukan milik Anda"
- Coba scan ulang setelah berhasil → akan ditolak: "Anda sudah absen hari ini"

---

## 📂 Struktur Database

```
tb_kepsek      → Data Kepala Sekolah
tb_admin       → Data staff Tata Usaha
tb_guru        → Data guru + barcode_token unik (kolom: barcode_token)
tb_absensi     → Record absensi harian (unique: id_guru + tanggal)
```

Soft delete pakai kolom `is_active` (true/false), tidak hapus permanen.

---

## 🎨 Color Theme

- Primary: `#2E7D32` (hijau Darussalam)
- Secondary: `#66BB6A`
- Background: putih dominan dengan accent hijau muda

---

## 📌 Catatan Pengembangan

Versi awal ini fokus pada fitur **core**. Modul **pengembangan lanjutan** (sesuai laporan KP) yang belum dibangun:
- ⏳ Modul izin (`tb_izin`) — sementara guru hanya bisa absen via scan, status izin/sakit/alpa diinput manual oleh admin
- ⏳ Modul jadwal mengajar (`tb_jadwal`)
- ⏳ Modul mata pelajaran terpisah (`tb_matpel`) — saat ini disimpan langsung di `tb_guru`
- ⏳ Pengaturan sistem (`tb_web`)
- ⏳ Export laporan ke PDF/Excel (sudah disiapkan composer-nya, tinggal implementasi controller)

---

## 🔧 Troubleshooting

**Kamera tidak bisa diakses?**
- Pastikan akses via `https://` atau `http://localhost` (kamera tidak akan jalan di IP lain via HTTP)
- Cek permission browser: Settings → Site Settings → Camera

**QR code tidak terdeteksi?**
- Pastikan pencahayaan cukup
- Jangan terlalu dekat (min 15 cm)
- Pastikan QR ter-print/tertampil dengan jelas

**Migration error?**
- Pastikan MySQL versi 5.7+
- Pastikan `DB_DATABASE` di `.env` sudah dibuat di MySQL

---

## 📝 Tim Pengembang

Kerja Praktek — Universitas Pamulang, Teknik Informatika

1. **Refa Yudistira** (Team Leader)
2. **Zakky Ananda Astqalani Tindoy**
3. **Ahmad Rivaldi**

Dosen Pembimbing & Kepala Sekolah SMP Terpadu Darussalam: **Bpk. Syamsudin, S.E**
