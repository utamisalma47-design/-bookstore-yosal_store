<?php
require_once 'config/db.php';
require_once 'config/auth.php';
$pageTitle = 'About Us';
$activePage = 'about';

$totalBuku = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) c FROM buku"))['c'];
$totalUser = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) c FROM users WHERE role='user'"))['c'];

require 'includes/header.php';
?>

<div class="about-grid">
    <div>
        <h1>About Us</h1>
        <h2>Yosal Store<br>Teman Terbaik Pecinta Buku</h2>
        <p>Yosal Store datang untuk menyediakan buku-buku berkualitas dengan harga terjangkau. Kami percaya buku adalah jendela dunia dan kunci untuk masa depan yang lebih baik.</p>
    </div>
    <img src="https://images.unsplash.com/photo-1524995997946-a1c2e315a42f?w=900" alt="Rak Buku">
</div>

<div class="about-lower">
    <div>
        <h4>Misi kami</h4>
        <p>Membantu masyarakat indonesia mendapatkan akses buku berkualitas dengan mudah dan menyenangkan.</p>
        <h4>Visi kami</h4>
        <p>Menjadi toko buku online terbesar dan terpercaya di indonesia.</p>
    </div>
    <div class="stat-box">
        <div><strong>10k+</strong><span>Pelanggan aktif</span></div>
        <div><strong><?php echo $totalBuku; ?></strong><span>Judul Buku</span></div>
        <div><strong>500k+</strong><span>Penulis Terpercaya</span></div>
        <div><strong>4,9/5</strong><span>Rating Pelanggan</span></div>
    </div>
</div>

<?php require 'includes/footer.php'; ?>
