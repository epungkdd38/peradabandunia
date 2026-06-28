<?php
require __DIR__ . '/bootstrap.php';

function load_settings(): array
{
    $rows = db()->query('SELECT setting_key, setting_value FROM site_settings')->fetchAll();
    $settings = [];
    foreach ($rows as $row) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
    return $settings;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    respond(['ok' => true, 'settings' => load_settings()]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_admin_action();

    $data = input();
    $settings = $data['settings'] ?? [];
    if (!is_array($settings)) {
        respond(['ok' => false, 'message' => 'Data setting tidak valid.'], 422);
    }

    $allowed = [
        'brand_name', 'brand_tagline', 'brand_mark', 'meta_description',
        'hero_eyebrow', 'hero_title', 'hero_copy', 'hero_panel_text', 'hero_panel_strong',
        'whatsapp_number', 'footer_title', 'footer_description', 'footer_link_text',
        'color_ink', 'color_paper', 'color_panel', 'color_green', 'color_red', 'color_gold', 'color_cream',
    ];

    $stmt = db()->prepare(
        'INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?)
         ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)'
    );

    foreach ($settings as $key => $value) {
        if (!in_array($key, $allowed, true)) {
            continue;
        }
        $clean = text_value($value, 1200);
        if ($key === 'whatsapp_number') {
            $clean = wa_number($value, '628993998544');
        }
        if (strpos($key, 'color_') === 0) {
            $clean = hex_color($value, '#111111');
        }
        $stmt->execute([$key, $clean]);
    }

    respond(['ok' => true, 'settings' => load_settings()]);
}

respond(['ok' => false, 'message' => 'Method tidak didukung.'], 405);
