<?php
require __DIR__ . '/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    ensure_book_description_columns();
    $stmt = db()->query('SELECT * FROM books WHERE is_active = 1 ORDER BY created_at DESC, title ASC');
    $books = array_map('normalize_book', $stmt->fetchAll());
    respond(['ok' => true, 'books' => $books]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_admin_action();
    ensure_book_description_columns();

    $isMultipart = strpos($_SERVER['CONTENT_TYPE'] ?? '', 'multipart/form-data') !== false;
    $data = $isMultipart ? $_POST : input();
    $title = text_value($data['title'] ?? '', 255);
    $author = text_value($data['author'] ?? '', 180);

    if ($title === '' || $author === '') {
        respond(['ok' => false, 'message' => 'Judul dan penulis wajib diisi.'], 422);
    }

    $rawId = text_value($data['id'] ?? '', 120);
    $id = $rawId !== '' ? slugify($rawId) : slugify((string)($data['isbn'] ?? $title));
    $coverImage = public_upload_path((string)($data['existingCoverImage'] ?? $data['coverImage'] ?? ''));
    if ($isMultipart && isset($_FILES['coverImage']) && $_FILES['coverImage']['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($_FILES['coverImage']['error'] !== UPLOAD_ERR_OK) {
            respond(['ok' => false, 'message' => 'Upload cover gagal.'], 422);
        }

        $allowed = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
        ];
        $mime = mime_content_type($_FILES['coverImage']['tmp_name']);
        if (!isset($allowed[$mime])) {
            respond(['ok' => false, 'message' => 'Format cover harus JPG, PNG, atau WEBP.'], 422);
        }
        if ((int)$_FILES['coverImage']['size'] > 2 * 1024 * 1024) {
            respond(['ok' => false, 'message' => 'Ukuran cover maksimal 2 MB.'], 422);
        }

        $uploadDir = dirname(__DIR__) . '/uploads/covers';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $filename = $id . '-' . bin2hex(random_bytes(8)) . '.' . $allowed[$mime];
        $target = $uploadDir . '/' . $filename;
        if (!move_uploaded_file($_FILES['coverImage']['tmp_name'], $target)) {
            respond(['ok' => false, 'message' => 'Cover gagal disimpan.'], 500);
        }
        $coverImage = 'uploads/covers/' . $filename;
    }

    $stmt = db()->prepare(
        'INSERT INTO books (id, title, author, isbn, price, price_label, stock, source, category, short_description, description, cover_image, is_active)
         VALUES (:id, :title, :author, :isbn, :price, :price_label, :stock, :source, :category, :short_description, :description, :cover_image, 1)
         ON DUPLICATE KEY UPDATE
          title = VALUES(title),
          author = VALUES(author),
          isbn = VALUES(isbn),
          price = VALUES(price),
          price_label = VALUES(price_label),
          stock = VALUES(stock),
          source = VALUES(source),
          category = VALUES(category),
          short_description = VALUES(short_description),
          description = VALUES(description),
          cover_image = VALUES(cover_image),
          is_active = 1'
    );

    $stmt->execute([
        ':id' => $id,
        ':title' => $title,
        ':author' => $author,
        ':isbn' => text_value($data['isbn'] ?? '', 120),
        ':price' => max(0, (int)($data['price'] ?? 0)),
        ':price_label' => text_value($data['priceLabel'] ?? '', 80),
        ':stock' => min(999999, max(0, (int)($data['stock'] ?? 0))),
        ':source' => enum_value($data['source'] ?? 'Terbitan PPD', ['Terbitan PPD', 'Distributor', 'Titipan'], 'Terbitan PPD'),
        ':category' => text_value($data['category'] ?? 'Agama', 80),
        ':short_description' => text_value($data['shortDescription'] ?? $data['short_description'] ?? '', 500),
        ':description' => text_value($data['description'] ?? '', 10000),
        ':cover_image' => $coverImage,
    ]);

    respond(['ok' => true, 'message' => 'Buku berhasil disimpan.']);
}

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    require_admin_action();

    $data = input();
    $id = slugify((string)($data['id'] ?? ''));
    if ($id === '') {
        respond(['ok' => false, 'message' => 'ID buku tidak valid.'], 422);
    }

    $stmt = db()->prepare('UPDATE books SET is_active = 0 WHERE id = ?');
    $stmt->execute([$id]);

    respond(['ok' => true, 'message' => 'Buku berhasil dihapus.']);
}

respond(['ok' => false, 'message' => 'Method tidak didukung.'], 405);
