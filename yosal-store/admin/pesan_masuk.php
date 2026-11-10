<?php
require_once '../config/db.php';
require_once '../config/auth.php';
requireAdmin();
$inAdmin = true;
$activeAdmin = 'pesan_masuk';

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = mysqli_prepare($koneksi, "DELETE FROM pesan_masuk WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    header('Location: pesan_masuk.php');
    exit;
}

if (isset($_GET['read'])) {
    $id = (int)$_GET['read'];
    $stmt = mysqli_prepare($koneksi, "UPDATE pesan_masuk SET status = 'Sudah Dibaca' WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
}

$list = mysqli_query($koneksi, "SELECT * FROM pesan_masuk ORDER BY created_at DESC");
$rows = mysqli_fetch_all($list, MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Pesan Masuk - Yosal Store Admin</title>
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="admin-body">
<div class="admin-wrap">
    <?php require 'includes/sidebar.php'; ?>
    <div class="admin-main">
        <div class="page-toolbar"><h2>Pesan Masuk</h2></div>

        <div class="admin-table-wrap">
            <table class="data-table">
                <thead><tr><th>No</th><th>Nama</th><th>Email</th><th>Subjek</th><th>Tanggal</th><th>Status</th><th>Aksi</th></tr></thead>
                <tbody>
                <?php $no = 1; foreach ($rows as $m): ?>
                    <tr>
                        <td><?php echo $no++; ?></td>
                        <td><?php echo e($m['nama']); ?></td>
                        <td><?php echo e($m['email']); ?></td>
                        <td><?php echo e($m['subjek']); ?></td>
                        <td><?php echo date('d M Y', strtotime($m['created_at'])); ?></td>
                        <td><span class="status-pill <?php echo $m['status']=='Sudah Dibaca'?'status-selesai':'status-menunggu'; ?>"><?php echo e($m['status']); ?></span></td>
                        <td>
                            <button class="icon-action" onclick='openMsg(<?php echo json_encode($m); ?>)'>&#128065;</button>
                            <a class="icon-action" href="pesan_masuk.php?delete=<?php echo $m['id']; ?>" onclick="return confirm('Hapus pesan ini?')">&#128465;</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal-overlay" id="modalMsg">
    <div class="modal-box">
        <h3 id="msg-subjek"></h3>
        <p style="margin-bottom:10px; color:#666;" id="msg-from"></p>
        <p id="msg-body" style="line-height:1.6;"></p>
        <div class="modal-actions">
            <button type="button" class="btn-secondary" onclick="closeModal('modalMsg')" style="flex:1;">Tutup</button>
        </div>
    </div>
</div>

<script src="../assets/js/main.js"></script>
<script>
function openMsg(data) {
    document.getElementById('msg-subjek').innerText = data.subjek;
    document.getElementById('msg-from').innerText = data.nama + ' - ' + data.email;
    document.getElementById('msg-body').innerText = data.pesan;
    openModal('modalMsg');
    if (data.status !== 'Sudah Dibaca') {
        fetch('pesan_masuk.php?read=' + data.id);
    }
}
</script>
</body>
</html>
