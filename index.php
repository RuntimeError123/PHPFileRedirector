<?php
require_once 'config.php';
require_once 'MatomoTracker.php';

// Extract GUID from the URL path
$guid = basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// Connect to the database
$pdo = new PDO(DB_DSN, DB_USER, DB_PASS);
$stmt = $pdo->prepare("SELECT filename FROM files WHERE guid = ?");
$stmt->execute([$guid]);
$filename = $stmt->fetchColumn();

// If no matching file is found, return 404
if (!$filename) {
    http_response_code(404);
    echo "File not found.";
    exit;
}

// Build dynamic base URL (protocol + host)
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$baseUrl = $protocol . '://' . $host;

// Construct full URL for tracking, optionally including referrer
$url = "$baseUrl/$guid";
if (!empty($_SERVER['HTTP_REFERER'])) {
    $url .= '?ref=' . urlencode($_SERVER['HTTP_REFERER']);
}

// Track download event in Matomo
if (class_exists('MatomoTracker')) {
    $tracker = new MatomoTracker(MATOMO_SITE_ID, MATOMO_URL);
    $tracker->setTokenAuth(MATOMO_TOKEN);
    $tracker->setUrl($url);
    $tracker->setIp($_SERVER['REMOTE_ADDR'] ?? '');
    $tracker->setUserAgent($_SERVER['HTTP_USER_AGENT'] ?? '');
    $tracker->setBrowserLanguage($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '');
    $tracker->doTrackEvent('Download', 'Redirect', $guid);
} else {
    error_log("MatomoTracker class not found.");
}

// Redirect to actual file
header("Location: $baseUrl/data/$filename", true, 302);
exit;
