<?php
$page = $_GET['page'] ?? 'pages/gallery.php';
$activeNav = (strpos($page, 'about') !== false) ? 'about' : 'posts';
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
            <a href="index.php?page=pages/gallery.php" class="nav-brand">Mini-Danbooru A7</a>
            <nav class="nav-links">
                <a href="index.php?page=pages/gallery.php" class="nav-link <?php echo $activeNav === 'posts' ? 'active' : ''; ?>">Posts</a>
                <a href="upload.php" class="nav-link">Upload</a>
                <a href="index.php?page=pages/about.php" class="nav-link <?php echo $activeNav === 'about' ? 'active' : ''; ?>">About</a>
            </nav>
        </div>
        <div class="nav-tagline">Community image archive</div>
    </header>

    <main class="page-wrapper">
        <?php
        // Vulnerable LFI: directly includes user-supplied path without validation
        include($page);
        ?>
    </main>
</body>
</html>
