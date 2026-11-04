# Changelog

Semua perubahan penting pada proyek ini akan didokumentasikan dalam file ini.

Format pencatatan mengacu pada [Keep a Changelog](https://keepachangelog.com/id/1.0.0/) dan mengikuti [Semantic Versioning](https://semver.org/lang/id/).

---

## [1.0.0] - 2026-11-04

Rilis penuh e-Budget: perencanaan anggaran bulanan, pengeluaran rutin, tabungan beserta master tempat penyimpanan, dashboard analitik interaktif, dan dukungan deployment Docker.

### ✨ Fitur Utama (Features)

- **Manajemen Tabungan (Savings)**:
  - Pengelolaan tabungan (nama, tempat penyimpanan, target nominal opsional, tenggat opsional, dan catatan).
  - Kolom kalkulasi otomatis **Kekurangan Target** (`Target - Saldo` atau status `Tercapai`).
  - Kartu ringkasan saldo otomatis per tempat penyimpanan beserta total saldo keseluruhan.
  - Halaman detail 3 kolom (Uang Masuk, Uang Keluar, Ringkasan + Form Penyesuaian Saldo manual tambah/kurang).
  - Integrasi otomatis dengan Budget Plan: kategori pengeluaran bertanda **Termasuk Tabungan** otomatis membuat transaksi tabungan saat status Budget Plan diubah dari `active` menjadi `closed`.

- **Master Tempat Penyimpanan (Saving Providers CRUD)**:
  - Pengelolaan tempat penyimpanan tabungan (Bank Mandiri, BCA, Bibit, dll.) dengan nomor rekening/akun, atas nama, dan deskripsi bersifat opsional.

- **Perencanaan Anggaran (Budget Plans)**:
  - Pembuatan rencana anggaran bulanan (Draft, Aktif, Selesai) dengan item pemasukan, lembur, dan alokasi pengeluaran bulan berikutnya.
  - Validasi pencegahan duplikasi periode bulan dan tahun yang sama.
  - Pengisian otomatis dari kategori default dan pengeluaran rutin yang aktif pada periode terkait.
  - Antarmuka Create, Edit, dan Detail 2 kolom (kolom kiri Periode & Ringkasan *sticky*, kolom kanan tab **Pemasukan** & **Pengeluaran** dengan tabel *scrollable*).
  - Modal tambah baris dengan pencarian kategori dan input nominal (kategori pemasukan dapat dipilih berulang, kategori pengeluaran unik per baris).

- **Pengeluaran Rutin (Recurring Expenses)**:
  - Pencatatan pengeluaran berulang/cicilan dengan durasi tenor, tanggal mulai, dan toggle aktif/nonaktif.

- **Manajemen Kategori (Categories CRUD)**:
  - Pengelolaan kategori pemasukan dan pengeluaran dengan nominal default, nomor urut, flag default Budget Plan, serta pengaturan integrasi ke Tabungan.

- **Dashboard Analitik**:
  - Ringkasan anggaran aktif, rasio pengeluaran, rincian pemasukan/pengeluaran, dan grafik tren bulanan dalam satu tahun.

### 🐛 Perbaikan & Infrastruktur

- Konfigurasi Dockerfile dan penanganan URL/asset di belakang reverse proxy.
- Penghapusan halaman welcome publik; rute utama langsung mengarah ke login/dashboard.

---

## [0.1.0] - 2026-10-03

Rilis awal sistem manajemen pengguna, peran, hak akses, dan log aktivitas dengan antarmuka DataTable modern serta arsitektur backend Service-Repository.

### ✨ Fitur Utama (Features)

- **Manajemen Pengguna (Users CRUD)**:
  - Pengelolaan data pengguna (Nama, Username, Email, Password, dan Peran).
  - Fitur pencarian *real-time*, paginasi bernomor, dan pengaturan jumlah baris data per halaman.
  - Avatar inisial dinamis dan modal konfirmasi hapus pengguna.

- **Manajemen Peran & Hak Akses (Roles & Permissions)**:
  - Pembuatan dan pembaruan peran dengan multi-select hak akses (*permissions*).
  - Proteksi otomatis untuk peran `super admin` (akses penuh dan tidak dapat dihapus).
  - Tabel katalog seluruh hak akses (*permissions*) yang terdaftar dalam sistem.

- **Audit & Log Aktivitas (Activity Logs)**:
  - Pencatatan otomatis setiap aksi penting (Create, Update, Delete) beserta detail perubahan data.
  - Pencatatan informasi audit perangkat (IP Address dan User Agent/Browser).
  - Panel filter pencarian berdasarkan pengguna dan rentang tanggal.

- **Sistem Notifikasi Toast**:
  - Integrasi notifikasi toast Sonner dengan indikator warna (*rich colors*: hijau untuk sukses, merah untuk gagal/error) yang terhubung langsung dengan session flash Laravel Inertia.

- **Standardisasi UI / UX**:
  - Breadcrumbs dan judul halaman langsung terintegrasi di dalam *body content*.
  - Form pembuatan dan pengeditan dengan layout kartu *full-width*.
  - Seluruh antarmuka, pesan validasi, dan notifikasi menggunakan Bahasa Indonesia.

### 🏗️ Arsitektur & Backend

- **Service & Repository Pattern**:
  - Pemisahan *business logic* dan *query execution* pada modul User, Role, Permission, dan Activity Log.
- **Form Request Validation**:
  - Validasi input dan filter paginasi dikelompokkan dalam sub-folder fitur masing-masing (`app/Http/Requests/`).
- **Error Handling & Keamanan**:
  - Pengecekan keberadaan data sebelum operasi database.
  - Proteksi transaksi database dengan blok `try-catch` dan pencatatan log error otomatis.
- **Unit Testing**:
  - Pengujian unit untuk `UserService`, `RoleService`, dan `PermissionService`.

---
