<?php
$allowed_pages = [
    'gallery' => 'pages/gallery.php',
    'about'   => 'pages/about.php',
    'detail'  => 'pages/detail.php',
];

$page_key = $_GET['page'] ?? 'gallery';
if (!is_string($page_key)) {
    $page_key = '';
}

$isValidPage = array_key_exists($page_key, $allowed_pages);
if (!$isValidPage) {
    http_response_code(404);
}

$activeNav = ($page_key === 'about') ? 'about' : 'posts';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mini-Danbooru A7</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <header class="navbar">
        <div class="nav-left">
            <a href="index.php?page=gallery" class="nav-brand">Mini-Danbooru A7</a>
            <nav class="nav-links">
                <a href="index.php?page=gallery" class="nav-link <?php echo $activeNav === 'posts' ? 'active' : ''; ?>">Posts</a>
                <a href="upload.php" class="nav-link">Upload</a>
                <a href="index.php?page=about" class="nav-link <?php echo $activeNav === 'about' ? 'active' : ''; ?>">About</a>
            </nav>
        </div>
        <div class="nav-tagline">Community image archive</div>
    </header>

    <main class="page-wrapper">
        <?php if ($isValidPage): ?>
            <?php include($allowed_pages[$page_key]); ?>
        <?php else: ?>
            <div class="about-container">
                <h1 class="about-title">404 - Page Not Found</h1>
                <p class="about-text">
                    The requested page is not allowed or does not exist.
                </p>
                <a href="index.php?page=gallery" class="detail-action-link">Return to Posts</a>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>
