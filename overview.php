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

// Connect to database
$pdo = new PDO(DB_DSN, DB_USER, DB_PASS);
$stmt = $pdo->query("SELECT guid, filename, uploaded_at, last_accessed FROM files ORDER BY uploaded_at DESC");
$files = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Build dynamic base URL
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$baseUrl = $protocol . '://' . $host;

// Output table
echo "<h1>File Overview</h1>";
echo "<table border='1' cellpadding='6' cellspacing='0'>";
echo "<tr>
        <th>GUID</th>
        <th>Filename</th>
        <th>Uploaded At</th>
        <th>Last Accessed</th>
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
                <form method='POST' action='remove.php' style='display:inline;' onsubmit=\"return confirm('Are you sure you want to remove GUID: $guid?');\">
                    <input type='hidden' name='guid' value='$guid'>
                    <input type='submit' value='Remove'>
                </form>
            </td>
          </tr>";
}

echo "</table>";
