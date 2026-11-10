<?php
require_once 'config/db.php';
require_once 'config/auth.php';
$pageTitle = 'Beranda';
$activePage = 'beranda';

// Buku unggulan untuk hero (bestseller pertama, fallback ke buku pertama)
$heroQ = mysqli_query($koneksi, "SELECT * FROM buku WHERE bestseller = 1 LIMIT 1");
$heroBook = mysqli_fetch_assoc($heroQ);
if (!$heroBook) {
    $heroQ = mysqli_query($koneksi, "SELECT * FROM buku LIMIT 1");
    $heroBook = mysqli_fetch_assoc($heroQ);
}

// Buku rekomendasi (5 buku terbaru)
$rekomQ = mysqli_query($koneksi, "SELECT * FROM buku ORDER BY created_at DESC LIMIT 5");

require 'includes/header.php';
?>

<section class="hero" style="background-image:url('https://images.unsplash.com/photo-1521587760476-6c12a4b040da?w=1400');">
    <div class="hero-content">
        <h1>Temukan Buku Terbaik<br>Untuk Setiap Momen</h1>
        <p>Ribuan Buku Berkualitas Dari Berbagai Genre.<br>Untuk Menemani Setiap Langkah Anda.</p>
        <a href="kategori.php" class="btn-primary">Belanja Sekarang</a>
        <div class="hero-stats">
            <div class="stat"><strong>10k+</strong>Pelanggan Aktif</div>
            <div class="stat"><strong>4,9&#9733;</strong>Rating Pelanggan</div>
            <div class="stat"><strong>99%</strong>Kepuasan Pelanggan</div>
        </div>
    </div>

    <?php if ($heroBook): ?>
    <div class="hero-product-card">
        <div class="badge-row">
            <span class="badge">Bestseller</span>
            <?php if ($heroBook['harga_coret']): ?>
            <span class="discount"><?php echo round((1 - $heroBook['harga']/$heroBook['harga_coret'])*100); ?>%</span>
            <?php endif; ?>
        </div>
        <img src="assets/img/<?php echo e($heroBook['gambar']); ?>" alt="<?php echo e($heroBook['judul']); ?>" onerror="this.src='https://placehold.co/200x260?text=Buku'">
        <div style="font-weight:700; font-size:13px; margin-top:8px;"><?php echo e($heroBook['judul']); ?></div>
        <div style="font-size:12px; color:#777;"><?php echo e($heroBook['penulis']); ?></div>
        <div>
            <span class="price-now"><?php echo formatRupiah($heroBook['harga']); ?></span>
            <?php if ($heroBook['harga_coret']): ?><span class="price-old"><?php echo formatRupiah($heroBook['harga_coret']); ?></span><?php endif; ?>
        </div>
        <a href="produk.php?id=<?php echo $heroBook['id']; ?>" class="btn-primary">Lihat Detail</a>
    </div>
    <?php endif; ?>
</section>

<h2 class="section-title">Kategori Buku</h2>
<div class="cat-grid">
    <a href="kategori.php?slug=fiksi" class="cat-card cat-fiksi">
        <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="#1a1a2e" stroke-width="1.6"><path d="M4 4h9a3 3 0 0 1 3 3v13a3 3 0 0 0-3-3H4V4z"/><path d="M20 4h-4v13h4V4z"/></svg>
        Fiksi
    </a>
    <a href="kategori.php?slug=non-fiksi" class="cat-card cat-nonfiksi">
        <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="#1a1a2e" stroke-width="1.6"><path d="M6 2h9l5 5v15H6V2z"/><path d="M14 2v5h5"/><path d="M9 13h6M9 17h6"/></svg>
        Non-Fiksi
    </a>
    <a href="kategori.php?slug=komik" class="cat-card cat-komik">
        <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="#1a1a2e" stroke-width="1.6"><path d="M13 2l2 5 5 .7-3.6 3.5.9 5-4.3-2.3L8.7 16l.9-5L6 7.7 11 7z"/></svg>
        Komik
    </a>
    <a href="kategori.php?slug=anak" class="cat-card cat-anak">
        <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="#1a1a2e" stroke-width="1.6"><circle cx="8" cy="7" r="2.4"/><circle cx="16" cy="7" r="2.4"/><path d="M4 20c0-3 2-5 4-5M20 20c0-3-2-5-4-5"/></svg>
        Anak
    </a>
</div>

<div class="section-head">
    <h2 class="section-title" style="margin-bottom:0;">Di Sarankan</h2>
    <a href="kategori.php" class="see-all">Lihat Semua</a>
</div>
<div class="book-grid">
    <?php while ($b = mysqli_fetch_assoc($rekomQ)): ?>
    <a href="produk.php?id=<?php echo $b['id']; ?>" class="book-card">
        <img class="cover" src="assets/img/<?php echo e($b['gambar']); ?>" alt="<?php echo e($b['judul']); ?>" onerror="this.src='https://placehold.co/220x300?text=Buku'">
        <h4><?php echo e($b['judul']); ?></h4>
        <div class="author"><?php echo e($b['penulis']); ?></div>
        <div class="price"><?php echo formatRupiah($b['harga']); ?></div>
    </a>
    <?php endwhile; ?>
</div>

<?php require 'includes/footer.php'; ?>
