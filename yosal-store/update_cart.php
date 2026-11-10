<?php
require_once 'config/db.php';
require_once 'config/auth.php';
header('Content-Type: application/json');

if (!isLoggedIn() || $_SESSION['role'] !== 'user') {
    echo json_encode(['success' => false]);
    exit;
}

$cartId = (int)($_POST['cart_id'] ?? 0);
$jumlah = max(1, (int)($_POST['jumlah'] ?? 1));
$userId = currentUserId();

$stmt = mysqli_prepare($koneksi, "SELECT c.id, b.harga, b.stock FROM cart c JOIN buku b ON c.buku_id = b.id WHERE c.id = ? AND c.user_id = ?");
mysqli_stmt_bind_param($stmt, 'ii', $cartId, $userId);
mysqli_stmt_execute($stmt);
$cartItem = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$cartItem) {
    echo json_encode(['success' => false]);
    exit;
}

if ($jumlah > $cartItem['stock']) { $jumlah = $cartItem['stock']; }

$upd = mysqli_prepare($koneksi, "UPDATE cart SET jumlah = ? WHERE id = ?");
mysqli_stmt_bind_param($upd, 'ii', $jumlah, $cartId);
mysqli_stmt_execute($upd);

$subtotal = $jumlah * $cartItem['harga'];

$totalQ = mysqli_prepare($koneksi, "SELECT SUM(c.jumlah * b.harga) as total FROM cart c JOIN buku b ON c.buku_id = b.id WHERE c.user_id = ?");
mysqli_stmt_bind_param($totalQ, 'i', $userId);
mysqli_stmt_execute($totalQ);
$total = mysqli_fetch_assoc(mysqli_stmt_get_result($totalQ))['total'] ?? 0;

echo json_encode([
    'success' => true,
    'subtotal_format' => formatRupiah($subtotal),
    'total_format' => formatRupiah($total),
]);
