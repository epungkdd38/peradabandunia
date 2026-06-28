<?php
require __DIR__ . '/bootstrap.php';

require_csrf();
$_SESSION = [];
session_destroy();

respond(['ok' => true]);
