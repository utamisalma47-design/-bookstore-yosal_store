<?php
require_once 'config/db.php';
require_once 'config/auth.php';
$pageTitle = 'Hasil Pencarian';

$keyword = trim($_GET['q'] ?? '');
$results = null;
if ($keyword !== '') {
    $like = '%' . $keyword . '%';
    $stmt = mysqli_prepare($koneksi, "SELECT * FROM buku WHERE judul LIKE ? OR penulis LIKE ? ORDER BY judul ASC");
    mysqli_stmt_bind_param($stmt, 'ss', $like, $like);
    mysqli_stmt_execute($stmt);
    $results = mysqli_stmt_get_result($stmt);
}

require 'includes/header.php';
?>

<div class="cat-header">Hasil Pencarian: "<?php echo e($keyword); ?>"</div>

<?php if (!$results || mysqli_num_rows($results) === 0): ?>
    <p>Tidak ada buku yang ditemukan.</p>
<?php else: ?>
<div class="book-list-grid">
    <?php while ($b = mysqli_fetch_assoc($results)): ?>
    <a href="produk.php?id=<?php echo $b['id']; ?>" class="book-list-card">
        <img class="cover" src="assets/img/<?php echo e($b['gambar']); ?>" alt="<?php echo e($b['judul']); ?>" onerror="this.src='https://placehold.co/300x400?text=Buku'">
        <div class="info">
            <h4><?php echo e($b['judul']); ?></h4>
            <div class="author"><?php echo e($b['penulis']); ?></div>
            <div class="price"><?php echo formatRupiah($b['harga']); ?></div>
        </div>
    </a>
    <?php endwhile; ?>
</div>
<?php endif; ?>

<?php require 'includes/footer.php'; ?>