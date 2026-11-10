<?php
require_once 'config/db.php';
require_once 'config/auth.php';
header('Content-Type: application/json');

if (!isLoggedIn() || $_SESSION['role'] !== 'user') {
    echo json_encode(['success' => false]);
    exit;
}

$cartId = (int)($_POST['cart_id'] ?? 0);
$userId = currentUserId();

$stmt = mysqli_prepare($koneksi, "DELETE FROM cart WHERE id = ? AND user_id = ?");
mysqli_stmt_bind_param($stmt, 'ii', $cartId, $userId);
$ok = mysqli_stmt_execute($stmt);

echo json_encode(['success' => $ok]);
