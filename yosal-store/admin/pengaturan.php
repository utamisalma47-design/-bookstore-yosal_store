<?php
require_once '../config/db.php';
require_once '../config/auth.php';
requireAdmin();
$inAdmin = true;
$activeAdmin = 'pengaturan';

$adminId = currentUserId();
$stmt = mysqli_prepare($koneksi, "SELECT * FROM users WHERE id = ?");
mysqli_stmt_bind_param($stmt, 'i', $adminId);
mysqli_stmt_execute($stmt);
$admin = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $nama = trim($_POST['nama']);
    $email = trim($_POST['email']);
    $stmt = mysqli_prepare($koneksi, "UPDATE users SET nama = ?, email = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'ssi', $nama, $email, $adminId);
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['nama'] = $nama;
        $success = 'Profil berhasil diperbarui.';
        $admin['nama'] = $nama;
        $admin['email'] = $email;
    } else {
        $error = 'Gagal memperbarui profil (email mungkin sudah digunakan).';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_password'])) {
    $current = $_POST['current_password'];
    $new = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];

    if (!password_verify($current, $admin['password'])) {
        $error = 'Password saat ini salah.';
    } elseif (strlen($new) < 6) {
        $error = 'Password baru minimal 6 karakter.';
    } elseif ($new !== $confirm) {
        $error = 'Konfirmasi password tidak cocok.';
    } else {
        $hash = password_hash($new, PASSWORD_BCRYPT);
        $stmt = mysqli_prepare($koneksi, "UPDATE users SET password = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, 'si', $hash, $adminId);
        mysqli_stmt_execute($stmt);
        $success = 'Password berhasil diperbarui.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Pengaturan - Yosal Store Admin</title>
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="admin-body">
<div class="admin-wrap">
    <?php require 'includes/sidebar.php'; ?>
    <div class="admin-main">
        <div class="page-toolbar"><h2>Pengaturan Akun</h2></div>

        <?php if ($success): ?><div class="alert alert-success" style="max-width:420px;"><?php echo e($success); ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-error" style="max-width:420px;"><?php echo e($error); ?></div><?php endif; ?>

        <div class="checkout-box" style="max-width:420px;">
            <h3>Data Profil</h3>
            <form method="post">
                <div class="form-group"><label>Nama</label><input type="text" name="nama" value="<?php echo e($admin['nama']); ?>" required></div>
                <div class="form-group"><label>Email</label><input type="email" name="email" value="<?php echo e($admin['email']); ?>" required></div>
                <button type="submit" name="update_profile" class="btn-full">Simpan Profil</button>
            </form>
        </div>

        <div class="checkout-box" style="max-width:420px;">
            <h3>Ubah Password</h3>
            <form method="post">
                <div class="form-group"><label>Password Saat Ini</label><input type="password" name="current_password" required></div>
                <div class="form-group"><label>Password Baru</label><input type="password" name="new_password" required></div>
                <div class="form-group"><label>Konfirmasi Password Baru</label><input type="password" name="confirm_password" required></div>
                <button type="submit" name="update_password" class="btn-full">Ubah Password</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
