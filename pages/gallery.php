<?php
// pages/gallery.php - Tampilan Posts / Galeri Mini-Danbooru A7
$postsFile = __DIR__ . '/../posts.json';
$allPosts = [];
if (file_exists($postsFile)) {
    $content = file_get_contents($postsFile);
    $allPosts = json_decode($content, true) ?: [];
}

// Fitur Pencarian & Filter Tag
$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';
$selectedTag = isset($_GET['tag']) ? trim($_GET['tag']) : '';

$filteredPosts = $allPosts;

if (!empty($searchQuery)) {
    $query = strtolower($searchQuery);
    $filteredPosts = array_filter($filteredPosts, function($p) use ($query) {
        return (strpos(strtolower($p['title']), $query) !== false) || 
               (strpos(strtolower($p['tags']), $query) !== false);
    });
} elseif (!empty($selectedTag)) {
    $tagLower = strtolower($selectedTag);
    $filteredPosts = array_filter($filteredPosts, function($p) use ($tagLower) {
        $tags = array_map('trim', explode(',', strtolower($p['tags'])));
        return in_array($tagLower, $tags);
    });
}

// Hitung kemunculan masing-masing tag untuk sidebar
$tagCounts = [];
foreach ($allPosts as $p) {
    if (!empty($p['tags'])) {
        $tags = array_map('trim', explode(',', $p['tags']));
        foreach ($tags as $t) {
            $t = trim($t);
            if (!empty($t)) {
                $tagCounts[$t] = ($tagCounts[$t] ?? 0) + 1;
            }
        }
    }
}
// Urutkan berdasarkan frekuensi terbanyak
arsort($tagCounts);

$totalPosts = count($allPosts);
$currentDisplayCount = count($filteredPosts);
?>

<div class="layout-container">
    <!-- Sidebar Kiri -->
    <aside class="sidebar">
        <div class="sidebar-section">
            <div class="sidebar-title">Search</div>
            <form action="index.php" method="GET">
                <input type="hidden" name="page" value="pages/gallery.php">
                <input type="text" name="search" class="search-input" placeholder="scenery, blue_sky..." value="<?= htmlspecialchars($searchQuery) ?>">
                <button type="submit" class="btn-search">Search</button>
            </form>
        </div>

        <div class="sidebar-section">
            <div class="sidebar-title">Tags</div>
            <ul class="tag-list">
                <?php foreach ($tagCounts as $tag => $count): ?>
                    <li class="tag-item">
                        <a href="index.php?page=pages/gallery.php&tag=<?= urlencode($tag) ?>"><?= htmlspecialchars($tag) ?></a>
                        <span class="tag-count">(<?= $count ?>)</span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </aside>

    <!-- Konten Utama Galeri -->
    <main class="main-content">
        <div class="content-header">
            <h1 class="page-title">Latest Posts</h1>
            <span class="post-count-badge"><?= $totalPosts ?> posts</span>
        </div>

        <hr class="divider">

        <?php if (empty($filteredPosts)): ?>
            <div style="padding: 20px 0; color: #555;">
                Tidak ada artwork yang cocok dengan kriteria pencarian. <a href="index.php?page=pages/gallery.php" style="color: #1d70b8;">Tampilkan semua</a>.
            </div>
        <?php else: ?>
            <div class="posts-grid">
                <?php foreach ($filteredPosts as $post): ?>
                    <?php
                    $detailUrl = "index.php?page=pages/detail.php&id=" . urlencode($post['id']);
                    $imagePath = "uploads/" . $post['filename'];
                    $ext = strtolower(pathinfo($post['filename'], PATHINFO_EXTENSION));
                    $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']) && file_exists(__DIR__ . '/../' . $imagePath);
                    ?>
                    <div class="post-card">
                        <a href="<?= $detailUrl ?>" class="artwork-preview-box">
                            <?php if ($isImage): ?>
                                <img src="<?= htmlspecialchars($imagePath) ?>" alt="<?= htmlspecialchars($post['title']) ?>" style="width: 100%; height: 100%; object-fit: cover; display: block;">
                            <?php else: ?>
                                <span>Artwork preview<br>220 &times; 220</span>
                            <?php endif; ?>
                        </a>
                        <div class="post-card-title">
                            <a href="<?= $detailUrl ?>"><?= htmlspecialchars($post['title']) ?></a>
                        </div>
                        <div class="post-card-tags">
                            <?php 
                            $tags = array_map('trim', explode(',', $post['tags']));
                            $tagLinks = [];
                            foreach ($tags as $tag) {
                                $tagLinks[] = '<a href="index.php?page=pages/gallery.php&tag=' . urlencode($tag) . '">' . htmlspecialchars($tag) . '</a>';
                            }
                            echo implode(', ', $tagLinks);
                            ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="gallery-footer">
            <span class="showing-indicator">Showing 1–<?= $currentDisplayCount ?> of <?= $totalPosts ?> posts</span>
            <div class="pagination-box">1</div>
        </div>
    </main>
</div>
