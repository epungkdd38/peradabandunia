# Pilar Peradaban Dunia

Website resmi penerbit, toko buku, layanan editor/layout/penerbitan/pencetakan, dan panel admin.

## Struktur

- `index.html` - halaman utama.
- `styles.css` - tampilan website.
- `script.js` - interaksi frontend dan koneksi API.
- `api/` - endpoint PHP untuk MySQL.
- `database/schema.sql` - struktur tabel dan data awal.
- `assets/` - gambar website.
- `uploads/covers/` - folder penyimpanan cover buku.
- `robots.txt` - aturan crawler mesin pencari.
- `sitemap.xml` - sitemap untuk submit ke search engine.
- `llms.txt` - ringkasan website untuk crawler AI.

## Cara publish di shared hosting

1. Buat database MySQL dari panel hosting.
2. Import `database/schema.sql` lewat phpMyAdmin.
3. Edit `api/config.php`:
   - `db_host`
   - `db_name`
   - `db_user`
   - `db_pass`
   - `wa_number`
4. Upload semua file ke `public_html` atau folder domain.
5. Pastikan folder `uploads/covers/` bisa ditulis oleh PHP.
6. Buka domain website.

Jika database sudah pernah dibuat sebelum fitur cover, jalankan SQL ini:

```sql
ALTER TABLE books ADD COLUMN IF NOT EXISTS cover_image VARCHAR(255) DEFAULT '';
```

Halaman publik:
- `index.html`

Portal admin:
- `admin.html`

## Login admin awal

- Username: `admin`
- Password: `Hint@2138`

Setelah live, simpan password admin dengan aman dan jangan dibagikan. Kolom `password_hash` memakai SHA-256 dari password.

## SEO dan indexing

- Pastikan domain final sama dengan canonical di `index.html`, `robots.txt`, `sitemap.xml`, dan `llms.txt`.
- Submit `https://domain-anda/sitemap.php` ke Google Search Console dan Bing Webmaster Tools agar halaman detail buku ikut terbaca.
- Jangan submit `admin.html` atau folder `api/`; keduanya sudah diset noindex/disallow.
- Untuk AI crawler, `llms.txt` berisi ringkasan publik website dan area yang tidak boleh diringkas.

## Endpoint API

- `api/books.php`
  - `GET` daftar buku publik
  - `POST` tambah/update buku, wajib login admin
- `api/orders.php`
  - `POST` buat order pelanggan
  - `GET` daftar order, wajib login admin
  - `PUT` update status order, wajib login admin
- `api/login.php`
  - `POST` login admin
- `api/logout.php`
  - `POST` logout admin
- `api/session.php`
  - `GET` cek session admin
- `api/settings.php`
  - `GET` identitas website, warna, WhatsApp, dan footer
  - `POST` update settings, wajib login admin
- `api/pages.php`
  - `GET` page/menu tambahan publik
  - `POST` tambah/update page dan menu, wajib login admin
  - `DELETE` nonaktifkan page, wajib login admin

## Fitur portal admin

- Mengelola identitas web: nama brand, tagline, hero, meta description, dan nomor WhatsApp.
- Mengelola warna halaman dari input color.
- Mengelola konten buku: tambah, lihat, edit, hapus, dan upload cover buku.
- Mengelola page/menu: tambah, lihat, edit, dan hapus page tambahan.
- Mengubah keterangan footer.
- Tracking order dan export data.
