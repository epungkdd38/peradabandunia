<?php
$config = require __DIR__ . '/api/config.php';

header('Content-Type: application/xml; charset=utf-8');

$baseUrl = 'https://peradabandunia.id/';
$pdo = new PDO(
    sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', $config['db_host'], $config['db_name']),
    $config['db_user'],
    $config['db_pass'],
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
);

$books = $pdo->query('SELECT id, updated_at FROM books WHERE is_active = 1 ORDER BY updated_at DESC')->fetchAll();

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
  <url>
    <loc><?= htmlspecialchars($baseUrl, ENT_XML1) ?></loc>
    <lastmod><?= date('Y-m-d') ?></lastmod>
    <changefreq>weekly</changefreq>
    <priority>1.0</priority>
  </url>
  <url>
    <loc><?= htmlspecialchars($baseUrl . 'about.php', ENT_XML1) ?></loc>
    <lastmod><?= date('Y-m-d') ?></lastmod>
    <changefreq>monthly</changefreq>
    <priority>0.9</priority>
  </url>
<?php foreach ($books as $book): ?>
  <url>
    <loc><?= htmlspecialchars($baseUrl . 'book.php?id=' . rawurlencode($book['id']), ENT_XML1) ?></loc>
    <lastmod><?= htmlspecialchars(substr((string)$book['updated_at'], 0, 10), ENT_XML1) ?></lastmod>
    <changefreq>weekly</changefreq>
    <priority>0.8</priority>
  </url>
<?php endforeach; ?>
</urlset>
