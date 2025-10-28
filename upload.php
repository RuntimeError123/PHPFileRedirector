<?php
require_once 'config.php';
session_start();

// Simple password gate
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
    if ($_POST['password'] === ADMIN_PASSWORD) {
        $_SESSION['authenticated'] = true;
    } else {
        die('Invalid password.');
    }
}

if (!isset($_SESSION['authenticated'])) {
    echo '<form method="POST">
        <input type="password" name="password" placeholder="Admin password" required>
        <input type="submit" value="Login">
    </form>';
    exit;
}

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $guid = bin2hex(random_bytes(16)); // 32-char unique ID
    $ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
    $filename = $guid . ($ext ? '.' . strtolower($ext) : '');

    $targetDir = __DIR__ . '/data';
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }

    $targetPath = $targetDir . '/' . $filename;

    if (move_uploaded_file($_FILES['file']['tmp_name'], $targetPath)) {
        $pdo = new PDO(DB_DSN, DB_USER, DB_PASS);
        $stmt = $pdo->prepare("INSERT INTO files (guid, filename) VALUES (?, ?)");
        $stmt->execute([$guid, $filename]);

        // Dynamically build full URL
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
        $baseUrl = $protocol . '://' . $host;

        echo "Upload successful! GUID: <a href='$baseUrl/$guid'>$guid</a>";
    } else {
        echo "Upload failed.";
    }
} else {
    echo '<form method="POST" enctype="multipart/form-data">
        <input type="file" name="file" required>
        <input type="submit" value="Upload">
    </form>';
}
