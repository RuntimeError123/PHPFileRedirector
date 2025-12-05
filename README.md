# PHPFileRedirector

A minimal, self-hosted PHP-based file upload and redirect system with Matomo event tracking.  
No frameworks, no JavaScript — just clean server-side logic.

## Features

- Upload files via a password-protected form
- Store files with GUID-based access
- Redirect downloads via `/[guid]` URLs
- Track downloads using Matomo's PHP Tracking API
- Fully dynamic domain detection (no hardcoded hostnames)
- Lightweight and reproducible — no external dependencies

## Requirements

- PHP 7.4+
- PDO with MySQL/MariaDB support
- A web server (Apache, LiteSpeed, nginx)
- Matomo instance (optional, for tracking)

## Setup

1. **Clone the repository**
```bash
git clone https://github.com/yourusername/PHPFileRedirector.git
cd PHPFileRedirector
```

2. **Create the database Import the schema:**

```bash
mysql -u youruser -p yourdatabase < database.sql
```
3. **Configure the application**

* Copy the sample config:
```
cp config.sample.php config.php
```
- Edit config.php and set:
  - DB_DSN, DB_USER, DB_PASS
  - UPLOAD_PASSWORD
  - MATOMO_URL, MATOMO_SITE_ID, MATOMO_TOKEN (optional)

4. Ensure the data/ directory exists and is writable
```bash
mkdir data
chmod 755 data
```

Enable URL rewriting If using Apache or LiteSpeed, ensure .htaccess is active:
```
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^ index.php [QSA,L]
```

## Usage

- Visit /admin.php to upload a file (password required)
- After upload, you'll receive a link like: 
https://yourdomain.com/792ec2272f1145d46283b0865838aedc
- When accessed, the application:
  - Looks up the GUID
  - Tracks the download in Matomo
  - Redirects to /data/filename.ext

The admin.php shows all files in the table and allows an admin to remove them.

## Security Notes

- Upload access is gated by a simple password (stored in config.php)
- No file type restrictions — use at your own risk
- No authentication or rate limiting — intended for personal or internal use

## License

MIT — see LICENSE

## Credits 
Built by L. Bosch
Inspired by simplicity, reproducibility, and explicit configuration.