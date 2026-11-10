<?php
require_once 'config/db.php';
require_once 'config/auth.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

// Nama field dibuat fleksibel supaya cocok dengan form tambah keranjang
$bukuId = (int)($_POST['buku_id'] ?? $_POST['id'] ?? 0);
$jumlah = (int)($_POST['jumlah'] ?? $_POST['qty'] ?? $_POST['quantity'] ?? 1);
if ($jumlah < 1) $jumlah = 1;

$stmt = mysqli_prepare($koneksi, "SELECT id, stock FROM buku WHERE id = ?");
mysqli_stmt_bind_param($stmt, 'i', $bukuId);
mysqli_stmt_execute($stmt);
$buku = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$buku || (int)$buku['stock'] < 1) {
    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'index.php'));
    exit;
}

// Jumlah tidak boleh melebihi stok
if ($jumlah > (int)$buku['stock']) {
    $jumlah = (int)$buku['stock'];
}

$_SESSION['beli_langsung'] = ['buku_id' => $bukuId, 'jumlah' => $jumlah];
header('Location: checkout.php?mode=langsung');
exit;