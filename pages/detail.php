<?php
// pages/detail.php - Tampilan Detail Post Artwork (Sesuai Screenshot 3)
$postsFile = __DIR__ . '/../posts.json';
$allPosts = [];
if (file_exists($postsFile)) {
    $content = file_get_contents($postsFile);
    $allPosts = json_decode($content, true) ?: [];
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 1;
$foundPost = null;

foreach ($allPosts as $p) {
    if (isset($p['id']) && (int)$p['id'] === $id) {
        $foundPost = $p;
        break;
    }
}

// Fallback jika tidak ditemukan
if (!$foundPost && !empty($allPosts)) {
    $foundPost = $allPosts[0];
}

$imageFile = $foundPost ? $foundPost['filename'] : 'contoh.jpg';
$imagePath = 'uploads/' . $imageFile;
$tagsArray = $foundPost ? array_map('trim', explode(',', $foundPost['tags'])) : ['scenery', 'blue_sky'];
?>

<div class="layout-container">
    <!-- Sidebar Kiri (Search, Tags, Information) -->
    <aside class="sidebar">
        <div class="sidebar-section">
            <div class="sidebar-title">Search</div>
            <form action="index.php" method="GET">
                <input type="hidden" name="page" value="pages/gallery.php">
                <input type="text" name="search" class="search-input" placeholder="scenery, blue_sky...">
                <button type="submit" class="btn-search">Search</button>
            </form>
        </div>

        <div class="sidebar-section">
            <div class="sidebar-title">Tags</div>
            <ul class="tag-list">
                <?php foreach ($tagsArray as $tag): ?>
                    <li class="tag-item">
                        <a href="index.php?page=pages/gallery.php&tag=<?= urlencode($tag) ?>"><?= htmlspecialchars($tag) ?></a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div class="sidebar-section">
            <div class="sidebar-title">Information</div>
            <div class="info-item">ID: <?= htmlspecialchars($foundPost['id'] ?? 1) ?></div>
            <div class="info-item">File: <?= htmlspecialchars($foundPost['filename'] ?? 'contoh.jpg') ?></div>
            <div class="info-item">Dimensions: <?= htmlspecialchars($foundPost['dimensions'] ?? '1920×1080') ?></div>
            <div class="info-item">Status: <?= htmlspecialchars($foundPost['status'] ?? 'Public') ?></div>
        </div>
    </aside>

    <!-- Konten Utama Detail Artwork -->
    <main class="main-content">
        <h1 class="page-title"><?= htmlspecialchars($foundPost['title'] ?? 'Contoh Artwork') ?></h1>

        <hr class="divider">

        <div class="detail-preview-container">
            <?php if (file_exists(__DIR__ . '/../' . $imagePath)): ?>
                <img src="<?= htmlspecialchars($imagePath) ?>" alt="<?= htmlspecialchars($foundPost['title'] ?? 'Artwork') ?>" class="detail-image" style="max-width: 100%; height: auto; display: block;">
            <?php else: ?>
                <div style="width: 700px; height: 350px; background: #eef4f9; display: flex; align-items: center; justify-content: center; color: #788c9f;">
                    [Image Preview: <?= htmlspecialchars($imageFile) ?>]
                </div>
            <?php endif; ?>
        </div>

        <div class="detail-actions">
            <a href="<?= htmlspecialchars($imagePath) ?>" target="_blank">View Original File</a>
            <span style="color: #bbb; margin: 0 10px;">|</span>
            <a href="index.php?page=pages/gallery.php">Back to Gallery</a>
        </div>
    </main>
</div>
