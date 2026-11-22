<?php
// pages/detail.php - Menampilkan detail dari artwork tertentu
$postsFile = __DIR__ . '/../posts.json';
$posts = [];
if (file_exists($postsFile)) {
    $content = file_get_contents($postsFile);
    $posts = json_decode($content, true) ?: [];
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$foundPost = null;

foreach ($posts as $post) {
    if (isset($post['id']) && $post['id'] === $id) {
        $foundPost = $post;
        break;
    }
}
?>

<div class="card">
    <?php if ($foundPost): ?>
        <h2><?= htmlspecialchars($foundPost['title']) ?></h2>
        <div style="margin: 1.5rem 0; text-align: center; background: #f3f4f6; padding: 1rem; border-radius: 8px;">
            <img src="uploads/<?= htmlspecialchars($foundPost['filename']) ?>" alt="<?= htmlspecialchars($foundPost['title']) ?>" style="max-width: 100%; max-height: 500px; border-radius: 4px;">
        </div>
        <p><strong>Tags:</strong> <?= htmlspecialchars($foundPost['tags']) ?></p>
        <p><strong>Filename:</strong> <code><?= htmlspecialchars($foundPost['filename']) ?></code></p>
        <br>
        <a href="index.php?page=pages/gallery.php" class="btn-submit" style="display: inline-block; text-decoration: none;">&larr; Kembali ke Galeri</a>
    <?php else: ?>
        <h3>Artwork Tidak Ditemukan</h3>
        <p>Post dengan ID <?= htmlspecialchars($id) ?> tidak ditemukan dalam sistem.</p>
        <br>
        <a href="index.php?page=pages/gallery.php" class="btn-submit" style="display: inline-block; text-decoration: none;">&larr; Kembali ke Galeri</a>
    <?php endif; ?>
</div>
