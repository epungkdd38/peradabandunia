<?php
$config = require __DIR__ . '/api/config.php';

function h($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function rupiah(int $value): string
{
    return 'Rp' . number_format($value, 0, ',', '.');
}

function excerpt(?string $value, int $length): string
{
    $text = trim((string)$value);
    if (function_exists('mb_substr')) {
        return mb_substr($text, 0, $length);
    }
    return substr($text, 0, $length);
}

function pdo(array $config): PDO
{
    $dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', $config['db_host'], $config['db_name']);
    return new PDO($dsn, $config['db_user'], $config['db_pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
}

$db = pdo($config);
$id = trim((string)($_GET['id'] ?? ''));

$stmt = $db->prepare('SELECT * FROM books WHERE id = ? AND is_active = 1 LIMIT 1');
$stmt->execute([$id]);
$book = $stmt->fetch();

if (!$book) {
    http_response_code(404);
    $book = [
        'id' => '',
        'title' => 'Buku tidak ditemukan',
        'author' => 'Pilar Peradaban Dunia',
        'isbn' => '',
        'price' => 0,
        'price_label' => '',
        'stock' => 0,
        'source' => '',
        'category' => '',
        'short_description' => '',
        'description' => 'Buku yang Anda cari tidak tersedia atau sudah tidak aktif.',
        'cover_image' => '',
    ];
}

$settings = [];
foreach ($db->query('SELECT setting_key, setting_value FROM site_settings') as $row) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

$brandName = $settings['brand_name'] ?? 'Pilar Peradaban Dunia';
$brandTagline = $settings['brand_tagline'] ?? 'Penerbit & Book Services';
$waNumber = $settings['whatsapp_number'] ?? ($config['wa_number'] ?? '628993998544');
$baseUrl = 'https://peradabandunia.id/';
$bookUrl = $baseUrl . 'book.php?id=' . rawurlencode($book['id']);
$cover = $book['cover_image'] ? $baseUrl . ltrim($book['cover_image'], '/') : $baseUrl . 'assets/logo-pilar.svg';
$priceText = $book['price_label'] ?: rupiah((int)$book['price']);
$shortDescription = trim((string)($book['short_description'] ?? ''));
$longDescription = trim((string)($book['description'] ?? ''));
$metaDescription = $shortDescription ?: excerpt($longDescription, 155);

$relatedStmt = $db->prepare('SELECT id, title, author, price, price_label, cover_image FROM books WHERE is_active = 1 AND id <> ? ORDER BY created_at DESC LIMIT 4');
$relatedStmt->execute([$book['id']]);
$relatedBooks = $relatedStmt->fetchAll();
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="<?= h($metaDescription) ?>">
  <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1">
  <link rel="canonical" href="<?= h($bookUrl) ?>">
  <link rel="icon" href="assets/logo-pilar.svg" type="image/svg+xml">
  <meta property="og:type" content="product">
  <meta property="og:locale" content="id_ID">
  <meta property="og:site_name" content="<?= h($brandName) ?>">
  <meta property="og:title" content="<?= h($book['title']) ?>">
  <meta property="og:description" content="<?= h($shortDescription ?: excerpt($longDescription, 180)) ?>">
  <meta property="og:url" content="<?= h($bookUrl) ?>">
  <meta property="og:image" content="<?= h($cover) ?>">
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="<?= h($book['title']) ?>">
  <meta name="twitter:description" content="<?= h($shortDescription ?: excerpt($longDescription, 180)) ?>">
  <meta name="twitter:image" content="<?= h($cover) ?>">
  <title><?= h($book['title']) ?> | <?= h($brandName) ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="styles.css?v=20260627-10">
  <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "Book",
      "@id": "<?= h($bookUrl) ?>#book",
      "name": "<?= h($book['title']) ?>",
      "author": { "@type": "Person", "name": "<?= h($book['author']) ?>" },
      "isbn": "<?= h($book['isbn'] ?? '') ?>",
      "image": "<?= h($cover) ?>",
      "description": "<?= h($longDescription ?: $shortDescription) ?>",
      "publisher": {
        "@type": "Organization",
        "name": "<?= h($brandName) ?>",
        "url": "<?= h($baseUrl) ?>"
      },
      "offers": {
        "@type": "Offer",
        "url": "<?= h($bookUrl) ?>",
        "priceCurrency": "IDR",
        "price": "<?= (int)$book['price'] ?>",
        "availability": "<?= ((int)$book['stock'] > 0) ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock' ?>"
      }
    }
  </script>
</head>
<body>
  <header class="site-header">
    <a class="brand" href="index.html" aria-label="<?= h($brandName) ?>">
      <img class="brand-logo" src="assets/logo-pilar.svg" alt="Yayasan Pilar Peradaban Dunia">
      <span>
        <strong><?= h($brandName) ?></strong>
        <small><?= h($brandTagline) ?></small>
      </span>
    </a>
    <nav class="nav-links" aria-label="Navigasi utama">
      <a href="about.php">Tentang</a>
      <a href="index.html#layanan">Layanan</a>
      <a href="index.html#katalog">Katalog</a>
      <a href="admin.html">Admin</a>
    </nav>
    <div class="header-actions">
      <a class="wa-action" href="https://wa.me/<?= h($waNumber) ?>" target="_blank" rel="noreferrer">WhatsApp</a>
    </div>
  </header>

  <main>
    <section class="section product-page">
      <div class="product-shell">
        <div class="product-cover">
          <?php if ($book['cover_image']): ?>
            <img src="<?= h($book['cover_image']) ?>" alt="Cover <?= h($book['title']) ?>">
          <?php else: ?>
            <strong><?= h($book['title']) ?></strong>
          <?php endif; ?>
        </div>
        <article class="product-info">
          <a class="secondary-action compact" href="index.html#katalog">Kembali ke Katalog</a>
          <div class="book-meta">
            <?php if ($book['source']): ?><span class="tag"><?= h($book['source']) ?></span><?php endif; ?>
            <?php if ($book['category']): ?><span class="tag"><?= h($book['category']) ?></span><?php endif; ?>
          </div>
          <h1><?= h($book['title']) ?></h1>
          <p class="detail-author"><?= h($book['author']) ?></p>
          <div class="detail-facts">
            <div><span>Harga</span><strong><?= h($priceText) ?></strong></div>
            <div><span>Stok</span><strong><?= (int)$book['stock'] ?></strong></div>
            <div><span>ISBN / SKU</span><strong><?= h($book['isbn'] ?: '-') ?></strong></div>
          </div>
          <p class="detail-description"><?= nl2br(h($longDescription ?: $shortDescription)) ?></p>
          <?php if ($book['id']): ?>
          <div class="detail-actions">
            <button class="primary-action" type="button" data-add="<?= h($book['id']) ?>" <?= ((int)$book['stock'] < 1) ? 'disabled' : '' ?>><?= $book['price_label'] ? 'Tanya via Keranjang' : 'Tambah ke Keranjang' ?></button>
            <a class="secondary-action" href="https://wa.me/<?= h($waNumber) ?>?text=Halo%20Pilar%20Peradaban%20Dunia,%20saya%20ingin%20bertanya%20tentang%20buku%20<?= rawurlencode($book['title']) ?>" target="_blank" rel="noreferrer">Tanya WhatsApp</a>
          </div>
          <?php endif; ?>
        </article>
      </div>
    </section>

    <?php if ($relatedBooks): ?>
    <section class="section catalog-section">
      <div class="section-heading">
        <div>
          <p class="eyebrow">Buku lainnya</p>
          <h2>Judul lain dari katalog kami</h2>
        </div>
      </div>
      <div class="book-grid">
        <?php foreach ($relatedBooks as $related): ?>
        <a class="book-card" href="book.php?id=<?= h($related['id']) ?>">
          <div class="cover <?= $related['cover_image'] ? 'has-cover' : '' ?>">
            <?php if ($related['cover_image']): ?>
              <img src="<?= h($related['cover_image']) ?>" alt="Cover <?= h($related['title']) ?>" loading="lazy">
            <?php else: ?>
              <strong><?= h($related['title']) ?></strong>
            <?php endif; ?>
          </div>
          <div class="book-body">
            <h3 class="book-title"><?= h($related['title']) ?></h3>
            <p class="book-author"><?= h($related['author']) ?></p>
            <div class="book-footer"><div class="price"><?= h($related['price_label'] ?: rupiah((int)$related['price'])) ?></div></div>
          </div>
        </a>
        <?php endforeach; ?>
      </div>
    </section>
    <?php endif; ?>
  </main>

  <button class="cart-toggle cart-bubble" type="button" aria-label="Buka keranjang">
    <span>Keranjang</span>
    <strong id="cartCount">0</strong>
  </button>

  <aside class="cart-drawer" id="cartDrawer" aria-hidden="true">
    <div class="cart-panel">
      <div class="cart-head">
        <h2>Keranjang</h2>
        <button class="icon-button" id="closeCart" type="button" aria-label="Tutup keranjang">Tutup</button>
      </div>
      <div id="cartItems" class="cart-items"></div>
      <form class="checkout-form" id="checkoutForm">
        <h3>Data pemesan</h3>
        <input name="name" placeholder="Nama lengkap" required>
        <input name="phone" placeholder="Nomor WhatsApp" required>
        <textarea name="address" rows="3" placeholder="Alamat pengiriman" required></textarea>
        <textarea name="notes" rows="2" placeholder="Catatan tambahan"></textarea>
        <div class="cart-total"><span>Total</span><strong id="cartTotal">Rp0</strong></div>
        <button class="primary-action full" type="submit">Buat Order</button>
      </form>
    </div>
  </aside>

  <footer class="site-footer">
    <div>
      <strong><?= h($settings['footer_title'] ?? $brandName) ?></strong>
      <span><?= h($settings['footer_description'] ?? 'Website resmi penerbit, toko buku, dan layanan produksi buku.') ?></span>
    </div>
    <a href="https://wa.me/<?= h($waNumber) ?>" target="_blank" rel="noreferrer"><?= h($settings['footer_link_text'] ?? 'Hubungi WhatsApp') ?></a>
  </footer>

  <script src="script.js?v=20260627-14"></script>
</body>
</html>
