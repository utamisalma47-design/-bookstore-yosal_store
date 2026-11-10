# Yosal Store - Website Toko Buku (HTML, CSS, JS, PHP, MySQL)

Website toko buku dengan 2 role (Admin & User) dalam satu tabel `users`,
sesuai studi kasus BookStore.

## Fitur

### User
- Registrasi & Login
- Lihat Beranda, Kategori Buku (Fiksi, Non-Fiksi, Komik, Anak)
- Cari buku
- Lihat detail buku
- Tambah ke keranjang (Add to Cart), ubah jumlah, hapus item
- Checkout: pilih alamat, metode pengiriman, metode pembayaran (COD / Transfer Bank)
- Halaman "Pembayaran Berhasil"
- Wishlist
- Lihat About Us & kirim pesan ke Admin (Contact)
- Lihat riwayat pesanan (Profil)

### Admin
- Dashboard ringkasan (total buku, kategori, user, pesanan)
- CRUD Kategori Buku
- CRUD Data Buku (termasuk stock)
- Lihat daftar User & ubah status aktif/nonaktif
- Lihat & kelola status Pesanan (Menunggu / Dikemas / Selesai / Dibatalkan)
- Lihat Pesan Masuk dari user (Contact Us)
- Laporan penjualan sederhana (buku terlaris, total pendapatan)
- Pengaturan akun admin (ubah profil & password)

## Instalasi (XAMPP / Laragon / sejenisnya)

1. Copy folder `yosal-store` ke folder `htdocs` (XAMPP) atau `www` (Laragon).
2. Buat database lewat phpMyAdmin, lalu import file `database/yosal_store.sql`.
   (Query pertama di file sudah otomatis membuat database `yosal_store`,
   jadi kamu tinggal klik Import saja tanpa perlu bikin database manual.)
3. Cek file `config/db.php`, sesuaikan `DB_USER` / `DB_PASS` dengan MySQL kamu
   (default XAMPP: user `root`, password kosong — biasanya sudah cocok).
4. Buka browser ke:
   `http://localhost/yosal-store/setup_akun.php`
   Ini akan membuat akun Admin & 5 akun User contoh dengan password
   yang benar-benar ter-enkripsi (bcrypt), lalu **hapus file ini** setelah
   selesai dijalankan (demi keamanan).
5. Login:
   - Admin: `admin@yosalstore.com` / `admin123`
   - User contoh: `nadiayaya2@gmail.com` / `user123` (dan 4 user lain, lihat
     `setup_akun.php` untuk daftar emailnya)
   - Atau daftar akun user baru sendiri lewat halaman Register.

## Gambar Cover Buku

Data buku di database mereferensikan nama file gambar (contoh: `atomic-habits.jpg`)
yang harus kamu taruh di folder `assets/img/`. Jika file belum ada, cover akan
otomatis diganti gambar placeholder supaya halaman tidak rusak. Silakan
tambahkan gambar cover asli sesuai nama file yang tertulis di kolom `gambar`
pada tabel `buku` (bisa diedit juga lewat menu Admin > Data Buku).

## Struktur Folder

```
yosal-store/
├── admin/                  Halaman & logika khusus admin
│   ├── includes/sidebar.php
│   ├── dashboard.php
│   ├── kategori.php        CRUD kategori
│   ├── data_buku.php       CRUD buku
│   ├── users.php
│   ├── pesanan.php
│   ├── laporan.php
│   ├── pesan_masuk.php
│   └── pengaturan.php
├── assets/
│   ├── css/style.css
│   ├── js/main.js
│   └── img/                Taruh cover buku di sini
├── config/
│   ├── db.php               Koneksi database
│   └── auth.php             Helper session & auth
├── database/
│   └── yosal_store.sql      Struktur tabel + data kategori/buku
├── includes/
│   ├── header.php
│   └── footer.php
├── index.php                 Beranda
├── kategori.php               Listing kategori/buku
├── produk.php                  Detail buku
├── cart.php / update_cart.php / remove_cart.php / add_to_cart.php
├── checkout.php / process_checkout.php / update_alamat.php
├── payment_success.php
├── login.php / register.php / logout.php
├── about.php / contact.php / search.php / wishlist.php / profile.php
└── setup_akun.php             Jalankan sekali, lalu hapus
```

## Catatan Keamanan

- Password disimpan ter-hash menggunakan `password_hash()` (bcrypt), bukan plain text.
- Semua query database menggunakan prepared statement (mysqli) untuk mencegah SQL Injection.
- Semua output ke HTML sudah melalui `htmlspecialchars()` (fungsi `e()`) untuk mencegah XSS.
- Halaman admin dilindungi `requireAdmin()`, halaman yang butuh login dilindungi `requireLogin()`.
- Jangan lupa hapus `setup_akun.php` setelah dipakai di server produksi.
