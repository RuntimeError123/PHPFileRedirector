<?php
require_once 'config.php';
session_start();

// --- Authentication ---
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

$pdo = new PDO(DB_DSN, DB_USER, DB_PASS);

// --- Handle file upload ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $guid = bin2hex(random_bytes(16));
    $ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
    $filename = $guid . ($ext ? '.' . strtolower($ext) : '');

    $targetDir = __DIR__ . '/data';
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }
    $targetPath = $targetDir . '/' . $filename;

    if (move_uploaded_file($_FILES['file']['tmp_name'], $targetPath)) {
        $stmt = $pdo->prepare("INSERT INTO files (guid, filename) VALUES (?, ?)");
        $stmt->execute([$guid, $filename]);
        echo "<p style='color:green;'>Upload successful! GUID: <a href='/$guid'>$guid</a></p>";
    } else {
        echo "<p style='color:red;'>Upload failed.</p>";
    }
}

// --- Handle removal ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guid']) && !isset($_FILES['file'])) {
    $guid = trim($_POST['guid']);
    $stmt = $pdo->prepare("SELECT filename FROM files WHERE guid = ?");
    $stmt->execute([$guid]);
    $filename = $stmt->fetchColumn();

    if ($filename) {
        $filePath = __DIR__ . '/data/' . $filename;
        if (file_exists($filePath)) {
            unlink($filePath);
        }
        $stmt = $pdo->prepare("DELETE FROM files WHERE guid = ?");
        $stmt->execute([$guid]);
        echo "<p style='color:green;'>File and record removed for GUID: <strong>$guid</strong></p>";
    } else {
        echo "<p style='color:red;'>No record found for GUID: <strong>$guid</strong></p>";
    }
}

// --- Sorting ---
$validSort = ['guid', 'filename', 'uploaded_at', 'last_accessed'];
$sort = $_GET['sort'] ?? 'uploaded_at';
$order = $_GET['order'] ?? 'DESC';
if (!in_array($sort, $validSort)) $sort = 'uploaded_at';
$order = strtoupper($order) === 'ASC' ? 'ASC' : 'DESC';

$stmt = $pdo->query("SELECT guid, filename, uploaded_at, last_accessed FROM files ORDER BY $sort $order");
$files = $stmt->fetchAll(PDO::FETCH_ASSOC);

// --- Build dynamic base URL ---
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$baseUrl = $protocol . '://' . $host;

// --- Upload form ---
echo "<h1>Admin Panel</h1>";
echo "<h2>Upload File</h2>";
echo '<form method="POST" enctype="multipart/form-data">
        <input type="file" name="file" required>
        <input type="submit" value="Upload">
      </form>';

// --- Overview table ---
echo "<h2>File Overview</h2>";
echo "<table border='1' cellpadding='6' cellspacing='0'>";
echo "<tr>
        <th><a href='?sort=guid&order=" . ($sort==='guid' && $order==='ASC'?'DESC':'ASC') . "'>GUID</a></th>
        <th><a href='?sort=filename&order=" . ($sort==='filename' && $order==='ASC'?'DESC':'ASC') . "'>Filename</a></th>
        <th><a href='?sort=uploaded_at&order=" . ($sort==='uploaded_at' && $order==='ASC'?'DESC':'ASC') . "'>Uploaded At</a></th>
        <th><a href='?sort=last_accessed&order=" . ($sort==='last_accessed' && $order==='ASC'?'DESC':'ASC') . "'>Last Accessed</a></th>
        <th>Link</th>
        <th>Remove</th>
      </tr>";

foreach ($files as $file) {
    $guid = htmlspecialchars($file['guid']);
    $filename = htmlspecialchars($file['filename']);
    $uploaded = htmlspecialchars($file['uploaded_at']);
    $lastAccessed = $file['last_accessed'] ? htmlspecialchars($file['last_accessed']) : 'Never';
    $link = "$baseUrl/$guid";

    echo "<tr>
            <td>$guid</td>
            <td>$filename</td>
            <td>$uploaded</td>
            <td>$lastAccessed</td>
            <td><a href='$link' target='_blank'>Open</a></td>
            <td>
                <form method='POST' style='display:inline;' onsubmit=\"return confirm('Are you sure you want to remove GUID: $guid?');\">
                    <input type='hidden' name='guid' value='$guid'>
                    <input type='submit' value='Remove'>
                </form>
            </td>
          </tr>";
}

echo "</table>";
