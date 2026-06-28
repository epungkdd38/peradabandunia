<?php
require __DIR__ . '/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    require_admin();

    $orders = db()->query('SELECT * FROM orders ORDER BY created_at DESC')->fetchAll();
    $stmt = db()->prepare('SELECT * FROM order_items WHERE order_id = ? ORDER BY id ASC');

    foreach ($orders as &$order) {
        $stmt->execute([$order['id']]);
        $order['items'] = array_map(function ($item) {
            return [
                'id' => $item['book_id'],
                'title' => $item['title'],
                'price' => (int)$item['price'],
                'priceLabel' => $item['price_label'] ?? '',
                'qty' => (int)$item['qty'],
            ];
        }, $stmt->fetchAll());
        $order['name'] = $order['customer_name'];
        $order['phone'] = $order['customer_phone'];
        $order['address'] = $order['customer_address'];
        $order['total'] = (int)$order['total'];
    }

    respond(['ok' => true, 'orders' => $orders]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = input();
    $items = $data['items'] ?? [];

    if (!is_array($items) || count($items) === 0) {
        respond(['ok' => false, 'message' => 'Keranjang masih kosong.'], 422);
    }

    $name = text_value($data['name'] ?? '', 160);
    $phone = text_value($data['phone'] ?? '', 40);
    $address = text_value($data['address'] ?? '', 1200);
    $notes = text_value($data['notes'] ?? '', 1200);

    if ($name === '' || $phone === '' || $address === '') {
        respond(['ok' => false, 'message' => 'Nama, WhatsApp, dan alamat wajib diisi.'], 422);
    }

    $pdo = db();
    $pdo->beginTransaction();

    try {
        $orderId = 'PPD-' . date('ymdHis') . random_int(10, 99);
        $total = 0;
        $resolved = [];

        $bookStmt = $pdo->prepare('SELECT * FROM books WHERE id = ? AND is_active = 1 LIMIT 1');
        foreach ($items as $item) {
            $qty = max(1, (int)($item['qty'] ?? 1));
            $qty = min($qty, 99);
            $bookStmt->execute([(string)($item['id'] ?? '')]);
            $book = $bookStmt->fetch();
            if (!$book) {
                continue;
            }

            $subtotal = ((int)$book['price']) * $qty;
            $total += $subtotal;
            $resolved[] = [
                'id' => $book['id'],
                'title' => $book['title'],
                'price' => (int)$book['price'],
                'priceLabel' => $book['price_label'] ?? '',
                'qty' => $qty,
                'subtotal' => $subtotal,
            ];
        }

        if (!$resolved) {
            throw new RuntimeException('Tidak ada buku valid di keranjang.');
        }

        $orderStmt = $pdo->prepare(
            'INSERT INTO orders (id, customer_name, customer_phone, customer_address, notes, total, status)
             VALUES (?, ?, ?, ?, ?, ?, "Baru")'
        );
        $orderStmt->execute([
            $orderId,
            $name,
            $phone,
            $address,
            $notes,
            $total,
        ]);

        $itemStmt = $pdo->prepare(
            'INSERT INTO order_items (order_id, book_id, title, price, price_label, qty, subtotal)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        foreach ($resolved as $item) {
            $itemStmt->execute([
                $orderId,
                $item['id'],
                $item['title'],
                $item['price'],
                $item['priceLabel'],
                $item['qty'],
                $item['subtotal'],
            ]);
        }

        $pdo->commit();
        respond([
            'ok' => true,
            'order' => [
                'id' => $orderId,
                'name' => $name,
                'phone' => $phone,
                'address' => $address,
                'notes' => $notes,
                'items' => $resolved,
                'total' => $total,
                'status' => 'Baru',
            ],
        ]);
    } catch (Throwable $e) {
        $pdo->rollBack();
        respond(['ok' => false, 'message' => 'Order belum bisa diproses. Periksa keranjang dan data pemesan.'], 422);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    require_admin_action();

    $data = input();
    $id = (string)($data['id'] ?? '');
    $status = (string)($data['status'] ?? '');
    $allowed = ['Baru', 'Diproses', 'Dikirim', 'Selesai'];

    if ($id === '' || !in_array($status, $allowed, true)) {
        respond(['ok' => false, 'message' => 'Data status tidak valid.'], 422);
    }

    $stmt = db()->prepare('UPDATE orders SET status = ? WHERE id = ?');
    $stmt->execute([$status, $id]);

    respond(['ok' => true]);
}

respond(['ok' => false, 'message' => 'Method tidak didukung.'], 405);
