CREATE TABLE IF NOT EXISTS admins (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(80) NOT NULL UNIQUE,
  password_hash CHAR(64) NOT NULL,
  name VARCHAR(120) NOT NULL DEFAULT 'Admin',
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS books (
  id VARCHAR(120) PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  author VARCHAR(180) NOT NULL,
  isbn VARCHAR(120) DEFAULT NULL,
  price INT UNSIGNED NOT NULL DEFAULT 0,
  price_label VARCHAR(80) DEFAULT '',
  stock INT UNSIGNED NOT NULL DEFAULT 0,
  source ENUM('Terbitan PPD','Distributor','Titipan') NOT NULL DEFAULT 'Terbitan PPD',
  category VARCHAR(80) NOT NULL DEFAULT 'Agama',
  short_description TEXT,
  description TEXT,
  cover_image VARCHAR(255) DEFAULT '',
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS orders (
  id VARCHAR(40) PRIMARY KEY,
  customer_name VARCHAR(160) NOT NULL,
  customer_phone VARCHAR(40) NOT NULL,
  customer_address TEXT NOT NULL,
  notes TEXT,
  total INT UNSIGNED NOT NULL DEFAULT 0,
  status ENUM('Baru','Diproses','Dikirim','Selesai') NOT NULL DEFAULT 'Baru',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS order_items (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  order_id VARCHAR(40) NOT NULL,
  book_id VARCHAR(120) NOT NULL,
  title VARCHAR(255) NOT NULL,
  price INT UNSIGNED NOT NULL DEFAULT 0,
  price_label VARCHAR(80) DEFAULT '',
  qty INT UNSIGNED NOT NULL DEFAULT 1,
  subtotal INT UNSIGNED NOT NULL DEFAULT 0,
  CONSTRAINT fk_order_items_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS site_settings (
  setting_key VARCHAR(80) PRIMARY KEY,
  setting_value TEXT,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS site_pages (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  menu_label VARCHAR(120) NOT NULL,
  slug VARCHAR(140) NOT NULL UNIQUE,
  title VARCHAR(255) NOT NULL,
  content TEXT NOT NULL,
  menu_order INT UNSIGNED NOT NULL DEFAULT 99,
  is_menu TINYINT(1) NOT NULL DEFAULT 1,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO admins (username, password_hash, name)
VALUES ('admin', '2e92e1a3640cc38b73093d1c56867d181e83b475dde46b5bd8413dfadb8cd4c0', 'Admin PPD')
ON DUPLICATE KEY UPDATE password_hash = VALUES(password_hash), name = VALUES(name);

INSERT INTO site_settings (setting_key, setting_value) VALUES
('brand_name', 'Pilar Peradaban Dunia'),
('brand_tagline', 'Penerbit & Book Services'),
('brand_mark', 'PPD'),
('meta_description', 'Website resmi Pilar Peradaban Dunia: penerbit, toko buku online, jasa editor buku, layout buku, penerbitan, dan pencetakan buku.'),
('hero_eyebrow', 'Official publisher store'),
('hero_title', 'Buku, penerbitan, dan produksi naskah dalam satu rumah kerja.'),
('hero_copy', 'Pilar Peradaban Dunia melayani pembelian buku, penerbitan naskah, editing, layout, dan pencetakan buku untuk penulis, komunitas, lembaga, dan pesantren.'),
('hero_panel_text', 'Mulai dari naskah mentah sampai buku siap edar'),
('hero_panel_strong', 'Editing - Layout - Terbit - Cetak'),
('whatsapp_number', '628993998544'),
('footer_title', 'Pilar Peradaban Dunia'),
('footer_description', 'Website resmi penerbit, toko buku, dan layanan produksi buku.'),
('footer_link_text', 'Hubungi WhatsApp'),
('color_ink', '#111111'),
('color_paper', '#f4eee4'),
('color_panel', '#fffaf0'),
('color_green', '#171717'),
('color_red', '#9f1717'),
('color_gold', '#c89b3c'),
('color_cream', '#f8f0df')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);

INSERT INTO books (id, title, author, isbn, price, price_label, stock, source, category, description, cover_image) VALUES
('ppd-menjawab-akidah-kaum-sufi', 'Menjawab Akidah Kaum Sufi', 'Abuya Syaikh Muhammad bin Alawy Al Maliky', '978-602-5607-45-7', 54600, '', 998, 'Terbitan PPD', 'Agama', 'Buku terjemahan 236 halaman berukuran 14,8 x 21 cm yang membahas dan menjawab persoalan akidah dalam tradisi kaum sufi.', ''),
('ppd-terjemah-miftahul-ulum', 'Terjemah Kitab Miftahul ''Ulum Fi Ta''rifil ''Ulum', 'As-Syekh Ahmad bin Zakaria Al-Anshari As-Syafi''i', 'PPD-MU-001', 0, 'Hubungi Kami', 18, 'Terbitan PPD', 'Agama', 'Terjemah kitab karya ulama besar mazhab Syafi''i yang memuat pembahasan tentang ragam ilmu dan pengenalannya.', ''),
('ppd-manual-kursus-aswaja', 'Manual Kursus Aswaja', 'Tim Penyusun Pilar Peradaban Dunia', 'PPD-ASW-001', 0, 'Hubungi Kami', 12, 'Terbitan PPD', 'Agama', 'Bahan ajar ringkas untuk kursus Aswaja, cocok sebagai pegangan belajar komunitas dan majelis.', ''),
('ppd-durarul-faraid', 'Terjemah Kitab Durarul Faraid', 'Habib Zen bin Smith', 'PPD-DF-001', 0, 'Hubungi Kami', 9, 'Terbitan PPD', 'Agama', 'Terjemah karya Habib Zen bin Smith yang berisi pembahasan keilmuan Islam dalam format kitab.', ''),
('ppd-obat-hati', 'Obat Hati', 'KH. Ahmad Zainuddin As-Sumbawi', 'PPD-OH-001', 125000, '', 9, 'Terbitan PPD', 'Agama', 'Buku keislaman tentang penyucian jiwa dan penguatan hati melalui nasihat ulama.', ''),
('ppd-mirqatul-wushul', 'Terjemah Kitab Mirqatul Wushul', 'Al-Imam As-Sayyid Muhammad bin Alawy Al-Maliki', 'PPD-MW-001', 0, 'Hubungi Kami', 15, 'Terbitan PPD', 'Agama', 'Terjemah kitab karya Al-Imam As-Sayyid Muhammad bin Alawy Al-Maliki untuk rujukan kajian keislaman.', ''),
('ppd-mutiara-ilmu-akidah', 'Terjemah Kitab Mutiara Ilmu Akidah', 'Abu Bakar Al-Jazairi', 'PPD-MIA-001', 0, 'Hubungi Kami', 7, 'Terbitan PPD', 'Agama', 'Terjemah kitab akidah karya Abu Bakar Al-Jazairi sebagai bahan belajar dasar-dasar keyakinan Islam.', ''),
('ppd-tanwirul-qulub', 'Terjemah Kitab Tanwirul Qulub', 'Syaikh Muhammad Amin Al-Kurdi', 'PPD-TQ-001', 0, 'Hubungi Kami', 11, 'Terbitan PPD', 'Agama', 'Terjemah kitab Tanwirul Qulub karya Syaikh Muhammad Amin Al-Kurdi untuk pembaca kajian tasawuf dan fikih.', ''),
('ppd-selayang-pandang-habib-umar', 'Selayang Pandang Habib Umar Bin Hafidz', 'Habib Hamid bin Umar bin Hafidz', 'PPD-SPH-001', 0, 'Hubungi Kami', 6, 'Terbitan PPD', 'Biografi', 'Buku pengenalan tentang Habib Umar bin Hafidz, disusun oleh Habib Hamid bin Umar bin Hafidz.', ''),
('ppd-habib-umar-keramat', 'Habib Umar Bin Hafidz Keramat Wali Zaman Ini', 'Habib Hamid bin Umar bin Hafidz', 'PPD-HUK-001', 0, 'Hubungi Kami', 6, 'Terbitan PPD', 'Biografi', 'Buku biografi dan manaqib tentang Habib Umar bin Hafidz sebagai tokoh ulama kontemporer.', '')
ON DUPLICATE KEY UPDATE title = VALUES(title), author = VALUES(author), isbn = VALUES(isbn), price = VALUES(price), price_label = VALUES(price_label), stock = VALUES(stock), source = VALUES(source), category = VALUES(category), description = VALUES(description), cover_image = IF(books.cover_image = '', VALUES(cover_image), books.cover_image);
