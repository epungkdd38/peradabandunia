<?php
declare(strict_types=1);

$config = require __DIR__ . '/config.php';

$secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => $secure,
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();

header('Content-Type: application/json; charset=utf-8');
header('X-Robots-Tag: noindex, nofollow', true);
header('X-Frame-Options: SAMEORIGIN');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: camera=(), microphone=(), geolocation=()');

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function db(): PDO
{
    static $pdo = null;
    global $config;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dsn = sprintf(
        'mysql:host=%s;dbname=%s;charset=utf8mb4',
        $config['db_host'],
        $config['db_name']
    );

    $pdo = new PDO($dsn, $config['db_user'], $config['db_pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    return $pdo;
}

function input(): array
{
    $raw = file_get_contents('php://input');
    if (!$raw) {
        return [];
    }
    if (strlen($raw) > 1024 * 1024) {
        respond(['ok' => false, 'message' => 'Payload terlalu besar.'], 413);
    }

    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

function respond(array $payload, int $status = 200): void
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

function require_admin(): void
{
    if (empty($_SESSION['admin_id'])) {
        respond(['ok' => false, 'message' => 'Login admin diperlukan.'], 401);
    }
}

function require_csrf(): void
{
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        return;
    }

    $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        respond(['ok' => false, 'message' => 'Token keamanan tidak valid. Muat ulang halaman lalu coba lagi.'], 403);
    }
}

function require_admin_action(): void
{
    require_admin();
    require_csrf();
}

function text_value($value, int $maxLength = 1000): string
{
    $text = trim((string)$value);
    $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $text) ?? '';
    if (function_exists('mb_substr')) {
        return mb_substr($text, 0, $maxLength);
    }
    return substr($text, 0, $maxLength);
}

function enum_value($value, array $allowed, string $fallback): string
{
    $value = (string)$value;
    return in_array($value, $allowed, true) ? $value : $fallback;
}

function hex_color($value, string $fallback): string
{
    $value = trim((string)$value);
    return preg_match('/^#[0-9a-fA-F]{6}$/', $value) ? $value : $fallback;
}

function wa_number($value, string $fallback): string
{
    $value = preg_replace('/\D+/', '', (string)$value) ?? '';
    return preg_match('/^62[0-9]{8,15}$/', $value) ? $value : $fallback;
}

function slugify(string $text): string
{
    $slug = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $text), '-'));
    return substr($slug ?: 'book-' . time(), 0, 120);
}

function public_upload_path(string $path): string
{
    $path = trim($path);
    if ($path === '') {
        return '';
    }
    return preg_match('#^uploads/covers/[a-zA-Z0-9._-]+$#', $path) ? $path : '';
}

function normalize_book(array $row): array
{
    $description = $row['description'] ?? '';
    $shortDescription = trim((string)($row['short_description'] ?? ''));

    return [
        'id' => $row['id'],
        'title' => $row['title'],
        'author' => $row['author'],
        'isbn' => $row['isbn'] ?? '',
        'price' => (int) $row['price'],
        'priceLabel' => $row['price_label'] ?? '',
        'stock' => (int) $row['stock'],
        'source' => $row['source'],
        'category' => $row['category'],
        'shortDescription' => $shortDescription,
        'description' => $description,
        'coverImage' => public_upload_path((string)($row['cover_image'] ?? '')),
    ];
}

function ensure_book_description_columns(): void
{
    static $checked = false;
    if ($checked) {
        return;
    }

    $checked = true;
    $stmt = db()->query("SHOW COLUMNS FROM books LIKE 'short_description'");
    if (!$stmt->fetch()) {
        db()->exec("ALTER TABLE books ADD short_description TEXT NULL AFTER category");
    }
}
