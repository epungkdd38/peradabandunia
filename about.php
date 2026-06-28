<?php
$config = require __DIR__ . '/api/config.php';

function h($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function pdo(array $config): PDO
{
    $dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', $config['db_host'], $config['db_name']);
    return new PDO($dsn, $config['db_user'], $config['db_pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
}

$settings = [];
try {
    $db = pdo($config);
    foreach ($db->query('SELECT setting_key, setting_value FROM site_settings') as $row) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
} catch (Throwable $error) {
    $settings = [];
}

$brandName = $settings['brand_name'] ?? 'Pilar Peradaban Dunia';
$brandTagline = $settings['brand_tagline'] ?? 'Penerbit & Book Services';
$waNumber = $settings['whatsapp_number'] ?? ($config['wa_number'] ?? '628993998544');
$baseUrl = 'https://peradabandunia.id/';
$pageUrl = $baseUrl . 'about.php';
$description = 'Profil resmi Pilar Peradaban Dunia, penerbit buku dan mitra produksi naskah untuk layanan editing, layout, penerbitan, pencetakan, serta katalog buku online.';
$schema = [
    '@context' => 'https://schema.org',
    '@graph' => [
        [
            '@type' => ['Organization', 'BookStore'],
            '@id' => $baseUrl . '#organization',
            'name' => $brandName,
            'alternateName' => 'Yayasan Pilar Peradaban Dunia',
            'url' => $baseUrl,
            'logo' => $baseUrl . 'assets/logo.png',
            'description' => $description,
            'contactPoint' => [
                '@type' => 'ContactPoint',
                'contactType' => 'customer service',
                'telephone' => '+' . $waNumber,
                'areaServed' => 'ID',
                'availableLanguage' => ['id'],
            ],
        ],
        [
            '@type' => 'AboutPage',
            '@id' => $pageUrl . '#webpage',
            'url' => $pageUrl,
            'name' => 'Tentang ' . $brandName,
            'isPartOf' => ['@id' => $baseUrl . '#website'],
            'about' => ['@id' => $baseUrl . '#organization'],
            'inLanguage' => 'id-ID',
        ],
    ],
];
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="<?= h($description) ?>">
  <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1">
  <link rel="canonical" href="<?= h($pageUrl) ?>">
  <link rel="icon" href="assets/logo.png" type="image/png">
  <meta property="og:type" content="website">
  <meta property="og:locale" content="id_ID">
  <meta property="og:site_name" content="<?= h($brandName) ?>">
  <meta property="og:title" content="Tentang <?= h($brandName) ?>">
  <meta property="og:description" content="<?= h($description) ?>">
  <meta property="og:url" content="<?= h($pageUrl) ?>">
  <meta property="og:image" content="<?= h($baseUrl) ?>assets/hero-penerbit.jpg">
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="Tentang <?= h($brandName) ?>">
  <meta name="twitter:description" content="<?= h($description) ?>">
  <title>Tentang <?= h($brandName) ?> | Penerbit Buku</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="styles.css?v=20260627-10">
  <script type="application/ld+json"><?= json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?></script>
</head>
<body class="about-page">
  <header class="site-header">
    <a class="brand" href="index.html" aria-label="<?= h($brandName) ?>">
      <img class="brand-logo" src="assets/logo.png" alt="Yayasan Pilar Peradaban Dunia">
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
    <section class="section about-page-hero">
      <div class="about-page-shell">
        <div>
          <p class="eyebrow">Tentang penerbit</p>
          <h1><?= h($brandName) ?></h1>
          <p>Pilar Peradaban Dunia adalah penerbit buku dan mitra produksi naskah yang mendampingi penulis, komunitas, lembaga, dan pesantren menyiapkan karya agar lebih rapi, layak terbit, layak cetak, dan siap menjangkau pembaca.</p>
          <div class="hero-actions">
            <a class="primary-action" href="index.html#layanan">Lihat Layanan</a>
            <a class="secondary-action" href="https://wa.me/<?= h($waNumber) ?>" target="_blank" rel="noreferrer">Hubungi Admin</a>
          </div>
        </div>
        <div class="about-proof">
          <img src="assets/logo.png" alt="Logo Yayasan Pilar Peradaban Dunia">
          <strong>Publisher Profile</strong>
          <span>Halaman resmi untuk profil penerbit, layanan produksi buku, katalog, dan kontak administrasi.</span>
        </div>
      </div>
    </section>

    <section class="section intro-band">
      <div class="section-heading">
        <div>
          <p class="eyebrow">Profil singkat</p>
          <h2>Profil penerbit, publikasi buku, dan toko online dalam satu kanal resmi.</h2>
        </div>
      </div>
      <div class="about-layout">
        <article class="about-copy">
          <p>Website ini menjadi kanal resmi Pilar Peradaban Dunia untuk memperkenalkan identitas penerbit, katalog buku, layanan produksi naskah, dan informasi pemesanan. Katalog memuat buku terbitan Pilar Peradaban Dunia serta judul pilihan dari distributor atau titipan.</p>
          <p>Tim kami melayani editing buku, layout buku, penerbitan buku, dan pencetakan buku. Halaman ini dapat digunakan sebagai rujukan profil penerbit ketika penulis, komunitas, atau lembaga membutuhkan tautan resmi untuk kebutuhan administrasi, proposal, kerja sama, atau persyaratan penerbitan.</p>
        </article>
        <div class="about-points">
          <article><strong>Identitas penerbit</strong><span>Profil, nama brand, layanan, dan kontak resmi tersaji jelas dalam satu halaman.</span></article>
          <article><strong>Publikasi buku</strong><span>Setiap judul dapat ditampilkan sebagai katalog produk dengan detail dan URL tersendiri.</span></article>
          <article><strong>Layanan produksi</strong><span>Editing, layout, penerbitan, dan cetak buku dapat dikonsultasikan langsung dengan tim.</span></article>
        </div>
      </div>
    </section>

    <section class="section services-section">
      <div class="section-heading">
        <div>
          <p class="eyebrow">Ruang kerja</p>
          <h2>Ruang kerja Pilar Peradaban Dunia.</h2>
        </div>
      </div>
      <div class="service-grid">
        <article class="service-card"><span class="service-icon">01</span><h3>Editor Buku</h3><p>Merapikan bahasa, struktur tulisan, konsistensi istilah, dan alur baca naskah.</p></article>
        <article class="service-card"><span class="service-icon">02</span><h3>Layout Buku</h3><p>Menata isi buku agar nyaman dibaca dan siap masuk proses cetak.</p></article>
        <article class="service-card"><span class="service-icon">03</span><h3>Penerbitan Buku</h3><p>Mendampingi proses terbit, identitas buku, dan publikasi katalog resmi.</p></article>
        <article class="service-card"><span class="service-icon">04</span><h3>Pencetakan Buku</h3><p>Memproduksi buku sesuai ukuran, bahan, finishing, dan kebutuhan distribusi.</p></article>
      </div>
    </section>
  </main>

  <footer class="site-footer">
    <div>
      <strong><?= h($brandName) ?></strong>
      <span><?= h($settings['footer_description'] ?? 'Website resmi penerbit, toko buku, dan layanan produksi buku.') ?></span>
    </div>
    <a href="https://wa.me/<?= h($waNumber) ?>" target="_blank" rel="noreferrer"><?= h($settings['footer_link_text'] ?? 'Hubungi WhatsApp') ?></a>
  </footer>
</body>
</html>
