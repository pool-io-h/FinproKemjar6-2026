<?php
// ==============================================================================
// index.php - Main Router & Layout (Fokus Anggota 2 - LFI)
// VERSI: v1-vulnerable (Sengaja Dibuat Rentan untuk Target Pentest)
//
// Kerentanan: Local File Inclusion (LFI) via Path Traversal
// Input user ($_GET['page']) langsung diteruskan ke include() tanpa sanitasi.
// ==============================================================================

$page = isset($_GET['page']) ? $_GET['page'] : 'pages/gallery.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mini-Danbooru Imageboard</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

<header>
    <a href="index.php?page=pages/gallery.php" class="logo">Mini-<span>Danbooru</span></a>
    <nav>
        <ul>
            <li><a href="index.php?page=pages/gallery.php" class="<?= (strpos($page, 'gallery') !== false) ? 'active' : '' ?>">Gallery</a></li>
            <li><a href="index.php?page=pages/about.php" class="<?= (strpos($page, 'about') !== false) ? 'active' : '' ?>">About</a></li>
            <li><a href="upload.php" class="btn-upload">+ Upload Artwork</a></li>
        </ul>
    </nav>
</header>

<div class="container">
    <?php
    // [VULNERABILITY] Local File Inclusion (LFI)
    // Parameter $page tidak divalidasi terhadap whitelist maupun dicek traversal "../"
    if (file_exists($page)) {
        include($page);
    } else {
        echo "<div class='alert alert-danger'>Halaman <code>" . htmlspecialchars($page) . "</code> tidak ditemukan!</div>";
    }
    ?>
</div>

<footer>
    <p>&copy; 2026 Kelompok A7 - Praktikum Keamanan Jaringan UI</p>
</footer>

</body>
</html>
