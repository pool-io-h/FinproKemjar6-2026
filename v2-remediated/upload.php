<?php
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $tags = trim($_POST['tags'] ?? '');

    if ($title === '') {
        $title = 'Untitled Artwork';
    }

    if (!isset($_FILES['artwork']) || $_FILES['artwork']['error'] !== UPLOAD_ERR_OK) {
        $message = 'Upload failed. Please select a valid image file.';
        $messageType = 'error';
    } else {
        $tmpName = $_FILES['artwork']['tmp_name'];
        $originalName = $_FILES['artwork']['name'];
        $fileSize = (int)$_FILES['artwork']['size'];
        $maxSize = 5 * 1024 * 1024; // 5 MB

        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        // 1. Whitelist ekstensi file
        if (!in_array($ext, $allowedExtensions, true)) {
            $message = 'Rejected: Only JPG, JPEG, PNG, and GIF extensions are allowed.';
            $messageType = 'error';
        } elseif ($fileSize <= 0 || $fileSize > $maxSize) {
            $message = 'Rejected: File size must be between 1 byte and 5 MB.';
            $messageType = 'error';
        } elseif (!is_uploaded_file($tmpName)) {
            $message = 'Rejected: Invalid upload source.';
            $messageType = 'error';
        } else {
            // 2. Validasi MIME type asli & konten gambar
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = $finfo ? finfo_file($finfo, $tmpName) : '';

            $mimeToExt = [
                'image/jpeg' => 'jpg',
                'image/png'  => 'png',
                'image/gif'  => 'gif',
            ];

            $imageInfo = @getimagesize($tmpName);
            $allowedImageTypes = [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_GIF];

            if (!isset($mimeToExt[$mimeType]) || $imageInfo === false || !in_array($imageInfo[2], $allowedImageTypes, true)) {
                $message = 'Rejected: File content is not a valid JPG, PNG, or GIF image.';
                $messageType = 'error';
            } else {
                // 3. Rename file menjadi nama acak berbasis hash/random_bytes
                $safeExt = $mimeToExt[$mimeType];
                $newFilename = bin2hex(random_bytes(16)) . '.' . $safeExt;
                $targetDir = __DIR__ . '/uploads/';

                if (!is_dir($targetDir)) {
                    mkdir($targetDir, 0755, true);
                }

                if (move_uploaded_file($tmpName, $targetDir . $newFilename)) {
                    $postsFile = __DIR__ . '/posts.json';
                    $posts = [];

                    if (file_exists($postsFile)) {
                        $decoded = json_decode(file_get_contents($postsFile), true);
                        if (is_array($decoded)) {
                            $posts = $decoded;
                        }
                    }

                    $maxId = 0;
                    foreach ($posts as $post) {
                        if (isset($post['id']) && (int)$post['id'] > $maxId) {
                            $maxId = (int)$post['id'];
                        }
                    }

                    $newId = $maxId + 1;
                    $posts[] = [
                        'id' => $newId,
                        'title' => $title,
                        'tags' => $tags,
                        'filename' => $newFilename,
                    ];

                    file_put_contents($postsFile, json_encode($posts, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

                    $safeFilename = htmlspecialchars($newFilename, ENT_QUOTES, 'UTF-8');
                    $detailUrl = 'index.php?page=detail&id=' . $newId;
                    $message = "Artwork uploaded securely as <strong>{$safeFilename}</strong>. <a href=\"{$detailUrl}\">View Post</a>";
                    $messageType = 'success';
                } else {
                    $message = 'Failed to save uploaded artwork.';
                    $messageType = 'error';
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload - Mini-Danbooru A7</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <header class="navbar">
        <div class="nav-left">
            <a href="index.php?page=gallery" class="nav-brand">Mini-Danbooru A7</a>
            <nav class="nav-links">
                <a href="index.php?page=gallery" class="nav-link">Posts</a>
                <a href="upload.php" class="nav-link active">Upload</a>
                <a href="index.php?page=about" class="nav-link">About</a>
            </nav>
        </div>
        <div class="nav-tagline">Community image archive</div>
    </header>

    <main class="upload-wrapper">
        <div class="upload-card">
            <div class="upload-card-header">
                <h1 class="upload-card-title">Upload New Artwork</h1>
            </div>
            <div class="upload-card-body">
                <?php if ($message !== ''): ?>
                    <div class="alert alert-<?php echo $messageType; ?>">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>

                <form action="upload.php" method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="title" class="form-label">Post Title</label>
                        <input
                            type="text"
                            id="title"
                            name="title"
                            class="form-control"
                            placeholder="Contoh Artwork"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="tags" class="form-label">Tags (comma separated)</label>
                        <input
                            type="text"
                            id="tags"
                            name="tags"
                            class="form-control"
                            placeholder="scenery, blue_sky"
                        >
                    </div>

                    <div class="form-group">
                        <label for="artwork" class="form-label">Select Image File</label>
                        <div class="file-input-box">
                            <input type="file" id="artwork" name="artwork" accept=".jpg,.jpeg,.png,.gif,image/jpeg,image/png,image/gif" required>
                            <span class="file-hint">(Allowed: JPG, PNG, GIF)</span>
                        </div>
                    </div>

                    <button type="submit" class="submit-btn">Upload Artwork</button>
                </form>
            </div>
        </div>
    </main>
</body>
</html>
