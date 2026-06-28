<?php
require __DIR__ . '/bootstrap.php';

function normalize_page(array $row): array
{
    return [
        'id' => (int)$row['id'],
        'menuLabel' => $row['menu_label'],
        'slug' => $row['slug'],
        'title' => $row['title'],
        'content' => $row['content'],
        'menuOrder' => (int)$row['menu_order'],
        'isMenu' => (bool)$row['is_menu'],
        'isActive' => (bool)$row['is_active'],
    ];
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = db()->query('SELECT * FROM site_pages WHERE is_active = 1 ORDER BY menu_order ASC, id ASC');
    respond(['ok' => true, 'pages' => array_map('normalize_page', $stmt->fetchAll())]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_admin_action();

    $data = input();
    $title = text_value($data['title'] ?? '', 255);
    $menuLabel = text_value($data['menuLabel'] ?? $title, 120);
    $content = text_value($data['content'] ?? '', 10000);
    $slug = slugify((string)($data['slug'] ?? '') ?: ($menuLabel ?: $title));

    if ($title === '' || $menuLabel === '' || $content === '') {
        respond(['ok' => false, 'message' => 'Label menu, judul, dan konten wajib diisi.'], 422);
    }

    $id = (int)($data['id'] ?? 0);
    if ($id > 0) {
        $stmt = db()->prepare(
            'UPDATE site_pages SET menu_label = ?, slug = ?, title = ?, content = ?, menu_order = ?, is_menu = ?, is_active = ? WHERE id = ?'
        );
        $stmt->execute([
            $menuLabel,
            $slug,
            $title,
            $content,
            max(0, (int)($data['menuOrder'] ?? 99)),
            !empty($data['isMenu']) ? 1 : 0,
            !empty($data['isActive']) ? 1 : 0,
            $id,
        ]);
    } else {
        $stmt = db()->prepare(
            'INSERT INTO site_pages (menu_label, slug, title, content, menu_order, is_menu, is_active)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $menuLabel,
            $slug,
            $title,
            $content,
            max(0, (int)($data['menuOrder'] ?? 99)),
            !empty($data['isMenu']) ? 1 : 0,
            !empty($data['isActive']) ? 1 : 0,
        ]);
    }

    $stmt = db()->query('SELECT * FROM site_pages WHERE is_active = 1 ORDER BY menu_order ASC, id ASC');
    respond(['ok' => true, 'pages' => array_map('normalize_page', $stmt->fetchAll())]);
}

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    require_admin_action();
    $data = input();
    $id = (int)($data['id'] ?? 0);
    if ($id <= 0) {
        respond(['ok' => false, 'message' => 'ID halaman tidak valid.'], 422);
    }

    $stmt = db()->prepare('UPDATE site_pages SET is_active = 0 WHERE id = ?');
    $stmt->execute([$id]);
    respond(['ok' => true]);
}

respond(['ok' => false, 'message' => 'Method tidak didukung.'], 405);
