<?php
require_once 'config/db.php';
require_once 'config/auth.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: kategori.php');
    exit;
}

$bukuId = (int)($_POST['buku_id'] ?? 0);
$jumlah = (int)($_POST['jumlah'] ?? 1);

if ($bukuId <= 0 || $jumlah <= 0) {
    header('Location: kategori.php');
    exit;
}

$stmt = mysqli_prepare(
    $koneksi,
    "SELECT id, stock FROM buku WHERE id = ?"
);

mysqli_stmt_bind_param($stmt, 'i', $bukuId);
mysqli_stmt_execute($stmt);

$buku = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$buku || $jumlah > (int)$buku['stock']) {
    header('Location: produk.php?id=' . $bukuId . '&error=stok');
    exit;
}

// Simpan pembelian langsung ke SESSION
$_SESSION['buy_now'] = [
    'buku_id' => $bukuId,
    'jumlah' => $jumlah
];

header('Location: checkout.php?mode=buy_now');
exit;
?>