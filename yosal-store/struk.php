<?php
require_once 'config/db.php';
require_once 'config/auth.php';
requireLogin();
$pageTitle = 'Struk Pembelian';
$activePage = 'kategori';

$userId = currentUserId();

// Ambil kode pesanan dari URL (?id=YSL123...) atau dari session
$orderCode = trim($_GET['id'] ?? ($_SESSION['last_order_id'] ?? ''));

$order = null;
if ($orderCode !== '') {
    $stmt = mysqli_prepare($koneksi, "SELECT * FROM pesanan WHERE order_id = ? AND user_id = ?");
    mysqli_stmt_bind_param($stmt, 'si', $orderCode, $userId);
    mysqli_stmt_execute($stmt);
    $order = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
}

if (!$order) {
    header('Location: profil.php');
    exit;
}

// Struk hanya bisa dilihat/diunduh setelah admin mengonfirmasi pesanan
$sudahDikonfirmasi = in_array($order['status'], ['Dikemas', 'Selesai'], true);

$dtl = mysqli_prepare($koneksi, "SELECT * FROM pesanan_detail WHERE pesanan_id = ?");
mysqli_stmt_bind_param($dtl, 'i', $order['id']);
mysqli_stmt_execute($dtl);
$items = mysqli_stmt_get_result($dtl);

$subtotalItems = 0;
$rows = [];
while ($r = mysqli_fetch_assoc($items)) {
    $subtotalItems += $r['subtotal'];
    $rows[] = $r;
}

// Nama kolom tanggal bisa berbeda-beda, cek yang tersedia
$tanggal = $order['created_at'] ?? $order['tanggal_pesan'] ?? $order['tanggal'] ?? null;
$tanggalTeks = $tanggal ? date('d-m-Y H:i', strtotime($tanggal)) : date('d-m-Y H:i');

require 'includes/header.php';

if (!$sudahDikonfirmasi):
?>
<div style="max-width:560px; margin:30px auto 60px; text-align:center; background:#fff; border:1px solid #dbe0ff; border-radius:12px; padding:36px 28px;">
    <h2 style="color:#1E3A9E; margin-top:0;">Struk Belum Tersedia</h2>
    <p style="color:#374151; line-height:1.6;">
        <?php if ($order['status'] === 'Dibatalkan'): ?>
            Pesanan ini sudah dibatalkan, sehingga tidak ada struk.
        <?php else: ?>
            Struk dapat dilihat dan diunduh setelah pembayaran dikonfirmasi oleh admin.
        <?php endif; ?>
    </p>
    <p style="color:#6b7280; font-size:0.9rem;">No. Pesanan: #<?php echo e($order['order_id']); ?> &middot; Status: <?php echo e($order['status']); ?></p>
    <a href="profil.php" class="btn-primary" style="display:inline-block; padding:12px 24px; margin-top:8px; text-decoration:none;">Kembali ke Profil</a>
</div>
<?php
require 'includes/footer.php';
exit;
endif;
?>

<style>
.struk-wrap { max-width: 560px; margin: 10px auto 40px; }
.struk-box { background:#fff; border:1px solid #dbe0ff; border-radius:12px; padding:28px; }
.struk-head { text-align:center; border-bottom:2px dashed #cbd5e1; padding-bottom:16px; margin-bottom:16px; }
.struk-head h2 { margin:0; color:#1E3A9E; }
.struk-head small { color:#6b7280; }
.struk-info { display:grid; grid-template-columns:140px 1fr; gap:4px 10px; font-size:0.95rem; margin-bottom:16px; }
.struk-info .k { color:#6b7280; }
.struk-table { width:100%; border-collapse:collapse; font-size:0.95rem; }
.struk-table th { text-align:left; border-bottom:1px solid #cbd5e1; padding:6px 0; color:#1E3A9E; }
.struk-table td { padding:6px 0; vertical-align:top; }
.struk-table .r { text-align:right; }
.struk-sum { border-top:2px dashed #cbd5e1; margin-top:12px; padding-top:12px; font-size:0.95rem; }
.struk-sum .row { display:flex; justify-content:space-between; padding:2px 0; }
.struk-sum .grand { font-weight:800; font-size:1.1rem; color:#1E3A9E; border-top:1px solid #cbd5e1; margin-top:6px; padding-top:8px; }
.struk-foot { text-align:center; color:#6b7280; font-size:0.9rem; margin-top:18px; }
.struk-actions { display:flex; gap:10px; margin-top:16px; }
.struk-actions a, .struk-actions button { flex:1; text-align:center; padding:12px; border-radius:8px; border:1px solid #1E3A9E; background:#fff; color:#1E3A9E; font-weight:700; cursor:pointer; text-decoration:none; font-size:1rem; }
.struk-actions .primary { background:#1E3A9E; color:#fff; }
@media print {
    header, nav, footer, .site-footer, .no-print { display:none !important; }
    .struk-box { border:none; }
}
</style>

<div class="struk-wrap">
    <div class="struk-box">
        <div class="struk-head">
            <h2>Yosal Store</h2>
            <small>Struk Pembelian</small>
        </div>

        <div class="struk-info">
            <span class="k">No. Pesanan</span><span><b>#<?php echo e($order['order_id']); ?></b></span>
            <span class="k">Tanggal</span><span><?php echo e($tanggalTeks); ?></span>
            <span class="k">Penerima</span><span><?php echo e($order['nama_penerima']); ?></span>
            <span class="k">Alamat</span><span><?php echo e($order['alamat_pengiriman']); ?></span>
            <span class="k">No. Telepon</span><span><?php echo e($order['no_telp']); ?></span>
            <span class="k">Pengiriman</span><span><?php echo e($order['metode_pengiriman']); ?></span>
            <span class="k">Pembayaran</span>
            <span>
                <?php echo e($order['metode_pembayaran']); ?>
                <?php if (!empty($order['bank_tujuan'])): ?>
                    (<?php echo e($order['bank_tujuan']); ?>)
                <?php endif; ?>
            </span>
            <span class="k">Status</span><span><?php echo e($order['status']); ?></span>
        </div>

        <table class="struk-table">
            <tr><th>Buku</th><th class="r">Qty</th><th class="r">Harga</th><th class="r">Subtotal</th></tr>
            <?php foreach ($rows as $r): ?>
            <tr>
                <td><?php echo e($r['judul_buku']); ?></td>
                <td class="r"><?php echo (int)$r['jumlah']; ?></td>
                <td class="r"><?php echo formatRupiah($r['harga']); ?></td>
                <td class="r"><?php echo formatRupiah($r['subtotal']); ?></td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($rows)): ?>
            <tr><td colspan="4" style="color:#991b1b;">Detail buku untuk pesanan ini tidak ditemukan.</td></tr>
            <?php endif; ?>
        </table>

        <div class="struk-sum">
            <div class="row"><span>Total Harga (<?php echo count($rows); ?> item)</span><span><?php echo formatRupiah($subtotalItems); ?></span></div>
            <div class="row"><span>Ongkos Kirim</span><span><?php echo formatRupiah($order['ongkos_kirim']); ?></span></div>
            <div class="row grand"><span>Total Pembayaran</span><span><?php echo formatRupiah($order['total']); ?></span></div>
        </div>

        <div class="struk-foot">Terima kasih sudah berbelanja di Yosal Store!</div>
    </div>

    <div class="struk-actions no-print">
        <button type="button" onclick="window.print()">Unduh Struk (PDF)</button>
        <a href="profile.php" class="primary">Kembali ke Profil</a>
    </div>
</div>

<?php require 'includes/footer.php'; ?>