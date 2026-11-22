# Rencana Pengkodean Target Pentest: Mini-Imageboard (Kelompok A7)

Dokumen ini berisi rencana teknis pembuatan aplikasi target sederhana bertema *imageboard* (terinspirasi dari Danbooru) yang dibangun dari nol sesuai ketentuan SOP Proyek Akhir Praktikum Keamanan Jaringan 2026.

* **Topik Kerentanan:** Insecure File Upload & Local File Inclusion (LFI)
* **Tech Stack Disarankan:** PHP Native (8.x) + HTML/CSS sederhana + Apache/Nginx (atau PHP Built-in Server / Docker)
* **Alasan Pemilihan Stack:** PHP sangat ideal dan eksplisit untuk mendemonstrasikan kedua kerentanan secara langsung (eksekusi file `.php` yang terunggah dan fungsi `include()` untuk LFI) tanpa memerlukan konfigurasi yang terlalu rumit.

---

## 1. Struktur Direktori Proyek

Agar pembagian kerja jelas dan minim konflik kode, gunakan struktur folder berikut:

```text
mini-danbooru/
├── index.php              # Halaman utama & router tampilan (Fokus Anggota 2 - LFI)
├── upload.php             # Form & logika unggah gambar (Fokus Anggota 1 - File Upload)
├── config/
│   └── secret.txt         # File simulasi data sensitif internal untuk target uji LFI
├── pages/
│   ├── gallery.php        # Tampilan daftar gambar yang sudah diunggah
│   ├── about.php          # Halaman informasi/aturan imageboard
│   └── detail.php         # Halaman detail gambar
├── uploads/               # Direktori penyimpanan file hasil unggahan user
│   └── .htaccess          # (Ditambahkan saat tahap Remediation oleh Anggota 1)
└── assets/
    └── style.css          # Styling sederhana bergaya imageboard (opsional/bersama)
```

---

## 2. Pembagian Tugas Pengkodean

### A. Tugas Bersama (Setup Awal & Integrasi)
Sebelum masuk ke fitur masing-masing, kerjakan bersama-sama:
1. Menyiapkan *environment* lokal/VM (misalnya Apache + PHP di Linux atau Docker container) agar bisa diakses untuk pemindaian (`nmap`, `gobuster`, dll.).
2. Membuat file `assets/style.css` sederhana untuk layout *grid* gambar dan navigasi dasar.
3. Membuat file `config/secret.txt` (berisi teks dummy seperti `API_KEY=netlab_ui_2026_secret`) sebagai alternatif target pembacaan file selain `/etc/passwd` saat pengujian LFI.
4. Menjaga dua versi *branch* atau folder: `v1-vulnerable` (untuk tahap *Exploitation*) dan `v2-remediated` (untuk tahap *Proof*).

---

### B. Tugas Anggota 1: Fokus *Insecure File Upload* (`upload.php`)

Anggota 1 bertanggung jawab membangun alur unggah gambar beserta celah keamanannya, lalu mengamankannya pada tahap remediasi.

#### 1. Tahap Target Creation (`v1-vulnerable`)
* **File:** `upload.php`
* **Alur Logika:**
  1. Menampilkan form HTML dengan input `title` (judul post), `tags` (tag sederhana), dan `file` (`<input type="file" name="artwork">`).
  2. Saat form disubmit (`POST`), skrip langsung mengambil nama file asli dari `$_FILES['artwork']['name']`.
  3. Memindahkan file langsung ke `uploads/` menggunakan `move_uploaded_file($_FILES['artwork']['tmp_name'], "uploads/" . $filename)` **tanpa pengecekan ekstensi, tipe MIME, maupun ukuran**.
  4. Menyimpan metadata sederhana (judul, tag, nama file) ke file JSON (`posts.json`) atau database ringan agar bisa ditampilkan di galeri.
* **Titik Lemah (Vulnerable Point):** File berbahaya seperti `shell.php` dapat diunggah dan langsung dieksekusi dengan mengakses `http://<target>/uploads/shell.php`.

#### 2. Tahap Remediation (`v2-remediated`)
* **Perbaikan Kode (`upload.php`):**
  1. **Whitelist Ekstensi:** Hanya mengizinkan ekstensi `['jpg', 'jpeg', 'png', 'gif']`.
  2. **Validasi MIME Type Asli:** Menggunakan `finfo_file()` atau `getimagesize()` untuk memastikan isi file benar-benar gambar, bukan skrip PHP yang diganti ekstensinya.
  3. **Rename File:** Mengubah nama file yang disimpan menjadi nama acak berbasis hash/UUID (misalnya `bin2hex(random_bytes(16)) . '.' . $ext`) agar penyerang tidak bisa menebak nama file dengan mudah.
