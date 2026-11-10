-- =====================================================
-- Yosal Store - Database Schema & Seed Data
-- Import file ini melalui phpMyAdmin atau:
-- mysql -u root -p < yosal_store.sql
-- =====================================================

CREATE DATABASE IF NOT EXISTS yosal_store CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE yosal_store;

-- =========================
-- Tabel Users (Admin & User digabung, dibedakan lewat kolom role)
-- =========================
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','user') NOT NULL DEFAULT 'user',
    alamat VARCHAR(255) DEFAULT NULL,
    no_telp VARCHAR(30) DEFAULT NULL,
    status ENUM('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =========================
-- Tabel Kategori Buku
-- =========================
CREATE TABLE kategori (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_kategori VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE
);

-- =========================
-- Tabel Buku
-- =========================
CREATE TABLE buku (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kategori_id INT NOT NULL,
    judul VARCHAR(150) NOT NULL,
    penulis VARCHAR(100) NOT NULL,
    deskripsi TEXT,
    harga DECIMAL(12,2) NOT NULL DEFAULT 0,
    harga_coret DECIMAL(12,2) DEFAULT NULL,
    stock INT NOT NULL DEFAULT 0,
    gambar VARCHAR(255) DEFAULT NULL,
    bestseller TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (kategori_id) REFERENCES kategori(id) ON DELETE CASCADE
);

-- =========================
-- Tabel Keranjang (Cart)
-- =========================
CREATE TABLE cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    buku_id INT NOT NULL,
    jumlah INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (buku_id) REFERENCES buku(id) ON DELETE CASCADE
);

-- =========================
-- Tabel Wishlist
-- =========================
CREATE TABLE wishlist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    buku_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (buku_id) REFERENCES buku(id) ON DELETE CASCADE
);

-- =========================
-- Tabel Pesanan (Orders)
-- =========================
CREATE TABLE pesanan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id VARCHAR(20) NOT NULL UNIQUE,
    user_id INT NOT NULL,
    nama_penerima VARCHAR(100) NOT NULL,
    alamat_pengiriman VARCHAR(255) NOT NULL,
    no_telp VARCHAR(30) NOT NULL,
    metode_pengiriman VARCHAR(50) NOT NULL,
    ongkos_kirim DECIMAL(12,2) NOT NULL DEFAULT 0,
    metode_pembayaran VARCHAR(50) NOT NULL,
    total DECIMAL(12,2) NOT NULL,
    status ENUM('Menunggu','Dikemas','Selesai','Dibatalkan') NOT NULL DEFAULT 'Menunggu',
    tanggal_pesan TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- =========================
-- Tabel Detail Pesanan
-- =========================
CREATE TABLE pesanan_detail (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pesanan_id INT NOT NULL,
    buku_id INT NOT NULL,
    judul_buku VARCHAR(150) NOT NULL,
    harga DECIMAL(12,2) NOT NULL,
    jumlah INT NOT NULL,
    subtotal DECIMAL(12,2) NOT NULL,
    FOREIGN KEY (pesanan_id) REFERENCES pesanan(id) ON DELETE CASCADE,
    FOREIGN KEY (buku_id) REFERENCES buku(id) ON DELETE CASCADE
);

