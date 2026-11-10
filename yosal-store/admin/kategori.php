<?php
require_once '../config/db.php';
require_once '../config/auth.php';
requireAdmin();
$inAdmin = true;
$activeAdmin = 'kategori';

function makeSlug($str) {
    return strtolower(trim(preg_replace('/[^A-Za-z0-9]+/', '-', $str), '-'));
}

// Tambah kategori
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $nama = trim($_POST['nama_kategori']);
    $slug = makeSlug($nama);
    $stmt = mysqli_prepare($koneksi, "INSERT INTO kategori (nama_kategori, slug) VALUES (?, ?)");
    mysqli_stmt_bind_param($stmt, 'ss', $nama, $slug);
    mysqli_stmt_execute($stmt);
    header('Location: kategori.php');
    exit;
}

// Update kategori
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
    $id = (int)$_POST['id'];
    $nama = trim($_POST['nama_kategori']);
    $slug = makeSlug($nama);
    $stmt = mysqli_prepare($koneksi, "UPDATE kategori SET nama_kategori = ?, slug = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'ssi', $nama, $slug, $id);
    mysqli_stmt_execute($stmt);
    header('Location: kategori.php');
    exit;
}

// Hapus kategori
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = mysqli_prepare($koneksi, "DELETE FROM kategori WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    header('Location: kategori.php');
    exit;
}

$list = mysqli_query($koneksi, "SELECT * FROM kategori ORDER BY id ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Kategori Buku - Yosal Store Admin</title>
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="admin-body">
<div class="admin-wrap">
    <?php require 'includes/sidebar.php'; ?>
    <div class="admin-main">
        <div class="page-toolbar">
            <h2>Kategori Buku</h2>
            <button class="btn-add" onclick="openModal('modalAdd')">+ Tambah Kategori</button>
        </div>

        <div class="admin-table-wrap">
            <table class="data-table">
                <thead><tr><th style="width:60px;">No</th><th>Nama Kategori</th><th style="width:120px;">Aksi</th></tr></thead>
                <tbody>
                <?php $no = 1; while ($k = mysqli_fetch_assoc($list)): ?>
                    <tr>
                        <td><?php echo $no++; ?></td>
                        <td><?php echo e($k['nama_kategori']); ?></td>
                        <td>
                            <button class="icon-action" onclick='openEdit(<?php echo json_encode($k); ?>)'>&#9998;</button>
                            <a class="icon-action" href="kategori.php?delete=<?php echo $k['id']; ?>" onclick="return confirm('Hapus kategori ini? Semua buku di kategori ini juga akan terhapus.')">&#128465;</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah -->
<div class="modal-overlay" id="modalAdd">
    <div class="modal-box">
        <h3>Tambah Kategori</h3>
        <form method="post" action="kategori.php">
            <input type="hidden" name="action" value="add">
            <div class="form-group">
                <label>Nama Kategori</label>
                <input type="text" name="nama_kategori" required>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-secondary" onclick="closeModal('modalAdd')">Batal</button>
                <button type="submit" class="btn-primary" style="flex:1;">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit -->
<div class="modal-overlay" id="modalEdit">
    <div class="modal-box">
        <h3>Edit Kategori</h3>
        <form method="post" action="kategori.php">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" id="edit-id">
            <div class="form-group">
                <label>Nama Kategori</label>
                <input type="text" name="nama_kategori" id="edit-nama" required>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-secondary" onclick="closeModal('modalEdit')">Batal</button>
                <button type="submit" class="btn-primary" style="flex:1;">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script src="../assets/js/main.js"></script>
<script>
function openEdit(data) {
    document.getElementById('edit-id').value = data.id;
    document.getElementById('edit-nama').value = data.nama_kategori;
    openModal('modalEdit');
}
</script>
</body>
</html>
