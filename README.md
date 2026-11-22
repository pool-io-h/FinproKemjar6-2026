# Mini-Danbooru - Target Pentest Praktikum Keamanan Jaringan 2026 (Kelompok A7)

Aplikasi web *imageboard* sederhana berbasis PHP Native yang dirancang secara khusus sebagai target pengujian penetrasi (*penetration testing target*) untuk mendemonstrasikan dua kerentanan web utama:
1. **Insecure File Upload** (Fokus Anggota 1)
2. **Local File Inclusion / LFI** (Fokus Anggota 2)

---

## 📁 Struktur Direktori & Pembagian Tugas

```text
FinproKemjar6-2026/
├── index.php              # Halaman utama & router LFI dinamis (Fokus Anggota 2)
├── upload.php             # Form & backend upload gambar (Fokus Anggota 1)
├── posts.json             # Kontrak data integrasi antara Anggota 1 dan Anggota 2
├── config/
│   └── secret.txt         # Data sensitif internal (Target pengujian LFI)
├── pages/
│   ├── gallery.php        # Tampilan galeri gambar (Membaca posts.json)
│   ├── about.php          # Halaman informasi aplikasi
│   └── detail.php         # Tampilan detail artwork
├── uploads/               # Direktori penyimpanan file yang diunggah
│   └── welcome.png        # Gambar sampel awal
├── assets/
│   └── style.css          # Styling imageboard (Shared)
├── Dockerfile             # Konfigurasi container Apache + PHP 8.2
└── docker-compose.yml     # Orkestrasi Docker untuk pentest
```

---

## 🚀 Cara Menjalankan Target

### Opsi A: Menggunakan Docker (Direkomendasikan untuk Pentest)
Pastikan Docker Desktop aktif, lalu jalankan di terminal:
```bash
docker compose up -d --build
```
Aplikasi akan dapat diakses di:
* **Web UI:** `http://localhost:8080` (atau IP host/VM Anda untuk pemindaian via Kali Linux).
* **Port Target:** `8080` (HTTP).

Untuk menghentikan:
```bash
docker compose down
```

### Opsi B: Menggunakan PHP Built-in Server (Cepat / Development)
Jika PHP sudah terpasang di sistem:
```bash
php -S 0.0.0.0:8080
```

---

## 🔗 Titik Integrasi Antar-Anggota

1. **Kontrak `posts.json`**:
   - **Anggota 1 (`upload.php`)**: Menulis metadata gambar baru ke `posts.json` dan memindahkan file ke `uploads/`.
   - **Anggota 2 (`pages/gallery.php`)**: Membaca `posts.json` dan menampilkannya dalam grid galeri.
2. **Skenario Eksploitasi Terintegrasi (Chained Attack)**:
   - Anggota 1 mengunggah *web shell* (misal `shell.php` atau `payload.jpg` berisi kode PHP).
   - Anggota 2 mengeksekusi payload tersebut melalui router LFI: `http://localhost:8080/index.php?page=uploads/shell.php`.

---

## 🛡️ Rencana Branching Git
* `v1-vulnerable`: Versi target rentan untuk tahap **Enumeration** dan **Exploitation**.
* `v2-remediated`: Versi target yang telah diperbaiki untuk tahap **Remediation** dan **Proof**.
