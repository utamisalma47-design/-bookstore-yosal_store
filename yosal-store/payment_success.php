<?php
require_once 'config/db.php';
require_once 'config/auth.php';
requireLogin();
$pageTitle = 'Pembayaran Berhasil';

$orderId = $_SESSION['last_order_id'] ?? null;
unset($_SESSION['last_order_id']);

require 'includes/header.php';
?>

<div class="success-wrap">
    <svg width="90" height="90" viewBox="0 0 24 24" fill="none">
        <circle cx="12" cy="12" r="10" stroke="#2196F3" stroke-width="2"/>
        <path d="M7 12.5l3 3 7-7" stroke="#2196F3" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
    </svg>
    <h2>Pembayaran Berhasil</h2>
    <?php if ($orderId): ?>
        <p style="margin-bottom:12px; color:#555;">Nomor pesanan kamu: <strong><?php echo e($orderId); ?></strong></p>
    <?php endif; ?>
    <p style="margin-bottom:30px; color:#555; max-width:420px; margin-left:auto; margin-right:auto; line-height:1.6;">
        Struk dapat dilihat dan diunduh setelah pembayaran dikonfirmasi oleh admin.
    </p>
    <a href="index.php" class="btn-primary">kembali</a>
</div>

<?php require 'includes/footer.php'; ?>