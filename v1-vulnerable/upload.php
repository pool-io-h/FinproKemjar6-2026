<?php
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $tags = trim($_POST['tags'] ?? '');

    if ($title === '') {
        $title = 'Untitled Artwork';
    }

    if (isset($_FILES['artwork']) && $_FILES['artwork']['error'] === UPLOAD_ERR_OK) {
        $filename = $_FILES['artwork']['name'];
        $targetDir = __DIR__ . '/uploads/';

        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        // Vulnerable File Upload: no extension, MIME type, or size validation
        if (move_uploaded_file($_FILES['artwork']['tmp_name'], $targetDir . $filename)) {
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
                'filename' => $filename,
            ];

            file_put_contents($postsFile, json_encode($posts, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            $safeFilename = htmlspecialchars($filename, ENT_QUOTES, 'UTF-8');
            $safeUrl = 'uploads/' . rawurlencode($filename);
            $detailUrl = 'index.php?page=pages/detail.php&id=' . $newId;
            $message = "Artwork uploaded successfully as <strong>{$safeFilename}</strong>. <a href=\"{$detailUrl}\">View Post</a> | <a href=\"{$safeUrl}\" target=\"_blank\">Open File</a>";
            $messageType = 'success';
        } else {
            $message = 'Failed to move uploaded file.';
            $messageType = 'error';
        }
    } else {
        $message = 'Please select a file to upload.';
        $messageType = 'error';
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
            <a href="index.php?page=pages/gallery.php" class="nav-brand">Mini-Danbooru A7</a>
            <nav class="nav-links">
                <a href="index.php?page=pages/gallery.php" class="nav-link">Posts</a>
                <a href="upload.php" class="nav-link active">Upload</a>
                <a href="index.php?page=pages/about.php" class="nav-link">About</a>
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
                            <input type="file" id="artwork" name="artwork" required>
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
