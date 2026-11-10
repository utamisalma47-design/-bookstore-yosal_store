
<?php
require_once 'config/db.php';
require_once 'config/auth.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = mysqli_prepare($koneksi, "SELECT b.*, k.nama_kategori, k.slug FROM buku b JOIN kategori k ON b.kategori_id = k.id WHERE b.id = ?");
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$buku = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$buku) {
    header('Location: kategori.php');
    exit;
}

$pageTitle = $buku['judul'];
$activePage = 'kategori';

// Cek status wishlist
$isWishlisted = false;

if (isLoggedIn()) {
    $userId = currentUserId();

    $wishStmt = mysqli_prepare(
        $koneksi,
        "SELECT id FROM wishlist WHERE user_id = ? AND buku_id = ?"
    );

    mysqli_stmt_bind_param($wishStmt, 'ii', $userId, $id);
    mysqli_stmt_execute($wishStmt);

    $isWishlisted = mysqli_num_rows(
        mysqli_stmt_get_result($wishStmt)
    ) > 0;
}

// Buku terkait (kategori sama)
$relStmt = mysqli_prepare($koneksi, "SELECT * FROM buku WHERE kategori_id = ? AND id != ? LIMIT 4");
mysqli_stmt_bind_param($relStmt, 'ii', $buku['kategori_id'], $id);
mysqli_stmt_execute($relStmt);
$related = mysqli_stmt_get_result($relStmt);

require 'includes/header.php';
?>

<div class="cat-header">
    <a href="kategori.php?slug=<?php echo e($buku['slug']); ?>" style="font-size:20px;">&lsaquo;</a>
    <?php echo e($buku['nama_kategori']); ?>
</div>

<form class="product-row" action="add_to_cart.php" method="post">

    <img class="cover"
         src="assets/img/<?php echo e($buku['gambar']); ?>"
         alt="<?php echo e($buku['judul']); ?>"
         onerror="this.src='https://placehold.co/300x420?text=Buku'">

    <div class="info">

        <h4><?php echo e($buku['judul']); ?></h4>

        <div class="author">
            <?php echo e($buku['penulis']); ?>
        </div>

        <div class="price" style="margin-bottom:10px;">
            <?php echo formatRupiah($buku['harga']); ?>
        </div>

        <p class="desc">
            <?php echo nl2br(e($buku['deskripsi'])); ?>
        </p>

        <p style="margin-top:10px; font-size:13px; color:#777;">
            Stok tersedia: <?php echo (int)$buku['stock']; ?>
        </p>

        <!-- TOMBOL WISHLIST -->
        <?php if (isLoggedIn()): ?>

        <button
            type="button"
            id="wishlistButton"
            data-buku-id="<?php echo (int)$buku['id']; ?>"
            data-active="<?php echo $isWishlisted ? '1' : '0'; ?>"
            aria-label="Wishlist"
            aria-pressed="<?php echo $isWishlisted ? 'true' : 'false'; ?>"
            title="<?php echo $isWishlisted ? 'Hapus dari Wishlist' : 'Tambah ke Wishlist'; ?>"
            style="
                display: inline-flex;
                align-items: center;
                justify-content: center;
                margin-top: 5px;
                padding: 0;
                border: none;
                background: transparent;
                color: <?php echo $isWishlisted ? '#e53965' : '#555'; ?>;
                font-size: 29px;
                cursor: pointer;
                line-height: 1;
            "
        ><?php echo $isWishlisted ? '♥' : '♡'; ?></button>

        <?php endif; ?>

    </div>

    <div class="side">

        <div class="qty-box">
            <button type="button" class="qty-minus">-</button>

            <input type="number"
                   name="jumlah"
                   value="1"
                   min="1"
                   max="<?php echo (int)$buku['stock']; ?>"
                   required>

            <button type="button" class="qty-plus">+</button>
        </div>

        <input type="hidden"
               name="buku_id"
               value="<?php echo $buku['id']; ?>">

        <?php if ($buku['stock'] > 0): ?>

            <button type="submit"
                    class="btn-cart"
                    formaction="add_to_cart.php">
                Tambah Keranjang
            </button>

            <button type="submit"
                    class="btn-buy"
                    formaction="buy_now.php"
                    style="
                        display: block;
                        width: 100%;
                        padding: 12px 16px;
                        margin-top: 10px;
                        background: #173aab;
                        color: #ffffff;
                        border: none;
                        border-radius: 9px;
                        font-size: 16px;
                        font-weight: 700;
                        font-family: Arial, sans-serif;
                        cursor: pointer;
                        text-align: center;
                        box-sizing: border-box;
                    ">
                Beli Sekarang
            </button>

        <?php else: ?>

            <button type="button" class="btn-cart" disabled>
                Stok Habis
            </button>

        <?php endif; ?>

    </div>

</form>

<?php if (mysqli_num_rows($related) > 0): ?>

    <h2 class="section-title" style="margin-top:40px;">
        Buku Terkait
    </h2>

    <div class="book-grid">

        <?php while ($r = mysqli_fetch_assoc($related)): ?>

            <a href="produk.php?id=<?php echo $r['id']; ?>"
               class="book-card">

                <img class="cover"
                     src="assets/img/<?php echo e($r['gambar']); ?>"
                     alt="<?php echo e($r['judul']); ?>"
                     onerror="this.src='https://placehold.co/220x300?text=Buku'">

                <h4><?php echo e($r['judul']); ?></h4>

                <div class="author">
                    <?php echo e($r['penulis']); ?>
                </div>

                <div class="price">
                    <?php echo formatRupiah($r['harga']); ?>
                </div>

            </a>

        <?php endwhile; ?>

    </div>

<?php endif; ?>


<!-- SCRIPT WISHLIST -->
<script>
document.addEventListener('DOMContentLoaded', function () {

    const button = document.getElementById('wishlistButton');

    if (!button) return;

    button.addEventListener('click', function () {

        const formData = new FormData();

        formData.append('buku_id', button.dataset.bukuId);

        fetch('wishlist_toggle.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {

            if (data.success) {

                button.dataset.active = data.active ? '1' : '0';

                button.textContent = data.active ? '♥' : '♡';

                button.style.color = data.active ? '#e53965' : '#555';

                button.setAttribute(
                    'aria-pressed',
                    data.active ? 'true' : 'false'
                );

                button.title = data.active
                    ? 'Hapus dari Wishlist'
                    : 'Tambah ke Wishlist';

            } else {
                alert('Wishlist gagal diperbarui.');
            }

        })
        .catch(error => {
            console.error(error);
            alert('Terjadi kesalahan. Silakan coba lagi.');
        });

    });

});
</script>

<?php require 'includes/footer.php'; ?>
