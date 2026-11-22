<?php
// ==============================================================================
// upload.php - Modul Unggah Gambar (Fokus Anggota 1)
// VERSI: v1-vulnerable (Sengaja Dibuat Rentan untuk Target Pentest)
//
// Kerentanan: Insecure File Upload
// - Tidak ada validasi ekstensi file (whitelist/blacklist).
// - Tidak ada verifikasi tipe MIME / magic bytes file asli.
// - Nama file asli langsung digunakan tanpa randomisasi/sanitasi.
// ==============================================================================

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = isset($_POST['title']) ? trim($_POST['title']) : 'Untitled';
    $tags  = isset($_POST['tags'])  ? trim($_POST['tags'])  : 'untagged';

    if (isset($_FILES['artwork']) && $_FILES['artwork']['error'] === UPLOAD_ERR_OK) {
        // [VULNERABILITY] Mengambil nama file asli langsung dari user
        $filename = $_FILES['artwork']['name'];
        $targetDir = __DIR__ . '/uploads/';
        $targetFile = $targetDir . $filename;

        // [VULNERABILITY] Memindahkan file langsung tanpa filter ekstensi/isi file
        if (move_uploaded_file($_FILES['artwork']['tmp_name'], $targetFile)) {
            // Simpan metadata ke posts.json untuk integrasi dengan gallery (Anggota 2)
            $postsFile = __DIR__ . '/posts.json';
            $posts = [];
            if (file_exists($postsFile)) {
                $content = file_get_contents($postsFile);
                $posts = json_decode($content, true) ?: [];
            }

            $newPost = [
                'id'       => count($posts) + 1,
                'title'    => $title,
                'tags'     => $tags,
                'filename' => $filename
            ];
            $posts[] = $newPost;
            file_put_contents($postsFile, json_encode($posts, JSON_PRETTY_PRINT));

            $message = "File berhasil diunggah! Lokasi: <a href='uploads/{$filename}' target='_blank'>uploads/{$filename}</a>";
            $messageType = 'success';
        } else {
            $message = "Gagal memindahkan file yang diunggah ke direktori uploads/.";
            $messageType = 'danger';
        }
    } else {
        $message = "Silakan pilih file yang valid untuk diunggah.";
        $messageType = 'danger';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Artwork - Mini-Danbooru</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

<header>
    <a href="index.php?page=pages/gallery.php" class="logo">Mini-<span>Danbooru</span></a>
    <nav>
        <ul>
            <li><a href="index.php?page=pages/gallery.php">Gallery</a></li>
            <li><a href="index.php?page=pages/about.php">About</a></li>
            <li><a href="upload.php" class="active btn-upload">+ Upload Artwork</a></li>
        </ul>
    </nav>
</header>

<div class="container">
    <div class="card" style="max-width: 600px; margin: 0 auto;">
        <h2>Upload Artwork Baru</h2>

        <?php if (!empty($message)): ?>
            <div class="alert alert-<?= $messageType ?>">
                <?= $message ?>
            </div>
        <?php endif; ?>

        <form action="upload.php" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="title">Judul Artwork</label>
                <input type="text" id="title" name="title" class="form-control" placeholder="Contoh: Sunset in Cyber Tokyo" required>
            </div>

            <div class="form-group">
                <label for="tags">Tags (Pisahkan dengan koma)</label>
                <input type="text" id="tags" name="tags" class="form-control" placeholder="scenery, landscape, digital_art">
            </div>

            <div class="form-group">
                <label for="artwork">Pilih File Gambar</label>
                <input type="file" id="artwork" name="artwork" class="form-control" required>
            </div>

            <button type="submit" class="btn-submit">Unggah Post</button>
            <a href="index.php?page=pages/gallery.php" style="margin-left: 1rem; color: var(--text-muted); text-decoration: none;">Batal</a>
        </form>
    </div>
</div>

<footer>
    <p>&copy; 2026 Kelompok A7 - Praktikum Keamanan Jaringan UI</p>
</footer>

</body>
</html>
