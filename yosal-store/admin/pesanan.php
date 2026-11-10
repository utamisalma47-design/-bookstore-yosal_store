<?php
require_once '../config/db.php';
require_once '../config/auth.php';
requireAdmin();
$inAdmin = true;
$activeAdmin = 'pesanan';

// Update status pesanan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $id = (int)$_POST['id'];
    $status = $_POST['status'];
    $allowed = ['Menunggu','Dikemas','Selesai','Dibatalkan'];
    if (in_array($status, $allowed)) {
        $stmt = mysqli_prepare($koneksi, "UPDATE pesanan SET status = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, 'si', $status, $id);
        mysqli_stmt_execute($stmt);
    }
    header('Location: pesanan.php');
    exit;
}

$search = trim($_GET['q'] ?? '');
if ($search !== '') {
    $like = '%' . $search . '%';
    $stmt = mysqli_prepare($koneksi, "SELECT * FROM pesanan WHERE nama_penerima LIKE ? OR order_id LIKE ? ORDER BY tanggal_pesan DESC");
    mysqli_stmt_bind_param($stmt, 'ss', $like, $like);
    mysqli_stmt_execute($stmt);
    $list = mysqli_stmt_get_result($stmt);
} else {
    $list = mysqli_query($koneksi, "SELECT * FROM pesanan ORDER BY tanggal_pesan DESC");
}

$statusClass = ['Menunggu'=>'status-menunggu','Dikemas'=>'status-dikemas','Selesai'=>'status-selesai','Dibatalkan'=>'status-dibatalkan'];

// Ambil detail untuk view modal
$rows = [];
while ($p = mysqli_fetch_assoc($list)) {
    $detStmt = mysqli_prepare($koneksi, "SELECT * FROM pesanan_detail WHERE pesanan_id = ?");
    mysqli_stmt_bind_param($detStmt, 'i', $p['id']);
    mysqli_stmt_execute($detStmt);
    $p['items'] = mysqli_fetch_all(mysqli_stmt_get_result($detStmt), MYSQLI_ASSOC);
    $rows[] = $p;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Pesanan - Yosal Store Admin</title>
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="admin-body">
<div class="admin-wrap">
    <?php require 'includes/sidebar.php'; ?>
    <div class="admin-main">
        <div class="page-toolbar">
            <h2>Pesanan</h2>
            <form class="search-admin" method="get">
                <input type="text" name="q" placeholder="Cari Users / Order Id" value="<?php echo e($search); ?>">
                <button type="submit">&#128269;</button>
            </form>
        </div>

        <div class="admin-table-wrap">
            <table class="data-table">
                <thead><tr><th>No</th><th>Order Id</th><th>Tanggal Pesan</th><th>Nama</th><th>Total</th><th>Status</th><th>Aksi</th></tr></thead>
                <tbody>
                <?php $no = 1; foreach ($rows as $p): ?>
                    <tr>
                        <td><?php echo $no++; ?></td>
                        <td>#<?php echo e($p['order_id']); ?></td>
                        <td><?php echo date('d M Y', strtotime($p['tanggal_pesan'])); ?></td>
                        <td><?php echo e($p['nama_penerima']); ?></td>
                        <td><?php echo formatRupiah($p['total']); ?></td>
                        <td><span class="status-pill <?php echo $statusClass[$p['status']] ?? ''; ?>"><?php echo e($p['status']); ?></span></td>
                        <td><button class="icon-action" onclick="openDetail(<?php echo htmlspecialchars(json_encode($p), ENT_QUOTES, 'UTF-8'); ?>)">&#128065;</button></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Detail Pesanan -->
<div class="modal-overlay" id="modalDetail">
    <div class="modal-box" style="max-width:500px; max-height:90vh; overflow-y:auto;">
        <h3>Detail Pesanan</h3>
        <div id="detail-content" style="font-size:14px; margin-bottom:16px;"></div>
        <form method="post" action="pesanan.php">
            <input type="hidden" name="id" id="detail-id">
            <input type="hidden" name="update_status" value="1">
            <div class="form-group">
                <label>Ubah Status</label>
                <select name="status" id="detail-status" style="width:100%; padding:12px; border-radius:8px; border:1px solid #ccc;">
                    <option value="Menunggu">Menunggu</option>
                    <option value="Dikemas">Dikemas</option>
                    <option value="Selesai">Selesai</option>
                    <option value="Dibatalkan">Dibatalkan</option>
                </select>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-secondary" onclick="closeModal('modalDetail')">Tutup</button>
                <button type="submit" class="btn-primary" style="flex:1;">Simpan Status</button>
            </div>
        </form>
    </div>
</div>

<script src="../assets/js/main.js"></script>
<script>
function esc(s) {
    return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
        return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c];
    });
}

function openDetail(data) {
    let pembayaran = esc(data.metode_pembayaran);
    if (data.bank_tujuan) {
        pembayaran += ' (' + esc(data.bank_tujuan) + ')';
    }

    let html = `<p><strong>Order:</strong> #${esc(data.order_id)}</p>
        <p><strong>Penerima:</strong> ${esc(data.nama_penerima)}</p>
        <p><strong>Alamat:</strong> ${esc(data.alamat_pengiriman)}</p>
        <p><strong>No Telp:</strong> ${esc(data.no_telp)}</p>
        <p><strong>Pengiriman:</strong> ${esc(data.metode_pengiriman)}</p>
        <p><strong>Pembayaran:</strong> ${pembayaran}</p>`;

    // ---- Bukti transfer (hanya ada untuk pembayaran Transfer Bank) ----
    if (data.metode_pembayaran === 'Transfer Bank') {
        html += '<p><strong>Bukti Transfer:</strong></p>';
        if (data.bukti_transfer) {
            const url = '../uploads/bukti_transfer/' + encodeURIComponent(data.bukti_transfer);
            if (/\.pdf$/i.test(data.bukti_transfer)) {
                html += `<p><a href="${url}" target="_blank" style="color:#1E3A9E; font-weight:700;">Buka bukti transfer (PDF)</a></p>`;
            } else {
                html += `<a href="${url}" target="_blank" title="Klik untuk memperbesar">
                    <img src="${url}" alt="Bukti transfer" style="max-width:100%; max-height:260px; border:1px solid #dbe0ff; border-radius:8px; display:block;">
                </a>`;
            }
        } else {
            html += '<p style="color:#991b1b;">Belum ada bukti transfer.</p>';
        }
    }

    html += '<hr style="margin:10px 0;">';
    data.items.forEach(function(it){
        html += `<p>${esc(it.judul_buku)} x${esc(it.jumlah)} - Rp.${Number(it.subtotal).toLocaleString('id-ID')}</p>`;
    });
    document.getElementById('detail-content').innerHTML = html;
    document.getElementById('detail-id').value = data.id;
    document.getElementById('detail-status').value = data.status;
    openModal('modalDetail');
}
</script>
</body>
</html>