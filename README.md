# Aplikasi Manajemen Produk (PHP-MySQL CRUD)

Aplikasi web Manajemen Produk dan Inventaris berbasis **PHP Native (PDO)** dan **MySQL** yang dirancang untuk mengelola data produk secara efisien, responsif, serta menerapkan praktik keamanan web standar industri.

---

## 💻 Persyaratan Sistem (Requirements)

Sebelum menjalankan aplikasi, pastikan sistem Anda memenuhi persyaratan berikut:
- **Web Server**: Apache (Rekomendasi via **XAMPP**)
- **Bahasa Pemrograman**: **PHP 8.0** atau versi yang lebih baru (dengan ekstensi `pdo_mysql` aktif)
- **Database Server**: **MySQL 5.7+** atau **MariaDB 10.4+**

---

## 🚀 Langkah Instalasi & Menjalankan (Step-by-Step)

Ikuti langkah-langkah berikut untuk menginstal dan menjalankan aplikasi dari nol:

### 1. Ekstrak / Tempatkan Folder Proyek
Salin atau ekstrak folder proyek `Project_produk_manager` ke dalam direktori `htdocs` web server XAMPP Anda.  
**Contoh jalur direktori:**
```text
C:\xampp\htdocs\Project_produk_manager
```

---

### 2. Impor Database (`schema.sql`)
1. Buka browser dan akses **phpMyAdmin**:
   ```text
   http://localhost/phpmyadmin
   ```
2. Klik tab **"SQL"** pada bagian atas layar (**tanpa memilih database terlebih dahulu**, karena file SQL sudah dilengkapi perintah pembuat database otomatis `CREATE DATABASE IF NOT EXISTS`).
3. Buka file `schema.sql` (atau cari file `schema.sql` di root direktori proyek), lalu **copy dan paste seluruh isinya** ke dalam kolom query SQL phpMyAdmin.
4. Klik tombol **"Go"** atau **"Kirim"** di bagian kanan bawah.
5. Database `db_produk_manager` dan tabel `products` beserta data awal akan otomatis dibuat.

---

### 3. Cek Konfigurasi Koneksi Database
Buka file `config/database.php`. Secara default, kredensial telah disesuaikan dengan konfigurasi standar XAMPP:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'db_produk_manager');
define('DB_USER', 'root');
define('DB_PASS', ''); // Password kosong secara default di XAMPP
```
> **Catatan:** Jika server MySQL/XAMPP Anda menggunakan username atau password yang berbeda, silakan sesuaikan konstanta di atas.

---

### 4. Akses Aplikasi di Browser
Buka browser pilihan Anda dan navigasikan ke alamat URL berikut:
```text
http://localhost/Project_produk_manager/public/index.php
```

---

## 📁 Struktur Folder Proyek

```text
Project_produk_manager/
├── schema.sql              # Script SQL (pembuat DB, tabel products & data sampel)
├── README.md               # Dokumentasi instalasi & informasi proyek
├── config/
│   └── database.php        # Koneksi PDO MySQL & error handling aman
├── includes/
│   ├── functions.php       # Helper (sanitasi, validasi, CSRF token, format rupiah)
│   ├── header.php          # Template header & wadah alert flash message
│   └── footer.php          # Template footer & penutup HTML
├── public/
│   ├── index.php           # Halaman utama (List produk card grid, stats, search)
│   ├── create.php          # Halaman form tambah produk (Validasi & CSRF)
│   ├── edit.php            # Halaman form edit produk berdasar ID
│   └── delete.php          # Action handler hapus produk (POST + CSRF)
└── assets/
    └── style.css           # Custom CSS styling (Responsif Card Grid & Form)
```

---

## ✨ Daftar Fitur & Keamanan yang Diimplementasikan

### Fitur Utama:
- **CRUD Lengkap**:
  - **Create**: Menambah data produk baru dengan validasi ketat.
  - **Read**: Menampilkan seluruh produk dalam format kartu (card grid) responsif.
  - **Update**: Memperbarui informasi produk berdasarkan ID.
  - **Delete**: Menghapus produk dari database via HTTP POST terproteksi.
- **Dashboard Ringkasan Inventaris**:
  - Total jenis produk.
  - Total unit stok seluruh produk.
  - Total nilai inventaris (Total Rp harga $\times$ stok).
  - Indikator jumlah produk dengan stok habis.
- **Pencarian & Filter Real-time (Server-side)**:
  - Pencarian berdasarkan nama produk atau kategori menggunakan query `GET` yang aman dengan `LIKE` prepared statement.
- **Validasi Server-Side Ketat**:
  - **Nama Produk**: Wajib diisi, panjang minimal 3 karakter, dan harus unik (tidak boleh duplikat).
  - **Harga**: Wajib diisi dan bernilai lebih besar dari 0 (`> 0`).
  - **Stok**: Wajib diisi dan bernilai 0 atau lebih (`>= 0`).
- **Antarmuka UI Responsif**:
  - Tampilan modern berbasis **CSS Grid & Flexbox** yang menyesuaikan ukuran layar (Mobile ~375px, Tablet, hingga Desktop).
  - Badge indikator status stok (Stok Aman, Stok Menipis, Stok Habis).

### Standar Keamanan:
- **PDO Prepared Statements**: Digunakan di 100% query SQL (`SELECT`, `INSERT`, `UPDATE`, `DELETE`) untuk mencegah serangan **SQL Injection**.
- **XSS Prevention**: Menggunakan `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')` pada seluruh output data yang ditampilkan ke HTML.
- **CSRF Protection**: Generator & validator token CSRF pada seluruh formulir POST dan tombol Hapus.
- **Restriksi Method HTTP**: Aksi pembaruan dan penghapusan data hanya menerima metode `POST`.
- **Post/Redirect/Get (PRG) Pattern**: Mencegah duplikasi submit data ketika pengguna me-refresh halaman (tombol F5) setelah menambah/mengubah/menghapus produk.
- **Safe Technical Error Logging**: Error teknis PDO tidak dibocorkan ke pengguna, melainkan dicatat ke file log server (`error_log`).

---

## 🔑 Kredensial Default Database

| Parameter | Nilai Default |
|---|---|
| **Host** | `localhost` |
| **Database Name** | `db_produk_manager` |
| **Username** | `root` |
| **Password** | *(Kosong / Tanpa Password)* |

---

## 🧪 Catatan Pengembangan & Pengujian

Proyek ini telah melalui pengujian manual (manual testing) menggunakan skenario normal dan abnormal, antara lain:
- [x] Input data valid lengkap (Create, Read, Update, Delete).
- [x] Penolakan nama kurang dari 3 karakter.
- [x] Penolakan harga $\le 0$ dan stok $< 0$ secara terpisah.
- [x] Pengujian refresh halaman (F5) setelah submit (tidak ada duplikasi data).
- [x] Percobaan XSS Injection dengan tag HTML/script pada nama produk (terbukti ter-escape aman).
- [x] Pengujian tampilan pada layar smartphone (~375px) tanpa terjadi overflow.
- [x] Percobaan bypass URL `delete.php` via GET request (terbukti diblokir & di-redirect).
- [x] Penolakan nama duplikat saat mode Tambah maupun mode Edit.
