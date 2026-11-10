<?php
require_once 'config/db.php';
require_once 'config/auth.php';

if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $konfirmasi = $_POST['konfirmasi'] ?? '';

    if ($nama === '' || $email === '' || $password === '' || $konfirmasi === '') {
        $error = 'Semua kolom wajib diisi.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid.';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter.';
    } elseif ($password !== $konfirmasi) {
        $error = 'Konfirmasi password tidak cocok.';
    } else {
        $check = mysqli_prepare($koneksi, "SELECT id FROM users WHERE email = ?");
        mysqli_stmt_bind_param($check, 's', $email);
        mysqli_stmt_execute($check);
        if (mysqli_stmt_get_result($check)->num_rows > 0) {
            $error = 'Email sudah terdaftar. Silakan login.';
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $ins = mysqli_prepare($koneksi, "INSERT INTO users (nama, email, password, role) VALUES (?, ?, ?, 'user')");
            mysqli_stmt_bind_param($ins, 'sss', $nama, $email, $hash);
            if (mysqli_stmt_execute($ins)) {
                header('Location: login.php?registered=1');
                exit;
            } else {
                $error = 'Registrasi gagal, silakan coba lagi.';
            }
        }
    }
}

$pageTitle = 'Registrasi';
require 'includes/header.php';
?>

<div class="split-layout" style="margin: 0 -40px;">
    <div class="img-side">
        <img src="https://images.unsplash.com/photo-1507842217343-583bb7270b66?w=900" alt="Rak Buku">
    </div>
    <div class="form-side">
        <div class="form-card" style="margin:0; max-width:420px;">
            <h2>Registrasi</h2>
            <p class="subtitle">Buat Akun Baru Untuk Memulai</p>

            <?php if ($error): ?><div class="alert alert-error"><?php echo e($error); ?></div><?php endif; ?>

            <form method="POST" action="" autocomplete="off">
                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" name="nama" placeholder="Nama Lengkap"  autocomplete="off" required value="<?php echo isset($nama) ? e($nama) : ''; ?>">
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" placeholder="Email"  autocomplete="off" required value="<?php echo isset($email) ? e($email) : ''; ?>">
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password"  autocomplete="new-password"  placeholder="Password" required>
                </div>
                <div class="form-group">
                    <label>Konfirmasi Password</label>
                    <input type="password" name="konfirmasi"  autocomplete="new-password"  placeholder="Konfirmasi Password" required>
                </div>
                <button type="submit" class="btn-full">Daftar Sekarang</button>
            </form>
            <p class="form-alt-text">Sudah Punya Akun? <a href="login.php">Login Disini</a></p>
        </div>
    </div>
</div>

<?php require 'includes/footer.php'; ?>
