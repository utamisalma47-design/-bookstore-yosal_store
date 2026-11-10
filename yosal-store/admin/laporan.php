<?php
require_once '../config/db.php';
require_once '../config/auth.php';
requireAdmin();
$inAdmin = true;
$activeAdmin = 'laporan';

$totalPendapatan = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COALESCE(SUM(total),0) t FROM pesanan WHERE status='Selesai'"))['t'];
$totalPesananSelesai = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) c FROM pesanan WHERE status='Selesai'"))['c'];
$totalPesananProses = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) c FROM pesanan WHERE status IN ('Menunggu','Dikemas')"))['c'];

$terlaris = mysqli_query($koneksi, "
    SELECT b.judul, b.penulis, SUM(pd.jumlah) as total_terjual, SUM(pd.subtotal) as total_pendapatan
    FROM pesanan_detail pd
    JOIN buku b ON pd.buku_id = b.id
    JOIN pesanan p ON pd.pesanan_id = p.id
    WHERE p.status = 'Selesai'
    GROUP BY pd.buku_id
    ORDER BY total_terjual DESC
    LIMIT 10
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Laporan - Yosal Store Admin</title>
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="admin-body">
<div class="admin-wrap">
    <?php require 'includes/sidebar.php'; ?>
    <div class="admin-main">
        <div class="page-toolbar"><h2>Laporan Penjualan</h2></div>

        <div class="admin-stats">
            <div class="admin-stat-card"><strong><?php echo formatRupiah($totalPendapatan); ?></strong><span>Total Pendapatan (Selesai)</span></div>
            <div class="admin-stat-card"><strong><?php echo $totalPesananSelesai; ?></strong><span>Pesanan Selesai</span></div>
            <div class="admin-stat-card"><strong><?php echo $totalPesananProses; ?></strong><span>Pesanan Diproses</span></div>
        </div>

        <div class="admin-table-wrap">
            <div class="admin-table-head">Buku Terlaris</div>
            <table class="data-table">
                <thead><tr><th>Judul</th><th>Penulis</th><th>Terjual</th><th>Pendapatan</th></tr></thead>
                <tbody>
                <?php if (mysqli_num_rows($terlaris) === 0): ?>
                    <tr><td colspan="4">Belum ada data penjualan.</td></tr>
                <?php else: while ($t = mysqli_fetch_assoc($terlaris)): ?>
                    <tr>
                        <td><?php echo e($t['judul']); ?></td>
                        <td><?php echo e($t['penulis']); ?></td>
                        <td><?php echo (int)$t['total_terjual']; ?></td>
                        <td><?php echo formatRupiah($t['total_pendapatan']); ?></td>
                    </tr>
                <?php endwhile; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>
