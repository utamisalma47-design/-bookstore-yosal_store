<?php
require_once 'config/db.php';
require_once 'config/auth.php';
$pageTitle = 'Wishlist';

// Toggle wishlist (tambah / hapus)
if (isset($_GET['toggle']) && isLoggedIn() && $_SESSION['role'] === 'user') {
    $bukuId = (int)$_GET['toggle'];
    $userId = currentUserId();
    $check = mysqli_prepare($koneksi, "SELECT id FROM wishlist WHERE user_id = ? AND buku_id = ?");
    mysqli_stmt_bind_param($check, 'ii', $userId, $bukuId);
    mysqli_stmt_execute($check);
    $exists = mysqli_fetch_assoc(mysqli_stmt_get_result($check));

    if ($exists) {
        $del = mysqli_prepare($koneksi, "DELETE FROM wishlist WHERE id = ?");
        mysqli_stmt_bind_param($del, 'i', $exists['id']);
        mysqli_stmt_execute($del);
    } else {
        $ins = mysqli_prepare($koneksi, "INSERT INTO wishlist (user_id, buku_id) VALUES (?, ?)");
        mysqli_stmt_bind_param($ins, 'ii', $userId, $bukuId);
        mysqli_stmt_execute($ins);
    }
    header('Location: wishlist.php');
    exit;
}

requireLogin();
$userId = currentUserId();
$stmt = mysqli_prepare($koneksi, "SELECT b.* FROM wishlist w JOIN buku b ON w.buku_id = b.id WHERE w.user_id = ? ORDER BY w.id DESC");
mysqli_stmt_bind_param($stmt, 'i', $userId);
mysqli_stmt_execute($stmt);
$items = mysqli_stmt_get_result($stmt);

require 'includes/header.php';
?>

<h1 class="section-title">Wishlist</h1>

<?php if (mysqli_num_rows($items) === 0): ?>
    <p>Belum ada buku di wishlist kamu.</p>
<?php else: ?>
<div class="book-list-grid">
    <?php while ($b = mysqli_fetch_assoc($items)): ?>
    <div class="book-list-card">
        <a href="produk.php?id=<?php echo $b['id']; ?>">
            <img class="cover" src="assets/img/<?php echo e($b['gambar']); ?>" alt="<?php echo e($b['judul']); ?>" onerror="this.src='https://placehold.co/300x400?text=Buku'">
        </a>
        <div class="info">
            <h4><?php echo e($b['judul']); ?></h4>
            <div class="author"><?php echo e($b['penulis']); ?></div>
            <div class="price"><?php echo formatRupiah($b['harga']); ?></div>
            <a href="wishlist.php?toggle=<?php echo $b['id']; ?>" style="color:#c0392b; font-size:13px; font-weight:600;">Hapus dari wishlist</a>
        </div>
    </div>
    <?php endwhile; ?>
</div>
<?php endif; ?>

<?php require 'includes/footer.php'; ?>
