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

// Handle GUID removal
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guid'])) {
    $guid = trim($_POST['guid']);

    // Lookup filename
    $pdo = new PDO(DB_DSN, DB_USER, DB_PASS);
    $stmt = $pdo->prepare("SELECT filename FROM files WHERE guid = ?");
    $stmt->execute([$guid]);
    $filename = $stmt->fetchColumn();

    if ($filename) {
        // Delete file from disk
        $filePath = __DIR__ . '/data/' . $filename;
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        // Delete from database
        $stmt = $pdo->prepare("DELETE FROM files WHERE guid = ?");
        $stmt->execute([$guid]);

        echo "File and record removed for GUID: <strong>$guid</strong>";
    } else {
        echo "No record found for GUID: <strong>$guid</strong>";
    }
} else {
    echo '<form method="POST">
        <input type="text" name="guid" placeholder="Enter GUID to remove" required>
        <input type="submit" value="Remove">
    </form>';
}