* **Perbaikan Server (`uploads/.htaccess`):**
  * Menambahkan aturan untuk menonaktifkan eksekusi skrip PHP di dalam folder `uploads/` (misalnya `php_flag engine off` atau memblokir akses eksekusi file skrip).

---

### C. Tugas Anggota 2: Fokus *Local File Inclusion (LFI)* (`index.php` & `pages/`)

Anggota 2 bertanggung jawab membangun sistem navigasi halaman dinamis yang memuat file lokal, lalu memperbaikinya pada tahap remediasi.

#### 1. Tahap Target Creation (`v1-vulnerable`)
* **File:** `index.php`, `pages/gallery.php`, `pages/about.php`, `pages/detail.php`
* **Alur Logika:**
  1. `index.php` berfungsi sebagai kerangka utama (*layout*) yang memiliki menu navigasi:
     * `index.php?page=pages/gallery.php`
     * `index.php?page=pages/about.php`
  2. Di bagian konten utama `index.php`, skrip mengambil parameter `$_GET['page']` (dengan nilai default `pages/gallery.php`).
  3. Memuat file menggunakan fungsi `include($page);` **tanpa validasi atau sanitasi jalur (*path*)**.
  4. `pages/gallery.php` membaca data dari `posts.json` (yang diisi oleh fitur Anggota 1) dan menampilkan daftar gambar beserta tag-nya.
* **Titik Lemah (Vulnerable Point):** Parameter `page` dapat dimanipulasi dengan *path traversal*, misalnya:
  * `index.php?page=config/secret.txt`
  * `index.php?page=../../../../etc/passwd`

#### 2. Tahap Remediation (`v2-remediated`)
* **Perbaikan Kode (`index.php`):**
  1. **Whitelist Halaman (Strict Mapping):** Mengganti pemanggilan jalur file langsung menjadi pemetaan berbasis daftar tetap (*array* atau `switch-case`):
     ```php
     $allowed_pages = [
         'gallery' => 'pages/gallery.php',
         'about'   => 'pages/about.php',
         'detail'  => 'pages/detail.php'
     ];
     $page_key = $_GET['page'] ?? 'gallery';
     if (array_key_exists($page_key, $allowed_pages)) {
         include($allowed_pages[$page_key]);
     } else {
         include('pages/gallery.php'); // atau tampilkan halaman 404
     }
     ```
  2. **Sanitasi Tambahan:** Memastikan tidak ada input user yang diteruskan langsung ke fungsi `include()` atau `require()`.

---

## 3. Titik Integrasi Antar-Anggota

Agar kedua fitur terasa seperti satu aplikasi *imageboard* yang utuh:
1. **Format Data `posts.json`:** Anggota 1 (penulis data saat upload) dan Anggota 2 (pembaca data di `gallery.php`) menyepakati format JSON sederhana berikut:
   ```json
   [
     {
       "id": 1,
       "title": "Contoh Artwork",
       "tags": "scenery, blue_sky",
       "filename": "contoh.jpg"
     }
   ]
   ```
2. **Skenario Pengujian Berantai (Opsional untuk Nilai Tambah di Laporan):**
   * Selain mendemonstrasikan masing-masing kerentanan secara terpisah, kelompok dapat menunjukkan skenario lanjutan di mana file gambar berisi payload PHP yang diunggah lewat fitur Anggota 1 dieksekusi melalui celah LFI milik Anggota 2 (sebelum remediasi dilakukan).

---

## 4. Checklist Eksekusi Tahapan Pentest

- [ ] **Target Creation:** Kode `v1-vulnerable` selesai dan berjalan lancar di server lokal/VM.
- [ ] **Enumeration:** Dokumentasi hasil `nmap`, identifikasi *web stack*, dan *directory scanning* (`gobuster`/`dirsearch`).
- [ ] **Exploitation (Anggota 1):** Bukti unggah *web shell* (`shell.php`) dan eksekusi perintah sistem (`RCE`).
- [ ] **Exploitation (Anggota 2):** Bukti pembacaan file lokal (`config/secret.txt` dan `/etc/passwd`) via *path traversal*.
- [ ] **Remediation:** Penerapan kode `v2-remediated` oleh masing-masing anggota.
- [ ] **Proof:** Pengujian ulang eksploitasi yang sama pada `v2-remediated` dengan hasil gagal/diblokir.
- [ ] **Laporan & Presentasi:** Penggabungan bukti *screenshot* ke template laporan **emas3** sebelum **3 Desember 2026**.