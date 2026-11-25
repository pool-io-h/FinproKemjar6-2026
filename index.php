<?php
// ==============================================================================
// index.php - Main Router & Layout (Fokus Anggota 2 - LFI)
// VERSI: v1-vulnerable (Sengaja Dibuat Rentan untuk Target Pentest)
//
// Kerentanan: Local File Inclusion (LFI) via Path Traversal
// Input user ($_GET['page']) langsung diteruskan ke include() tanpa sanitasi.
// ==============================================================================

$page = isset($_GET['page']) ? $_GET['page'] : 'pages/gallery.php';

// Menentukan judul halaman dan status tab navigasi
$activeTab = 'posts';
$pageTitle = 'Mini-Danbooru A7';

if (strpos($page, 'about') !== false) {
    $activeTab = 'about';
    $pageTitle = 'Mini-Danbooru A7 — About and rules';
} elseif (strpos($page, 'upload') !== false) {
    $activeTab = 'upload';
    $pageTitle = 'Mini-Danbooru A7 — Upload';
} elseif (strpos($page, 'detail') !== false) {
    $activeTab = 'posts';
    $pageTitle = 'Mini-Danbooru A7 — Post detail';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
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
            <li><a href="index.php?page=pages/gallery.php" class="<?= ($activeTab === 'posts') ? 'active' : '' ?>">Posts</a></li>
            <li><a href="upload.php" class="<?= ($activeTab === 'upload') ? 'active' : '' ?>">Upload</a></li>
            <li><a href="index.php?page=pages/about.php" class="<?= ($activeTab === 'about') ? 'active' : '' ?>">About</a></li>
        </ul>
    </div>
    <div class="header-right">
        Community image archive
    </div>
</header>

<?php
// [VULNERABILITY] Local File Inclusion (LFI)
// Parameter $page di-include langsung sehingga attacker bisa memasukkan traversal
if (file_exists($page)) {
    include($page);
} else {
    echo "<div style='padding: 30px;'><div class='alert-box danger'>Error 404: File <code>" . htmlspecialchars($page) . "</code> not found!</div></div>";
}
?>

</body>
</html>