-- =========================
-- Tabel Pesan Masuk (Contact Us)
-- =========================
CREATE TABLE pesan_masuk (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    subjek VARCHAR(150) NOT NULL,
    pesan TEXT NOT NULL,
    status ENUM('Belum Dibaca','Sudah Dibaca') NOT NULL DEFAULT 'Belum Dibaca',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


-- Kategori
INSERT INTO kategori (nama_kategori, slug) VALUES
('Fiksi', 'fiksi'),
('Non-Fiksi', 'non-fiksi'),
('Komik', 'komik'),
('Anak', 'anak');

-- Buku - Fiksi (kategori_id = 1)
INSERT INTO buku (kategori_id, judul, penulis, deskripsi, harga, harga_coret, stock, gambar, bestseller) VALUES
(1, 'Si Anak Pintar', 'Tere Liye', 'Novel karya Tere Liye yang mengisahkan perjalanan hidup penuh makna.', 99000, NULL, 40, 'si-anak-pintar.jpg', 0),
(1, 'Negeri Di Ujung Tanduk', 'Tere Liye', 'Kelanjutan kisah Thomas dalam dunia politik dan hukum yang penuh intrik.', 80000, NULL, 35, 'negeri-di-ujung-tanduk.jpg', 0),
(1, 'Rumah Untuk Alie', 'Lenn Liu', 'Kisah hangat tentang rumah, keluarga, dan kepulangan.', 70000, NULL, 25, 'rumah-untuk-alie.jpg', 0),
(1, 'Eccedentesiast', 'Itakrn', 'Novel tentang seseorang yang menyembunyikan luka di balik senyuman.', 80000, NULL, 20, 'eccedentesiast.jpg', 0),
(1, '01.00', 'Ameylia Falensia', 'Kisah tentang penantian dan pengertian yang datang terlambat.', 80000, NULL, 18, '01-00.jpg', 0),
(1, 'Lentera Senja', 'Alfian Ramadhani', 'Kisah cinta yang tenang seperti senja yang perlahan meredup.', 85000, NULL, 22, 'lentera-senja.jpg', 0);

-- Buku - Non-Fiksi (kategori_id = 2)
INSERT INTO buku (kategori_id, judul, penulis, deskripsi, harga, harga_coret, stock, gambar, bestseller) VALUES
(2, 'Atomic Habits', 'James Clear', 'Buku pengembangan diri terlaris yang mengubah kebiasaan kecil menjadi hasil luar biasa.', 70000, 100000, 50, 'atomic-habits.jpg', 1),
(2, 'Filosofi Teras', 'Henry Manampiring', 'Buku pengembangan diri terlaris yang memperkenalkan filsafat Stoisisme dengan bahasa santai dan relevan untuk generasi milenial serta Gen-Z.', 80000, NULL, 30, 'filosofi-teras.jpg', 0),
(2, 'Habibie & Ainun', 'Bacharuddin Jusuf Habibie', 'Buku biografi romantis dan memoar yang ditulis langsung oleh Presiden ke-3 Republik Indonesia untuk mengenang mendiang istrinya, Hasri Ainun Habibie.', 99000, NULL, 15, 'habibie-ainun.jpg', 0),
(2, 'A Brief History Of Time', 'Stephen Hawking', 'Buku sains populer legendaris yang menjelaskan konsep-konsep rumit kosmologi dan fisika modern dengan bahasa yang mudah dipahami.', 70000, NULL, 20, 'a-brief-history-of-time.jpg', 0),
(2, 'Rendra Karya Dan Dunianya', 'Dr. Bakdi Soemanto', 'Buku biografi dan kajian sastra mendalam yang membedah latar belakang kehidupan dan seluruh karya sastrawan besar Indonesia, W.S. Rendra.', 73000, NULL, 12, 'rendra-karya-dan-dunianya.jpg', 0),
(2, 'KIhajar Dewantara', 'Saiful Hermawan', 'Kisah pahlawan nasional dan Bapak Pendidikan Indonesia lewat esai-esai terkenalnya yang mengkritik kolonialisme.', 70000, NULL, 18, 'ki-hajar-dewantara.jpg', 0),
(2, 'Healing With Rumi', 'Wawan Kurniawan', 'Buku spiritual berisi 45 renungan luhur bersumber dari karya-karya penyair sufistik legendaris, Maulana Jalaluddin Rumi.', 70000, NULL, 16, 'healing-with-rumi.jpg', 0);

-- Buku - Komik (kategori_id = 3)
INSERT INTO buku (kategori_id, judul, penulis, deskripsi, harga, harga_coret, stock, gambar, bestseller) VALUES
(3, 'Omen', 'lexie xu', 'Komik bergenre thriller misteri yang penuh ketegangan.', 80000, NULL, 20, 'omen.jpg', 0),
(3, 'Bubble Pop', 'Muffin Graphics', 'Komik sekolah dengan cerita ceria dan penuh warna.', 60000, NULL, 25, 'bubble-pop.jpg', 0),
(3, 'Detektif Conan', 'Aoya Gosho', 'Serial komik detektif legendaris yang penuh teka-teki.', 60000, NULL, 45, 'detektif-conan.jpg', 0),
(3, 'Attack On Titan', 'Hajime Isayama', 'Komik aksi epik tentang perjuangan manusia melawan raksasa Titan.', 60000, NULL, 38, 'attack-on-titan.jpg', 0),
(3, 'Connet', 'Manutsuki Suzuki', 'Komik drama sekolah dengan alur cerita yang menyentuh.', 70000, NULL, 15, 'connet.jpg', 0);

-- Buku - Anak (kategori_id = 4)
INSERT INTO buku (kategori_id, judul, penulis, deskripsi, harga, harga_coret, stock, gambar, bestseller) VALUES
(4, 'Kisah Menakjubkan 25 Nabi & Rasul', 'watiek ideo', 'Kumpulan kisah menakjubkan 25 Nabi dan Rasul untuk anak-anak.', 80000, NULL, 30, 'kisah-25-nabi-rasul.jpg', 0),
(4, 'Dinosaurus', 'Hasbro', 'Buku aktivitas seru tentang dunia dinosaurus untuk anak-anak.', 35000, NULL, 40, 'dinosaurus.jpg', 0),
(4, 'Seberapa Jauh Ku Melangkah', 'Disney', 'Kisah petualangan Moana yang penuh keberanian.', 35000, NULL, 35, 'moana.jpg', 0),
(4, 'Pangeran Cilik', 'Antoine The Saint-Exupery', 'Buku klasik Le Petit Prince yang sarat makna kehidupan.', 75000, NULL, 20, 'pangeran-cilik.jpg', 0),
(4, 'Rapunzel Putri Kerajaan', 'Disney', 'Buku aktivitas seru bersama Putri Rapunzel.', 30000, NULL, 28, 'rapunzel.jpg', 0),
(4, 'Mimpi Adalah Harapan Kata Hatimu', 'Disney', 'Kisah Cinderella yang penuh mimpi dan harapan.', 35000, NULL, 25, 'cinderella.jpg', 0);


