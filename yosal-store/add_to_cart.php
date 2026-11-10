<?php
require_once 'config/db.php';
require_once 'config/auth.php';

if (!isLoggedIn() || $_SESSION['role'] !== 'user') {
    $_SESSION['redirect_after_login'] = $_SERVER['HTTP_REFERER'] ?? 'index.php';
    header('Location: login.php');
    exit;
}

$bukuId = (int)($_POST['buku_id'] ?? 0);
$jumlah = max(1, (int)($_POST['jumlah'] ?? 1));
$userId = currentUserId();

if ($bukuId > 0) {
    $stmt = mysqli_prepare($koneksi, "SELECT id, jumlah FROM cart WHERE user_id = ? AND buku_id = ?");
    mysqli_stmt_bind_param($stmt, 'ii', $userId, $bukuId);
    mysqli_stmt_execute($stmt);
    $existing = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    if ($existing) {
        $newJumlah = $existing['jumlah'] + $jumlah;
        $upd = mysqli_prepare($koneksi, "UPDATE cart SET jumlah = ? WHERE id = ?");
        mysqli_stmt_bind_param($upd, 'ii', $newJumlah, $existing['id']);
        mysqli_stmt_execute($upd);
    } else {
        $ins = mysqli_prepare($koneksi, "INSERT INTO cart (user_id, buku_id, jumlah) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($ins, 'iii', $userId, $bukuId, $jumlah);
        mysqli_stmt_execute($ins);
    }
}

header('Location: cart.php');
exit;
