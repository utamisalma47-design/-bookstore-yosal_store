<?php
require_once 'config/db.php';
require_once 'config/auth.php';
$activePage = 'kategori';

$slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';

if ($slug !== '') {
    $stmt = mysqli_prepare($koneksi, "SELECT * FROM kategori WHERE slug = ?");
    mysqli_stmt_bind_param($stmt, 's', $slug);
    mysqli_stmt_execute($stmt);
    $kategori = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    if (!$kategori) {
        header('Location: kategori.php');
        exit;
    }
    $pageTitle = $kategori['nama_kategori'];

    $stmt2 = mysqli_prepare($koneksi, "SELECT * FROM buku WHERE kategori_id = ? ORDER BY judul ASC");
    mysqli_stmt_bind_param($stmt2, 'i', $kategori['id']);
    mysqli_stmt_execute($stmt2);
    $bukuList = mysqli_stmt_get_result($stmt2);
} else {
    $pageTitle = 'Kategori';
    $semuaKategori = mysqli_query($koneksi, "SELECT * FROM kategori ORDER BY id ASC");
}

require 'includes/header.php';
?>

<?php if ($slug !== ''): ?>
    <div class="cat-header"><a href="kategori.php" style="font-size:20px;">&lsaquo;</a> <?php echo e($kategori['nama_kategori']); ?></div>

    <?php if (mysqli_num_rows($bukuList) === 0): ?>
        <p>Belum ada buku pada kategori ini.</p>
    <?php else: ?>
    <div class="book-list-grid">
        <?php while ($b = mysqli_fetch_assoc($bukuList)): ?>
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

<?php else: ?>
    <h2 class="section-title">Kategori Buku</h2>
    <div class="cat-grid">
        <?php
        $catClass = ['fiksi'=>'cat-fiksi','non-fiksi'=>'cat-nonfiksi','komik'=>'cat-komik','anak'=>'cat-anak'];
        while ($k = mysqli_fetch_assoc($semuaKategori)):
        ?>
        <a href="kategori.php?slug=<?php echo e($k['slug']); ?>" class="cat-card <?php echo $catClass[$k['slug']] ?? 'cat-fiksi'; ?>">
            <?php echo e($k['nama_kategori']); ?>
        </a>
        <?php endwhile; ?>
    </div>
<?php endif; ?>

<?php require 'includes/footer.php'; ?>
