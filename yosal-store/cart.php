<?php
require_once 'config/db.php';
require_once 'config/auth.php';
requireLogin();
$pageTitle = 'Keranjang';
$activePage = 'kategori';

$userId = currentUserId();
$stmt = mysqli_prepare($koneksi, "SELECT c.id as cart_id, c.jumlah, b.* FROM cart c JOIN buku b ON c.buku_id = b.id WHERE c.user_id = ? ORDER BY c.id DESC");
mysqli_stmt_bind_param($stmt, 'i', $userId);
mysqli_stmt_execute($stmt);
$items = mysqli_stmt_get_result($stmt);

$total = 0;
$rows = [];
while ($row = mysqli_fetch_assoc($items)) {
    $row['subtotal'] = $row['harga'] * $row['jumlah'];
    $total += $row['subtotal'];
    $rows[] = $row;
}

require 'includes/header.php';
?>

<h1 class="section-title">Keranjang</h1>

<?php if (empty($rows)): ?>
    <p>Keranjang kamu masih kosong. <a href="kategori.php" style="color:var(--btn-blue); font-weight:600;">Yuk cari buku</a></p>
<?php else: ?>
<table class="cart-table">
    <thead>
        <tr>
            <th>Produk</th>
            <th>Harga</th>
            <th>Jumlah</th>
            <th>Subtotal</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($rows as $r): ?>
        <tr>
            <td>
                <div class="cart-product">
                    <img src="assets/img/<?php echo e($r['gambar']); ?>" alt="<?php echo e($r['judul']); ?>" onerror="this.src='https://placehold.co/70x95?text=Buku'">
                    <div>
                        <h4><?php echo e($r['judul']); ?></h4>
                        <div class="author"><?php echo e($r['penulis']); ?></div>
                    </div>
                </div>
            </td>
            <td><?php echo formatRupiah($r['harga']); ?></td>
            <td>
                <div class="qty-box" style="width:100px;">
                    <button type="button" class="qty-minus">-</button>
                    <input type="number" min="1" max="<?php echo (int)$r['stock']; ?>" value="<?php echo (int)$r['jumlah']; ?>" onchange="updateCartQty(<?php echo $r['cart_id']; ?>, this)">
                    <button type="button" class="qty-plus">+</button>
                </div>
            </td>
            <td id="subtotal-<?php echo $r['cart_id']; ?>"><?php echo formatRupiah($r['subtotal']); ?></td>
            <td><button class="trash-btn" onclick="removeCartItem(<?php echo $r['cart_id']; ?>)">&#128465;</button></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<div class="summary-box">
    <h3>Ringkasan Belanja</h3>
    <div class="summary-row"><span>Total</span><span id="cart-total"><?php echo formatRupiah($total); ?></span></div>
    <div class="summary-row"><span>Ongkos Kirim</span><span>Rp.0</span></div>
    <div class="summary-row total"><span>Total Pembayaran</span><span id="cart-grand-total"><?php echo formatRupiah($total); ?></span></div>
    <a href="checkout.php" class="btn-primary" style="display:block; text-align:center; width:100%;">Checkout</a>
</div>
<?php endif; ?>

<?php require 'includes/footer.php'; ?>
