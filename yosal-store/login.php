<?php
require_once 'config/db.php';
require_once 'config/auth.php';

if (isLoggedIn()) {
    header('Location: ' . ($_SESSION['role'] === 'admin' ? 'admin/dashboard.php' : 'index.php'));
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = 'Email dan password wajib diisi.';
    } else {
        $stmt = mysqli_prepare($koneksi, "SELECT * FROM users WHERE email = ?");
        mysqli_stmt_bind_param($stmt, 's', $email);
        mysqli_stmt_execute($stmt);
        $user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

        if ($user && password_verify($password, $user['password'])) {
            if ($user['status'] === 'nonaktif') {
                $error = 'Akun kamu tidak aktif. Hubungi admin.';
            } else {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['nama'] = $user['nama'];
                $_SESSION['role'] = $user['role'];

                if ($user['role'] === 'admin') {
                    header('Location: admin/dashboard.php');
                } else {
                    $redirect = $_SESSION['redirect_after_login'] ?? 'index.php';
                    unset($_SESSION['redirect_after_login']);
                    header('Location: ' . $redirect);
                }
                exit;
            }
        } else {
            $error = 'Email atau password salah.';
        }
    }
}

$pageTitle = 'Login';
require 'includes/header.php';
?>

<div class="form-card">
    <h2>Login</h2>
    <p class="subtitle">Selamat Datang Kembali!</p>

    <?php if ($error): ?><div class="alert alert-error"><?php echo e($error); ?></div><?php endif; ?>

    <form method="POST" action="" autocomplete="off">
        <div class="form-group">
            <label>Email</label>
           <input type="email" name="email" autocomplete="off"> 
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" autocomplete="new-password">
        </div>
        <button type="submit" class="btn-full">Login</button>
    </form>


    <p class="form-alt-text">Belum Punya Akun? <a href="register.php">Daftar Disini</a></p>
</div>

<?php require 'includes/footer.php'; ?>
