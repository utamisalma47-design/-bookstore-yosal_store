<?php
require_once 'config/db.php';

function upsertUser($koneksi, $nama, $email, $passwordPlain, $role, $alamat = null, $noTelp = null, $tanggal = null) {
    $check = mysqli_prepare($koneksi, "SELECT id FROM users WHERE email = ?");
    mysqli_stmt_bind_param($check, 's', $email);
    mysqli_stmt_execute($check);
    $existing = mysqli_fetch_assoc(mysqli_stmt_get_result($check));
    if ($existing) return $existing['id'];

    $hash = password_hash($passwordPlain, PASSWORD_BCRYPT);
    if ($tanggal) {
        $stmt = mysqli_prepare($koneksi, "INSERT INTO users (nama, email, password, role, alamat, no_telp, status, created_at) VALUES (?, ?, ?, ?, ?, ?, 'aktif', ?)");
        mysqli_stmt_bind_param($stmt, 'sssssss', $nama, $email, $hash, $role, $alamat, $noTelp, $tanggal);
    } else {
        $stmt = mysqli_prepare($koneksi, "INSERT INTO users (nama, email, password, role, alamat, no_telp, status) VALUES (?, ?, ?, ?, ?, ?, 'aktif')");
        mysqli_stmt_bind_param($stmt, 'ssssss', $nama, $email, $hash, $role, $alamat, $noTelp);
    }
    mysqli_stmt_execute($stmt);
    return mysqli_insert_id($koneksi);
}

echo "<h2>Setup Akun Yosal Store</h2><ul>";

// Admin (password: admin123)
$adminId = upsertUser($koneksi, 'Admin', 'admin@yosalstore.com', 'admin123', 'admin');
echo "<li>Admin siap. Email: <b>admin@yosalstore.com</b> / Password: <b>admin123</b></li>";

// User contoh (password sama semua: user123)
$u1 = upsertUser($koneksi, 'Nadia Yaya', 'nadiayaya2@gmail.com', 'user123', 'user', 'Jl.Mawar, No.22, Jakarta Timur, DKI Jakarta 56746', '0834-2342-4433', '2004-07-22');
$u2 = upsertUser($koneksi, 'Alicia Nasya', 'alicianasya72@gmail.com', 'user123', 'user', 'Jl. Melati No.10, Bandung', '0812-1111-2222', '2004-05-12');
$u3 = upsertUser($koneksi, 'Melati Lestari', 'melatilestari03@gmail.com', 'user123', 'user', 'Jl. Kenanga No.5, Surabaya', '0813-2222-3333', '2004-05-24');
$u4 = upsertUser($koneksi, 'Mail Pramata', 'mailpramata8@gmail.com', 'user123', 'user', 'Jl. Anggrek No.7, Yogyakarta', '0814-3333-4444', '2004-06-11');
$u5 = upsertUser($koneksi, 'Budi Anggung', 'budianggung4@gmail.com', 'user123', 'user', 'Jl. Dahlia No.3, Semarang', '0815-4444-5555', '2004-07-06');
echo "<li>5 akun user contoh siap. Password semua: <b>user123</b></li>";

// Pesanan contoh (hanya dibuat jika tabel pesanan masih kosong)
$cek = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) c FROM pesanan"));
if ($cek['c'] == 0) {
    $orders = [
        ['YSL1234543', $u1, 'Nadia Yaya', 'Jl.Mawar, No.22, Jakarta Timur, DKI Jakarta 56746', '0834-2342-4433', 155000, 'Menunggu', '2004-07-22'],
        ['YSL1277544', $u2, 'Alicia Nasya', 'Jl. Melati No.10, Bandung', '0812-1111-2222', 75000, 'Dikemas', '2004-05-12'],
        ['YSL9877533', $u3, 'Melati Lestari', 'Jl. Kenanga No.5, Surabaya', '0813-2222-3333', 105000, 'Dikemas', '2004-05-24'],
        ['YSL9876567', $u4, 'Mail Pramata', 'Jl. Anggrek No.7, Yogyakarta', '0814-3333-4444', 70000, 'Selesai', '2004-06-11'],
        ['YSL7654398', $u5, 'Budi Anggung', 'Jl. Dahlia No.3, Semarang', '0815-4444-5555', 80000, 'Selesai', '2004-07-06'],
    ];
    $stmt = mysqli_prepare($koneksi, "INSERT INTO pesanan (order_id, user_id, nama_penerima, alamat_pengiriman, no_telp, metode_pengiriman, ongkos_kirim, metode_pembayaran, total, status, tanggal_pesan) VALUES (?, ?, ?, ?, ?, 'Reguler (2-4 Hari)', 0, 'COD', ?, ?, ?)");
    foreach ($orders as $o) {
        mysqli_stmt_bind_param($stmt, 'sisssdss', $o[0], $o[1], $o[2], $o[3], $o[4], $o[5], $o[6], $o[7]);
        mysqli_stmt_execute($stmt);
    }
    echo "<li>5 pesanan contoh berhasil dibuat.</li>";
}

echo "</ul><p><b>PENTING:</b> Hapus file setup_akun.php ini dari server sekarang untuk keamanan.</p>";
echo "<p><a href='login.php'>Ke halaman Login &rarr;</a></p>";
