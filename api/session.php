<?php
require __DIR__ . '/bootstrap.php';

respond([
    'ok' => true,
    'loggedIn' => !empty($_SESSION['admin_id']),
    'csrfToken' => $_SESSION['csrf_token'] ?? '',
    'admin' => !empty($_SESSION['admin_id']) ? [
        'id' => (int)$_SESSION['admin_id'],
        'name' => $_SESSION['admin_name'] ?? 'Admin',
    ] : null,
]);
