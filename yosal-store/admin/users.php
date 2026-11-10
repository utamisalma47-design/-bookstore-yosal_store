<?php
require_once '../config/db.php';
require_once '../config/auth.php';
requireAdmin();
$inAdmin = true;
$activeAdmin = 'users';

// Toggle status aktif/nonaktif
if (isset($_GET['toggle'])) {
    $id = (int)$_GET['toggle'];
    $stmt = mysqli_prepare($koneksi, "UPDATE users SET status = IF(status='aktif','nonaktif','aktif') WHERE id = ? AND role = 'user'");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    header('Location: users.php');
    exit;
}

$search = trim($_GET['q'] ?? '');
if ($search !== '') {
    $like = '%' . $search . '%';
    $stmt = mysqli_prepare($koneksi, "SELECT * FROM users WHERE role='user' AND (nama LIKE ? OR email LIKE ?) ORDER BY created_at DESC");
    mysqli_stmt_bind_param($stmt, 'ss', $like, $like);
    mysqli_stmt_execute($stmt);
    $list = mysqli_stmt_get_result($stmt);
} else {
    $list = mysqli_query($koneksi, "SELECT * FROM users WHERE role='user' ORDER BY created_at DESC");
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Users - Yosal Store Admin</title>
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="admin-body">
<div class="admin-wrap">
    <?php require 'includes/sidebar.php'; ?>
    <div class="admin-main">
        <div class="page-toolbar">
            <h2>Users</h2>
            <form class="search-admin" method="get">
                <input type="text" name="q" placeholder="Cari Users" value="<?php echo e($search); ?>">
                <button type="submit">&#128269;</button>
            </form>
        </div>

        <div class="admin-table-wrap">
            <table class="data-table">
                <thead><tr><th>No</th><th>Nama</th><th>Email</th><th>Tanggal Daftar</th><th>Status</th><th>Aksi</th></tr></thead>
                <tbody>
                <?php $no = 1; while ($u = mysqli_fetch_assoc($list)): ?>
                    <tr>
                        <td><?php echo $no++; ?></td>
                        <td><?php echo e($u['nama']); ?></td>
                        <td><?php echo e($u['email']); ?></td>
                        <td><?php echo date('d M Y', strtotime($u['created_at'])); ?></td>
                        <td><span class="status-pill <?php echo $u['status']=='aktif'?'status-aktif':'status-dibatalkan'; ?>"><?php echo ucfirst(e($u['status'])); ?></span></td>
                        <td>
                            <a class="icon-action" href="users.php?toggle=<?php echo $u['id']; ?>" title="Ubah status" onclick="return confirm('Ubah status akun ini?')">&#128065;</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>
