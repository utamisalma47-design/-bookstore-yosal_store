
<?php
require_once 'config/db.php';
require_once 'config/auth.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false]);
    exit;
}

requireLogin();

$userId = currentUserId();
$bukuId = isset($_POST['buku_id']) ? (int)$_POST['buku_id'] : 0;

if ($bukuId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false]);
    exit;
}

// Periksa apakah buku sudah ada di wishlist
$stmt = mysqli_prepare(
    $koneksi,
    "SELECT id FROM wishlist WHERE user_id = ? AND buku_id = ?"
);

mysqli_stmt_bind_param($stmt, 'ii', $userId, $bukuId);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$existing = mysqli_fetch_assoc($result);

if ($existing) {

    // Hapus dari wishlist
    $delete = mysqli_prepare(
        $koneksi,
        "DELETE FROM wishlist WHERE user_id = ? AND buku_id = ?"
    );

    mysqli_stmt_bind_param($delete, 'ii', $userId, $bukuId);
    $success = mysqli_stmt_execute($delete);

    $active = false;

} else {

    // Tambahkan ke wishlist
    $insert = mysqli_prepare(
        $koneksi,
        "INSERT INTO wishlist (user_id, buku_id) VALUES (?, ?)"
    );

    mysqli_stmt_bind_param($insert, 'ii', $userId, $bukuId);
    $success = mysqli_stmt_execute($insert);

    $active = true;
}

echo json_encode([
    'success' => $success,
    'active' => $active
]);
?>
