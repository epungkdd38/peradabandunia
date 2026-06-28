<?php
require __DIR__ . '/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(['ok' => false, 'message' => 'Method tidak didukung.'], 405);
}

$data = input();
$username = text_value($data['username'] ?? '', 80);
$password = (string)($data['password'] ?? '');
$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$bucket = 'login_attempts_' . hash('sha256', $ip . '|' . $username);
$attempts = $_SESSION[$bucket] ?? ['count' => 0, 'until' => 0];

if (($attempts['until'] ?? 0) > time()) {
    respond(['ok' => false, 'message' => 'Terlalu banyak percobaan login. Coba lagi beberapa menit.'], 429);
}

$stmt = db()->prepare('SELECT id, username, password_hash, name FROM admins WHERE username = ? AND is_active = 1 LIMIT 1');
$stmt->execute([$username]);
$admin = $stmt->fetch();

if (!$admin || !hash_equals($admin['password_hash'], hash('sha256', $password))) {
    $attempts['count'] = (int)($attempts['count'] ?? 0) + 1;
    $attempts['until'] = $attempts['count'] >= 5 ? time() + 900 : 0;
    $_SESSION[$bucket] = $attempts;
    respond(['ok' => false, 'message' => 'Username atau password salah.'], 401);
}

session_regenerate_id(true);
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
$_SESSION['admin_id'] = (int)$admin['id'];
$_SESSION['admin_name'] = $admin['name'];
unset($_SESSION[$bucket]);

respond([
    'ok' => true,
    'csrfToken' => $_SESSION['csrf_token'],
    'admin' => [
        'id' => (int)$admin['id'],
        'username' => $admin['username'],
        'name' => $admin['name'],
    ],
]);
