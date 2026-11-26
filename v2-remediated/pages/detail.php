<?php
$postsFile = __DIR__ . '/../posts.json';
$allPosts = [];

if (file_exists($postsFile)) {
    $decoded = json_decode(file_get_contents($postsFile), true);
    if (is_array($decoded)) {
        $allPosts = $decoded;
    }
}

$requestedId = isset($_GET['id']) ? (int)$_GET['id'] : 1;
$currentPost = null;

foreach ($allPosts as $post) {
    if ((int)($post['id'] ?? 0) === $requestedId) {
        $currentPost = $post;
        break;
    }
}

if ($currentPost === null && count($allPosts) > 0) {
    $currentPost = $allPosts[0];
}

$postId = (int)($currentPost['id'] ?? 0);
$postTitle = $currentPost['title'] ?? 'Artwork Not Found';
$postFilename = basename($currentPost['filename'] ?? '');
$postTagsRaw = $currentPost['tags'] ?? '';
$postTags = array_filter(array_map('trim', explode(',', $postTagsRaw)), fn($t) => $t !== '');

$filePath = __DIR__ . '/../uploads/' . $postFilename;
$imageInfo = ($postFilename !== '' && file_exists($filePath)) ? @getimagesize($filePath) : false;
$dimensions = $imageInfo !== false ? ($imageInfo[0] . '×' . $imageInfo[1]) : 'Unknown';
$ext = strtolower(pathinfo($postFilename, PATHINFO_EXTENSION));
$isRenderableImage = $imageInfo !== false && in_array($ext, ['jpg', 'jpeg', 'png', 'gif'], true);
?>
<div class="main-layout">
    <aside class="sidebar">
        <div class="sidebar-section">
            <h2 class="sidebar-title">Search</h2>
            <form action="index.php" method="GET">
                <input type="hidden" name="page" value="gallery">
                <input
                    type="text"
                    name="search"
                    class="search-input"
                    placeholder="scenery, blue_sky..."
                >
                <button type="submit" class="search-btn">Search</button>
            </form>
        </div>

        <div class="sidebar-section">
            <h2 class="sidebar-title">Tags</h2>
            <ul class="tag-list">
                <?php if (count($postTags) === 0): ?>
                    <li class="tag-item"><span class="tag-count">No tags</span></li>
                <?php else: ?>
                    <?php foreach ($postTags as $tag): ?>
                        <li class="tag-item">
                            <a href="index.php?page=gallery&amp;search=<?php echo urlencode($tag); ?>" class="tag-link">
                                <?php echo htmlspecialchars($tag, ENT_QUOTES, 'UTF-8'); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </div>

        <hr class="sidebar-divider">

        <div class="sidebar-section">
            <h2 class="sidebar-title">Information</h2>
            <ul class="info-list">
                <li>ID: <?php echo $postId; ?></li>
                <li>File: <?php echo htmlspecialchars($postFilename !== '' ? $postFilename : 'N/A', ENT_QUOTES, 'UTF-8'); ?></li>
                <li>Dimensions: <?php echo htmlspecialchars($dimensions, ENT_QUOTES, 'UTF-8'); ?></li>
                <li>Status: Public</li>
            </ul>
        </div>
    </aside>

    <section class="content">
        <div class="page-header">
            <h1 class="page-title"><?php echo htmlspecialchars($postTitle, ENT_QUOTES, 'UTF-8'); ?></h1>
        </div>

        <?php if ($currentPost === null): ?>
            <div class="empty-state">
                Post not found.
            </div>
        <?php else: ?>
            <div class="detail-image-frame">
                <?php if ($isRenderableImage): ?>
                    <img
                        src="uploads/<?php echo rawurlencode($postFilename); ?>"
                        alt="<?php echo htmlspecialchars($postTitle, ENT_QUOTES, 'UTF-8'); ?>"
                        class="detail-image"
                    >
                <?php else: ?>
                    <div class="post-thumb-placeholder" style="padding: 64px 20px;">
                        File preview unavailable for <strong><?php echo htmlspecialchars($postFilename, ENT_QUOTES, 'UTF-8'); ?></strong>
                    </div>
                <?php endif; ?>
            </div>

            <div class="detail-actions">
                <?php if ($postFilename !== ''): ?>
                    <a href="uploads/<?php echo rawurlencode($postFilename); ?>" target="_blank" class="detail-action-link">View Original File</a>
                    <span class="detail-action-sep">|</span>
                <?php endif; ?>
                <a href="index.php?page=gallery" class="detail-action-link">Back to Gallery</a>
            </div>
        <?php endif; ?>
    </section>
</div>
