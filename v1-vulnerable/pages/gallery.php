<?php
$postsFile = __DIR__ . '/../posts.json';
$allPosts = [];

if (file_exists($postsFile)) {
    $decoded = json_decode(file_get_contents($postsFile), true);
    if (is_array($decoded)) {
        $allPosts = $decoded;
    }
}

// Compute global tag counts
$tagCounts = [];
foreach ($allPosts as $post) {
    $rawTags = $post['tags'] ?? '';
    $parts = array_filter(array_map('trim', explode(',', $rawTags)), fn($t) => $t !== '');
    foreach ($parts as $tag) {
        $tagKey = strtolower($tag);
        $tagCounts[$tagKey] = ($tagCounts[$tagKey] ?? 0) + 1;
    }
}
arsort($tagCounts);

// Filter posts by search query
$searchQuery = trim($_GET['search'] ?? ($_GET['tag'] ?? ''));
$filteredPosts = $allPosts;

if ($searchQuery !== '') {
    $terms = array_filter(array_map(fn($t) => strtolower(trim($t)), explode(',', $searchQuery)), fn($t) => $t !== '');
    if (count($terms) > 0) {
        $filteredPosts = array_values(array_filter($allPosts, function ($post) use ($terms) {
            $titleLower = strtolower($post['title'] ?? '');
            $tagsLower = strtolower($post['tags'] ?? '');
            $tagArray = array_map('trim', explode(',', $tagsLower));
            foreach ($terms as $term) {
                $inTitle = str_contains($titleLower, $term);
                $inTags = in_array($term, $tagArray, true) || str_contains($tagsLower, $term);
                if (!$inTitle && !$inTags) {
                    return false;
                }
            }
            return true;
        }));
    }
}

// Pagination
$totalFiltered = count($filteredPosts);
$perPage = 8;
$totalPages = max(1, (int)ceil($totalFiltered / $perPage));
$currentPage = isset($_GET['p']) ? max(1, min($totalPages, (int)$_GET['p'])) : 1;
$offset = ($currentPage - 1) * $perPage;
$pagedPosts = array_slice($filteredPosts, $offset, $perPage);

$startIndex = $totalFiltered > 0 ? $offset + 1 : 0;
$endIndex = min($offset + count($pagedPosts), $totalFiltered);
?>
<div class="main-layout">
    <aside class="sidebar">
        <div class="sidebar-section">
            <h2 class="sidebar-title">Search</h2>
            <form action="index.php" method="GET">
                <input type="hidden" name="page" value="pages/gallery.php">
                <input
                    type="text"
                    name="search"
                    class="search-input"
                    placeholder="scenery, blue_sky..."
                    value="<?php echo htmlspecialchars($searchQuery, ENT_QUOTES, 'UTF-8'); ?>"
                >
                <button type="submit" class="search-btn">Search</button>
            </form>
        </div>

        <div class="sidebar-section">
            <h2 class="sidebar-title">Tags</h2>
            <ul class="tag-list">
                <?php foreach ($tagCounts as $tagName => $count): ?>
                    <li class="tag-item">
                        <a
                            href="index.php?page=pages/gallery.php&amp;search=<?php echo urlencode($tagName); ?>"
                            class="tag-link <?php echo strtolower($searchQuery) === $tagName ? 'active-tag' : ''; ?>"
                        >
                            <?php echo htmlspecialchars($tagName, ENT_QUOTES, 'UTF-8'); ?>
                        </a>
                        <span class="tag-count">(<?php echo (int)$count; ?>)</span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </aside>

    <section class="content">
        <div class="page-header">
            <h1 class="page-title">Latest Posts</h1>
            <span class="page-meta"><?php echo $totalFiltered; ?> <?php echo $totalFiltered === 1 ? 'post' : 'posts'; ?></span>
        </div>

        <?php if ($searchQuery !== ''): ?>
            <div class="filter-banner">
                <span>Filtered by: <strong><?php echo htmlspecialchars($searchQuery, ENT_QUOTES, 'UTF-8'); ?></strong></span>
                <a href="index.php?page=pages/gallery.php" class="filter-clear">Clear filter</a>
            </div>
        <?php endif; ?>

        <?php if ($totalFiltered === 0): ?>
            <div class="empty-state">
                No artworks found matching your search.
            </div>
        <?php else: ?>
            <div class="post-grid">
                <?php foreach ($pagedPosts as $post): ?>
                    <?php
                    $postId = (int)($post['id'] ?? 0);
                    $postTitle = $post['title'] ?? 'Untitled';
                    $postFilename = $post['filename'] ?? '';
                    $postTagsRaw = $post['tags'] ?? '';
                    $postTags = array_filter(array_map('trim', explode(',', $postTagsRaw)), fn($t) => $t !== '');
                    $imagePath = __DIR__ . '/../uploads/' . $postFilename;
                    $ext = strtolower(pathinfo($postFilename, PATHINFO_EXTENSION));
                    $isImage = $postFilename !== '' && file_exists($imagePath) && in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true) && @getimagesize($imagePath) !== false;
                    $detailHref = 'index.php?page=pages/detail.php&id=' . $postId;
                    ?>
                    <article class="post-card">
                        <a href="<?php echo htmlspecialchars($detailHref, ENT_QUOTES, 'UTF-8'); ?>" class="post-thumb-box">
                            <?php if ($isImage): ?>
                                <img
                                    src="uploads/<?php echo rawurlencode($postFilename); ?>"
                                    alt="<?php echo htmlspecialchars($postTitle, ENT_QUOTES, 'UTF-8'); ?>"
                                    class="post-thumb-img"
                                >
                            <?php else: ?>
                                <div class="post-thumb-placeholder">
                                    Artwork preview<br>220 × 220
                                </div>
                            <?php endif; ?>
                        </a>

                        <a href="<?php echo htmlspecialchars($detailHref, ENT_QUOTES, 'UTF-8'); ?>" class="post-card-title">
                            <?php echo htmlspecialchars($postTitle, ENT_QUOTES, 'UTF-8'); ?>
                        </a>

                        <div class="post-card-tags">
                            <?php
                            $tagLinks = [];
                            foreach ($postTags as $t) {
                                $tUrl = 'index.php?page=pages/gallery.php&search=' . urlencode($t);
                                $tSafe = htmlspecialchars($t, ENT_QUOTES, 'UTF-8');
                                $tagLinks[] = '<a href="' . htmlspecialchars($tUrl, ENT_QUOTES, 'UTF-8') . '" class="post-card-tag-link">' . $tSafe . '</a>';
                            }
                            echo implode(', ', $tagLinks);
                            ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="gallery-footer">
            <div class="showing-text">
                <?php if ($totalFiltered > 0): ?>
                    Showing <?php echo $startIndex; ?>–<?php echo $endIndex; ?> of <?php echo $totalFiltered; ?> posts
                <?php else: ?>
                    Showing 0 posts
                <?php endif; ?>
            </div>

            <div class="pagination">
                <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                    <?php
                    $pageUrl = 'index.php?page=pages/gallery.php&p=' . $p;
                    if ($searchQuery !== '') {
                        $pageUrl .= '&search=' . urlencode($searchQuery);
                    }
                    ?>
                    <a
                        href="<?php echo htmlspecialchars($pageUrl, ENT_QUOTES, 'UTF-8'); ?>"
                        class="page-btn <?php echo $p === $currentPage ? 'active' : ''; ?>"
                    >
                        <?php echo $p; ?>
                    </a>
                <?php endfor; ?>
            </div>
        </div>
    </section>
</div>
