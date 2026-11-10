<?php
// Endpoint JSON untuk pencarian otomatis (live search)
require_once 'config/db.php';
require_once 'config/auth.php';

header('Content-Type: application/json; charset=utf-8');

$keyword = trim($_GET['q'] ?? '');
if ($keyword === '') {
    echo json_encode([]);
    exit;
}

$like   = '%' . $keyword . '%';
$prefix = $keyword . '%';

$stmt = mysqli_prepare(
    $koneksi,
    "SELECT id, judul, penulis, harga, gambar
     FROM buku
     WHERE judul LIKE ? OR penulis LIKE ?
     ORDER BY (judul LIKE ?) DESC, judul ASC
     LIMIT 6"
);
mysqli_stmt_bind_param($stmt, 'sss', $like, $like, $prefix);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);

$out = [];
while ($b = mysqli_fetch_assoc($res)) {
    $out[] = [
        'id'      => (int)$b['id'],
        'judul'   => $b['judul'],
        'penulis' => $b['penulis'],
        'harga'   => formatRupiah($b['harga']),
        'gambar'  => $b['gambar'],
    ];
}

echo json_encode($out);
