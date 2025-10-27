<?php
// Database credentials
define('DB_DSN', 'mysql:host=localhost;dbname=bestanden;charset=utf8mb4');
define('DB_USER', 'user');
define('DB_PASS', 'pass');

// Matomo settings
define('MATOMO_SITE_ID', 1);
define('MATOMO_URL', 'https://matomo.domain.nl/matomo.php');
define('MATOMO_TOKEN', 'token_auth'); // Optional, for IP/User-Agent tracking

// Upload password
define('UPLOAD_PASSWORD', 'password');