<?php
require_once 'config/db.php';
require_once 'config/auth.php';
requireLogin();
$pageTitle = 'Profil Saya';

$userId = currentUserId();
$stmt = mysqli_prepare($koneksi, "SELECT * FROM users WHERE id = ?");
mysqli_stmt_bind_param($stmt, 'i', $userId);
mysqli_stmt_execute($stmt);
$user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

$orderStmt = mysqli_prepare($koneksi, "SELECT * FROM pesanan WHERE user_id = ? ORDER BY tanggal_pesan DESC");
mysqli_stmt_bind_param($orderStmt, 'i', $userId);
mysqli_stmt_execute($orderStmt);
$orders = mysqli_stmt_get_result($orderStmt);

$statusClass = ['Menunggu'=>'status-menunggu','Dikemas'=>'status-dikemas','Selesai'=>'status-selesai','Dibatalkan'=>'status-dibatalkan'];

require 'includes/header.php';
?>

<h1 class="section-title">Profil Saya</h1>
<div class="checkout-box" style="max-width:500px;">
    <h3>Data Diri</h3>
    <p><strong><?php echo e($user['nama']); ?></strong></p>
    <p style="color:#666; margin-top:4px;"><?php echo e($user['email']); ?></p>
    <p style="color:#666; margin-top:4px;"><?php echo e($user['alamat'] ?: 'Belum ada alamat'); ?></p>
    <p style="color:#666; margin-top:4px;"><?php echo e($user['no_telp'] ?: '-'); ?></p>
</div>

<h2 class="section-title" style="margin-top:36px;">Riwayat Pesanan</h2>
<?php if (mysqli_num_rows($orders) === 0): ?>
    <p>Belum ada pesanan.</p>
<?php else: ?>
<table class="data-table" style="width:100%;">
    <thead><tr><th>Order Id</th><th>Tanggal</th><th>Total</th><th>Status</th><th>Struk</th></tr></thead>
    <tbody>
    <?php while ($o = mysqli_fetch_assoc($orders)): ?>
        <tr>
            <td>#<?php echo e($o['order_id']); ?></td>
            <td><?php echo date('d M Y', strtotime($o['tanggal_pesan'])); ?></td>
            <td><?php echo formatRupiah($o['total']); ?></td>
            <td><span class="status-pill <?php echo $statusClass[$o['status']] ?? ''; ?>"><?php echo e($o['status']); ?></span></td>
            <td>
                <?php if (in_array($o['status'], ['Dikemas', 'Selesai'], true)): ?>
                    <a href="struk.php?id=<?php echo e($o['order_id']); ?>" class="btn-primary" style="display:inline-block; padding:6px 14px; font-size:0.85rem; text-decoration:none;">Unduh Struk</a>
                <?php elseif ($o['status'] === 'Dibatalkan'): ?>
                    <small style="color:#6b7280;">-</small>
                <?php else: ?>
                    <small style="color:#6b7280;">Menunggu konfirmasi admin</small>
                <?php endif; ?>
            </td>
        </tr>
    <?php endwhile; ?>
    </tbody>
</table>
<?php endif; ?>

<?php require 'includes/footer.php'; ?>