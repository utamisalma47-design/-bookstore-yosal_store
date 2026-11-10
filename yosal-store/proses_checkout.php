<?php
require_once 'config/db.php';
require_once 'config/auth.php';
requireLogin();

$userId = currentUserId();
$metodePengiriman = $_POST['metode_pengiriman'] ?? 'Reguler (2-4 Hari)';
$ongkir = (int)($_POST['ongkos_kirim'] ?? 0);
$metodePembayaran = $_POST['metode_pembayaran'] ?? 'COD';
// Kalau datang dari "Beli Sekarang", error harus kembali ke checkout mode yang sama
$modeQs = (($_POST['mode'] ?? '') === 'langsung') ? 'mode=langsung&' : '';
$bankTujuan = null;
$buktiTransferName = null;

// ---- Handle Transfer Bank: bank tujuan & upload bukti transfer ----
if ($metodePembayaran === 'Transfer Bank') {
    $bankTujuan = $_POST['bank_tujuan'] ?? null;

    if (!$bankTujuan) {
        header('Location: checkout.php?' . $modeQs . 'error=bank_belum_dipilih');
        exit;
    }

    if (!isset($_FILES['bukti_transfer']) || $_FILES['bukti_transfer']['error'] !== UPLOAD_ERR_OK) {
        header('Location: checkout.php?' . $modeQs . 'error=bukti_transfer_wajib');
        exit;
    }

    $allowedExt = ['jpg', 'jpeg', 'png', 'pdf'];
    $originalName = $_FILES['bukti_transfer']['name'];
    $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

    if (!in_array($ext, $allowedExt)) {
        header('Location: checkout.php?' . $modeQs . 'error=format_bukti_tidak_didukung');
        exit;
    }

    // Batas ukuran file 5MB
    if ($_FILES['bukti_transfer']['size'] > 5 * 1024 * 1024) {
        header('Location: checkout.php?' . $modeQs . 'error=bukti_terlalu_besar');
        exit;
    }

    $uploadDir = __DIR__ . '/uploads/bukti_transfer/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $buktiTransferName = 'BUKTI-' . time() . '-' . mt_rand(1000, 9999) . '.' . $ext;
    $targetPath = $uploadDir . $buktiTransferName;

    if (!move_uploaded_file($_FILES['bukti_transfer']['tmp_name'], $targetPath)) {
        header('Location: checkout.php?' . $modeQs . 'error=upload_gagal');
        exit;
    }
}

$userStmt = mysqli_prepare($koneksi, "SELECT * FROM users WHERE id = ?");
mysqli_stmt_bind_param($userStmt, 'i', $userId);
mysqli_stmt_execute($userStmt);
$user = mysqli_fetch_assoc(mysqli_stmt_get_result($userStmt));

$rows = [];
$total = 0;
$beliLangsung = (($_POST['mode'] ?? '') === 'langsung' && !empty($_SESSION['beli_langsung']));

if ($beliLangsung) {
    $bl = $_SESSION['beli_langsung'];
    $bukuStmt = mysqli_prepare($koneksi, "SELECT * FROM buku WHERE id = ?");
    mysqli_stmt_bind_param($bukuStmt, 'i', $bl['buku_id']);
    mysqli_stmt_execute($bukuStmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($bukuStmt));
    if ($row && (int)$row['stock'] >= (int)$bl['jumlah']) {
        $row['jumlah'] = (int)$bl['jumlah'];
        $row['subtotal'] = $row['harga'] * $row['jumlah'];
        $total += $row['subtotal'];
        $rows[] = $row;
    }
} else {
    $cartStmt = mysqli_prepare($koneksi, "SELECT c.jumlah, b.* FROM cart c JOIN buku b ON c.buku_id = b.id WHERE c.user_id = ?");
    mysqli_stmt_bind_param($cartStmt, 'i', $userId);
    mysqli_stmt_execute($cartStmt);
    $cartItems = mysqli_stmt_get_result($cartStmt);
    while ($row = mysqli_fetch_assoc($cartItems)) {
        $row['subtotal'] = $row['harga'] * $row['jumlah'];
        $total += $row['subtotal'];
        $rows[] = $row;
    }
}

if (empty($rows)) {
    header('Location: ' . ($beliLangsung ? 'index.php' : 'cart.php'));
    exit;
}

$grandTotal = $total + $ongkir;
$orderId = 'YSL' . mt_rand(1000000, 9999999);

mysqli_begin_transaction($koneksi);
try {
    $insOrder = mysqli_prepare($koneksi, "INSERT INTO pesanan (order_id, user_id, nama_penerima, alamat_pengiriman, no_telp, metode_pengiriman, ongkos_kirim, metode_pembayaran, bank_tujuan, bukti_transfer, total, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Menunggu')");
    $alamat = $user['alamat'] ?: '-';
    $noTelp = $user['no_telp'] ?: '-';
    mysqli_stmt_bind_param(
        $insOrder,
        'sissssdsssd',
        $orderId, $userId, $user['nama'], $alamat, $noTelp, $metodePengiriman,
        $ongkir, $metodePembayaran, $bankTujuan, $buktiTransferName, $grandTotal
    );
    mysqli_stmt_execute($insOrder);
    $pesananId = mysqli_insert_id($koneksi);

    $insDetail = mysqli_prepare($koneksi, "INSERT INTO pesanan_detail (pesanan_id, buku_id, judul_buku, harga, jumlah, subtotal) VALUES (?, ?, ?, ?, ?, ?)");
    $updStock = mysqli_prepare($koneksi, "UPDATE buku SET stock = stock - ? WHERE id = ?");

    foreach ($rows as $r) {
        mysqli_stmt_bind_param($insDetail, 'iisdid', $pesananId, $r['id'], $r['judul'], $r['harga'], $r['jumlah'], $r['subtotal']);
        mysqli_stmt_execute($insDetail);

        mysqli_stmt_bind_param($updStock, 'ii', $r['jumlah'], $r['id']);
        mysqli_stmt_execute($updStock);
    }

    if ($beliLangsung) {
        // Beli Sekarang: keranjang tidak disentuh
        unset($_SESSION['beli_langsung']);
    } else {
        $delCart = mysqli_prepare($koneksi, "DELETE FROM cart WHERE user_id = ?");
        mysqli_stmt_bind_param($delCart, 'i', $userId);
        mysqli_stmt_execute($delCart);
    }

    mysqli_commit($koneksi);
    $_SESSION['last_order_id'] = $orderId;
    header('Location: payment_success.php');
    exit;
} catch (Exception $e) {
    mysqli_rollback($koneksi);
    header('Location: checkout.php?' . $modeQs . 'error=1');
    exit;
}