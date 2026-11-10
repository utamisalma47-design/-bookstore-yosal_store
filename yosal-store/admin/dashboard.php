<?php
require_once '../config/db.php';
require_once '../config/auth.php';
requireAdmin();
$pageTitle = 'Dashboard Admin';
$inAdmin = true;
$activeAdmin = 'dashboard';

$totalBuku = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) c FROM buku"))['c'];
$totalKategori = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) c FROM kategori"))['c'];
$totalUser = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) c FROM users WHERE role='user'"))['c'];
$totalPesanan = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) c FROM pesanan"))['c'];

$recentOrders = mysqli_query($koneksi, "SELECT * FROM pesanan ORDER BY tanggal_pesan DESC LIMIT 5");
$statusClass = ['Menunggu'=>'status-menunggu','Dikemas'=>'status-dikemas','Selesai'=>'status-selesai','Dibatalkan'=>'status-dibatalkan'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard - Yosal Store Admin</title>
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="admin-body">
<div class="admin-wrap">
    <?php require 'includes/sidebar.php'; ?>
    <div class="admin-main">
        <h1>Dashboard</h1>
        <p class="welcome">Selamat Datang Kembali Admin!</p>

        <div class="admin-stats">
            <div class="admin-stat-card"><strong><?php echo $totalBuku; ?></strong><span>Total Buku</span></div>
            <div class="admin-stat-card"><strong><?php echo $totalKategori; ?></strong><span>Kategori</span></div>
            <div class="admin-stat-card"><strong><?php echo $totalUser; ?></strong><span>User Terdaftar</span></div>
            <div class="admin-stat-card"><strong><?php echo $totalPesanan; ?></strong><span>Pesanan</span></div>
        </div>

        <div class="admin-table-wrap">
            <div class="admin-table-head">
                Pesanan Terbaru
                <a href="pesanan.php">Lihat Semua</a>
            </div>
            <table class="data-table">
                <?php while ($o = mysqli_fetch_assoc($recentOrders)): ?>
                <tr>
                    <td>#<?php echo e($o['order_id']); ?></td>
                    <td><?php echo date('d M Y', strtotime($o['tanggal_pesan'])); ?></td>
                    <td><?php echo e($o['nama_penerima']); ?></td>
                    <td><?php echo formatRupiah($o['total']); ?></td>
                    <td><span class="status-pill <?php echo $statusClass[$o['status']] ?? ''; ?>"><?php echo e($o['status']); ?></span></td>
                </tr>
                <?php endwhile; ?>
            </table>
        </div>
    </div>
</div>
</body>
</html>
