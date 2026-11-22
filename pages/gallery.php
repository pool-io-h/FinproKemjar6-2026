<?php
// pages/gallery.php - Menampilkan galeri gambar dari posts.json (Integrasi dengan upload.php)
$postsFile = __DIR__ . '/../posts.json';
$posts = [];
if (file_exists($postsFile)) {
    $content = file_get_contents($postsFile);
    $posts = json_decode($content, true) ?: [];
}
?>

<h2>Koleksi Artwork Terbaru</h2>
<p style="color: var(--text-muted); margin-bottom: 1rem;">Daftar karya yang diunggah oleh komunitas Mini-Danbooru.</p>

<?php if (empty($posts)): ?>
    <div class="card">
        <p>Belum ada gambar yang diunggah. Jadilah yang pertama dengan <a href="upload.php">mengunggah artwork</a>!</p>
    </div>
<?php else: ?>
    <div class="gallery-grid">
        <?php foreach (array_reverse($posts) as $post): ?>
            <div class="post-card">
                <div class="post-thumb-wrapper">
                    <?php 
                    $imagePath = 'uploads/' . htmlspecialchars($post['filename']);
                    // Deteksi ekstensi file untuk preview jika bukan gambar standar
                    $ext = strtolower(pathinfo($post['filename'], PATHINFO_EXTENSION));
                    if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])): 
                    ?>
                        <img src="<?= $imagePath ?>" alt="<?= htmlspecialchars($post['title']) ?>" loading="lazy">
                    <?php else: ?>
                        <div style="text-align: center; padding: 1rem; color: var(--text-muted);">
                            <span>[File: <?= htmlspecialchars($ext) ?>]</span><br>
                            <small><?= htmlspecialchars($post['filename']) ?></small>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="post-info">
                    <div class="post-title"><?= htmlspecialchars($post['title']) ?></div>
                    <div class="post-tags">Tags: <?= htmlspecialchars($post['tags']) ?></div>
                    <div class="post-meta">
                        <a href="<?= $imagePath ?>" target="_blank" style="color: var(--primary); text-decoration: none;">Buka File Asli &rarr;</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
