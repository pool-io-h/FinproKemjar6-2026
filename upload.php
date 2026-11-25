<?php
// ==============================================================================
// upload.php - Modul Unggah Gambar (Fokus Anggota 1)
// VERSI: v1-vulnerable (Sengaja Dibuat Rentan untuk Target Pentest)
//
// Kerentanan: Insecure File Upload
// - Tidak ada validasi whitelist ekstensi (penyerang dapat mengunggah file .php).
// - Tidak ada pengecekan MIME type / magic bytes file sebenarnya.
// - Nama file asli langsung digunakan tanpa sanitasi atau randomisasi.
// ==============================================================================

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = isset($_POST['title']) ? trim($_POST['title']) : 'Contoh Artwork';
    $tags  = isset($_POST['tags'])  ? trim($_POST['tags'])  : 'scenery, blue_sky';

    if (isset($_FILES['artwork']) && $_FILES['artwork']['error'] === UPLOAD_ERR_OK) {
        // [VULNERABILITY] Nama file diambil mentah dari input pengguna
        $filename = $_FILES['artwork']['name'];
        $targetDir = __DIR__ . '/uploads/';
        $targetFile = $targetDir . $filename;

        // [VULNERABILITY] File langsung dipindahkan tanpa filter keamanan
        if (move_uploaded_file($_FILES['artwork']['tmp_name'], $targetFile)) {
            $postsFile = __DIR__ . '/posts.json';
            $posts = [];
            if (file_exists($postsFile)) {
                $content = file_get_contents($postsFile);
                $posts = json_decode($content, true) ?: [];
            }

            // Hitung dimensi jika file gambar valid, atau gunakan fallback
            $dimensions = '1920×1080';
            $imageInfo = @getimagesize($targetFile);
            if ($imageInfo) {
                $dimensions = $imageInfo[0] . '×' . $imageInfo[1];
            }

            $newPost = [
                'id'         => count($posts) + 1,
                'title'      => $title,
                'tags'       => $tags,
                'filename'   => $filename,
                'dimensions' => $dimensions,
                'status'     => 'Public'
            ];
            $posts[] = $newPost;
            file_put_contents($postsFile, json_encode($posts, JSON_PRETTY_PRINT));

            $message = "File berhasil diunggah! Akses di: <a href='uploads/{$filename}' target='_blank'>uploads/{$filename}</a> | <a href='index.php?page=pages/gallery.php'>Lihat di Posts</a>";
            $messageType = 'success';
        } else {
            $message = "Gagal mengunggah file ke direktori uploads/. Periksa permission direktori.";
            $messageType = 'danger';
        }
    } else {
        $message = "Silakan pilih file yang akan diunggah.";
        $messageType = 'danger';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mini-Danbooru A7 — Upload</title>
    <!-- Cache-busting link + inline fallback agar CSS selalu termuat sempurna -->
    <link rel="stylesheet" href="assets/style.css?v=<?= time() ?>">
    <style>
        <?php if (file_exists(__DIR__ . '/assets/style.css')) include __DIR__ . '/assets/style.css'; ?>
    </style>
</head>
<body>

<header>
    <div class="header-left">
        <a href="index.php?page=pages/gallery.php" class="brand-title">Mini-Danbooru A7</a>
        <ul class="nav-links">
            <li><a href="index.php?page=pages/gallery.php">Posts</a></li>
            <li><a href="upload.php" class="active">Upload</a></li>
            <li><a href="index.php?page=pages/about.php">About</a></li>
        </ul>
    </div>
    <div class="header-right">
        Community image archive
    </div>
</header>

<div class="upload-wrapper">
    <div class="upload-card">
        <div class="upload-card-header">
            Upload New Artwork
        </div>
        <div class="upload-card-body">
            <?php if (!empty($message)): ?>
                <div class="alert-box <?= $messageType ?>">
                    <?= $message ?>
                </div>
            <?php endif; ?>

            <form action="upload.php" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="title">Post Title</label>
                    <input type="text" id="title" name="title" class="form-control-text" placeholder="Contoh Artwork" required>
                </div>

                <div class="form-group">
                    <label for="tags">Tags (comma separated)</label>
                    <input type="text" id="tags" name="tags" class="form-control-text" placeholder="scenery, blue_sky" required>
                </div>

                <div class="form-group">
                    <label for="artwork">Select Image File</label>
                    <div class="file-input-wrapper">
                        <input type="file" id="artwork" name="artwork" required>
                        <span style="font-size: 11px; color: #666; margin-left: 5px;">(Allowed: JPG, PNG, GIF)</span>
                    </div>
                </div>

                <button type="submit" class="btn-upload-submit">Upload Artwork</button>
            </form>
        </div>
    </div>
</div>

</body>
</html>
