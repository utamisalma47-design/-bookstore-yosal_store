<?php
require_once 'config/db.php';
require_once 'config/auth.php';
requireLogin();

$alamat = trim($_POST['alamat'] ?? '');
$noTelp = trim($_POST['no_telp'] ?? '');
$userId = currentUserId();

$isAjax = ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';

function respond($isAjax, $ok, $message, $alamat = '', $noTelp = '') {
    if ($isAjax) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => $ok,
            'message' => $message,
            'alamat'  => $alamat,
            'no_telp' => $noTelp,
        ]);
    } else {
        header('Location: checkout.php');
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond($isAjax, false, 'Metode tidak valid.');
}

if ($alamat === '') {
    respond($isAjax, false, 'Alamat tidak boleh kosong.');
}

if (mb_strlen($alamat) > 255 || mb_strlen($noTelp) > 30) {
    respond($isAjax, false, 'Alamat maksimal 255 karakter dan no. telp maksimal 30 karakter.');
}

if ($noTelp !== '' && !preg_match('/^[0-9+\-\s()]{6,30}$/', $noTelp)) {
    respond($isAjax, false, 'Format no. telepon tidak valid.');
}

$stmt = mysqli_prepare($koneksi, "UPDATE users SET alamat = ?, no_telp = ? WHERE id = ?");
mysqli_stmt_bind_param($stmt, 'ssi', $alamat, $noTelp, $userId);

if (!mysqli_stmt_execute($stmt)) {
    respond($isAjax, false, 'Gagal menyimpan ke database.');
}

respond($isAjax, true, 'Alamat tersimpan.', $alamat, $noTelp);
