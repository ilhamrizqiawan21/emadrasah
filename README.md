# e-Madrasah Native PHP

Sistem Informasi Manajemen Madrasah berbasis PHP Native.

## 🚀 Fitur Utama
- **Manajemen User:** Pengaturan akun admin, TU, dan operator dengan proteksi CSRF.
- **Buku Induk & Siswa:** Pencatatan detail siswa, wali, hingga riwayat perkembangan siswa dengan fitur Export PDF.
- **Persuratan:** Pemisahan Surat Masuk & Keluar, serta sistem Template Surat otomatis.
- **Sarana & Prasarana:** Manajemen aset, peminjaman barang, dan log servis dengan sistem kontrol stok ketersediaan.
- **Jadwal Pelajaran:** Input jadwal berbasis Grid (Excel-like) dengan fitur Cetak PDF.
- **Arsip Akademik:** Penyimpanan dokumen digital per kelas dan semester.
- **Task Management:** Manajemen tugas staf dengan pelacakan status dan lampiran.

## 🛡️ Keamanan & Standar Kode
- **CSRF Protection:** Setiap request POST dilindungi token unik.
- **Input Sanitization:** Proteksi terhadap XSS dan SQL Injection menggunakan PDO Prepared Statements.
- **Clean UI:** Menggunakan Bootstrap 5 dengan kustomisasi CSS yang modern dan responsif.
- **Centralized Helpers:** Fungsi umum (tanggal, URL, validasi) dipusatkan di `includes/functions.php`.

## 🛠️ Persyaratan Sistem
- PHP 7.4 atau lebih baru.
- MySQL / MariaDB.
- Laragon, XAMPP, atau server PHP lainnya.

## 📦 Instruksi Instalasi

1. **Clone atau Copy Project:**
   Pastikan project berada di direktori web server Anda (contoh: `C:/laragon/www/emadrasah`).

2. **Setup Database:**
   - Buat database baru bernama `madrasah_db`.
   - Import file SQL yang tersedia (disarankan menggunakan schema terbaru dari `emadrasah2/madrasah_db (4).sql` jika ingin data awal, namun struktur native sudah disesuaikan secara mandiri selama proses rebuild).
   - *Catatan:* Jika Anda menggunakan database kosong, pastikan tabel `users`, `siswa`, `surat_masuk`, `surat_keluar`, `sarana_prasarana`, `peminjaman_sarana`, `pemeliharaan_sarana`, `template_surat`, `kelas`, `mapels`, dan `gurus` sudah tersedia.

3. **Konfigurasi Koneksi:**
   Buka `config/database.php` dan sesuaikan pengaturan database Anda:
   ```php
   $host = '127.0.0.1';
   $db   = 'madrasah_db';
   $user = 'root';
   $pass = 'PASSWORD_ANDA';
   ```

4. **Akses Aplikasi:**
   Buka browser dan akses `http://localhost/emadrasah`.

## 🔑 Akun Default
Silakan cek tabel `users` untuk email dan password yang tersedia. Password dienkripsi menggunakan `password_hash()`.

## 📂 Struktur Direktori
- `assets/`: File CSS, JS, dan Gambar.
- `config/`: Konfigurasi database.
- `includes/`: Header, footer, sidebar, dan fungsi-fungsi global.
- `modules/`: Modul-modul fitur aplikasi.
- `uploads/`: Folder penyimpanan file yang diupload (scan surat, foto siswa, dll).

---
*Update Terakhir: 18 Juni 2026*
