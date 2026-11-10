
<?php
require_once '../config/db.php';
require_once '../config/auth.php';

requireAdmin();

$inAdmin = true;
$activeAdmin = 'buku';

$uploadError = '';

// =====================================
// FUNGSI UPLOAD COVER BUKU
// =====================================

function uploadCover($file) {

    if (!isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }

    // Maksimal 5 MB
    if ($file['size'] > 5 * 1024 * 1024) {
        return false;
    }

    // Periksa apakah benar gambar
    $info = getimagesize($file['tmp_name']);

    if ($info === false) {
        return false;
    }

    // Format gambar yang diperbolehkan
    $format = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp'
    ];

    if (!isset($format[$info['mime']])) {
        return false;
    }

    // Nama gambar otomatis
    $namaFile = uniqid('cover_') . '.' . $format[$info['mime']];

    // Lokasi penyimpanan gambar
    $folder = __DIR__ . '/../assets/img/';

    if (!is_dir($folder) || !is_writable($folder)) {
        return false;
    }

    $tujuan = $folder . $namaFile;

    if (move_uploaded_file($file['tmp_name'], $tujuan)) {
        return $namaFile;
    }

    return false;
}


// =====================================
// TAMBAH BUKU
// =====================================

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    ($_POST['action'] ?? '') === 'add'
) {

    $kategoriId = (int)$_POST['kategori_id'];
    $judul = trim($_POST['judul']);
    $penulis = trim($_POST['penulis']);
    $deskripsi = trim($_POST['deskripsi']);
    $harga = (float)$_POST['harga'];
    $stock = (int)$_POST['stock'];

    $gambar = uploadCover($_FILES['gambar'] ?? []);

    if ($gambar === false) {

        $uploadError = 'Gagal upload gambar. Gunakan JPG, PNG, atau WEBP maksimal 5 MB.';

    } elseif ($judul === '' || $penulis === '') {

        $uploadError = 'Judul dan penulis wajib diisi.';

    } else {

        if ($gambar === null) {
            $gambar = 'default-book.jpg';
        }

        $stmt = mysqli_prepare(
            $koneksi,
            "INSERT INTO buku
            (kategori_id, judul, penulis, deskripsi, harga, stock, gambar)
            VALUES (?, ?, ?, ?, ?, ?, ?)"
        );

        mysqli_stmt_bind_param(
            $stmt,
            'isssdis',
            $kategoriId,
            $judul,
            $penulis,
            $deskripsi,
            $harga,
            $stock,
            $gambar
        );

        if (mysqli_stmt_execute($stmt)) {
            header('Location: data_buku.php');
            exit;
        } else {
            $uploadError = 'Gagal menyimpan data buku.';
        }
    }
}


// =====================================
// EDIT BUKU
// =====================================

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    ($_POST['action'] ?? '') === 'edit'
) {

    $id = (int)$_POST['id'];
    $kategoriId = (int)$_POST['kategori_id'];
    $judul = trim($_POST['judul']);
    $penulis = trim($_POST['penulis']);
    $deskripsi = trim($_POST['deskripsi']);
    $harga = (float)$_POST['harga'];
    $stock = (int)$_POST['stock'];

    // Ambil gambar lama
    $cek = mysqli_prepare(
        $koneksi,
        "SELECT gambar FROM buku WHERE id = ?"
    );

    mysqli_stmt_bind_param($cek, 'i', $id);
    mysqli_stmt_execute($cek);

    $dataLama = mysqli_fetch_assoc(
        mysqli_stmt_get_result($cek)
    );

    if (!$dataLama) {

        $uploadError = 'Data buku tidak ditemukan.';

    } elseif ($judul === '' || $penulis === '') {

        $uploadError = 'Judul dan penulis wajib diisi.';

    } else {

        // Upload gambar baru jika dipilih
        $gambarBaru = uploadCover($_FILES['gambar'] ?? []);

        if ($gambarBaru === false) {

            $uploadError = 'Gagal upload gambar. Gunakan JPG, PNG, atau WEBP maksimal 5 MB.';

        } else {

            // Jika tidak memilih gambar baru,
            // gunakan gambar yang lama
            $gambar = $gambarBaru ?: $dataLama['gambar'];

            $stmt = mysqli_prepare(
                $koneksi,
                "UPDATE buku SET
                kategori_id = ?,
                judul = ?,
                penulis = ?,
                deskripsi = ?,
                harga = ?,
                stock = ?,
                gambar = ?
                WHERE id = ?"
            );

            mysqli_stmt_bind_param(
                $stmt,
                'isssdisi',
                $kategoriId,
                $judul,
                $penulis,
                $deskripsi,
                $harga,
                $stock,
                $gambar,
                $id
            );

            if (mysqli_stmt_execute($stmt)) {

                header('Location: data_buku.php');
                exit;

            } else {

                $uploadError = 'Gagal mengubah data buku.';
            }
        }
    }
}


// =====================================
// HAPUS BUKU
// =====================================

if (isset($_GET['delete'])) {

    $id = (int)$_GET['delete'];

    $stmt = mysqli_prepare(
        $koneksi,
        "DELETE FROM buku WHERE id = ?"
    );

    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);

    header('Location: data_buku.php');
    exit;
}


// =====================================
// PENCARIAN BUKU
// =====================================

$search = trim($_GET['q'] ?? '');

if ($search !== '') {

    $like = '%' . $search . '%';

    $stmt = mysqli_prepare(
        $koneksi,
        "SELECT b.*, k.nama_kategori
        FROM buku b
        JOIN kategori k ON b.kategori_id = k.id
        WHERE b.judul LIKE ? OR b.penulis LIKE ?
        ORDER BY b.id DESC"
    );

    mysqli_stmt_bind_param($stmt, 'ss', $like, $like);
    mysqli_stmt_execute($stmt);

    $list = mysqli_stmt_get_result($stmt);

} else {

    $list = mysqli_query(
        $koneksi,
        "SELECT b.*, k.nama_kategori
        FROM buku b
        JOIN kategori k ON b.kategori_id = k.id
        ORDER BY b.id DESC"
    );
}


// =====================================
// DATA KATEGORI
// =====================================

$kategoriList = mysqli_query(
    $koneksi,
    "SELECT * FROM kategori ORDER BY nama_kategori ASC"
);

$kategoriArr = [];

while ($k = mysqli_fetch_assoc($kategoriList)) {
    $kategoriArr[] = $k;
}

?>

<!DOCTYPE html>
<html lang="id">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Data Buku - Yosal Store Admin</title>

<link rel="stylesheet" href="../assets/css/style.css">

<style>

/* Ukuran cover buku */
.data-table img.cover-admin {
    width: 42px !important;
    height: 58px !important;
    min-width: 42px !important;
    max-width: 42px !important;
    min-height: 58px !important;
    max-height: 58px !important;
    object-fit: cover !important;
    display: block !important;
    border-radius: 4px;
    border: 1px solid #ddd;
}

.data-table td.cover-cell {
    width: 75px;
    min-width: 75px;
    padding: 8px 15px;
    vertical-align: middle;
}

.data-table tbody tr {
    height: 70px;
}

.upload-info {
    font-size: 12px;
    color: #777;
    margin-top: 5px;
}

.upload-error {
    background: #ffe5e5;
    color: #b42318;
    padding: 12px;
    border-radius: 8px;
    margin-bottom: 15px;
}

</style>

</head>

<body class="admin-body">

<div class="admin-wrap">

    <?php require 'includes/sidebar.php'; ?>

    <div class="admin-main">

        <!-- HEADER -->

        <div class="page-toolbar">

            <h2>Daftar Buku</h2>

            <form class="search-admin" method="get">

                <input
                    type="text"
                    name="q"
                    placeholder="Cari judul/penulis"
                    value="<?php echo e($search); ?>"
                >

                <button type="submit">&#128269;</button>

            </form>

            <button
                class="btn-add"
                onclick="openModal('modalAdd')"
            >
                + Tambah Buku
            </button>

        </div>


        <!-- PESAN ERROR -->

        <?php if ($uploadError !== ''): ?>

            <div class="upload-error">
                <?php echo e($uploadError); ?>
            </div>

        <?php endif; ?>


        <!-- TABEL DATA BUKU -->

        <div class="admin-table-wrap">

            <table class="data-table">

                <thead>

                    <tr>
                        <th>No</th>
                        <th>Cover</th>
                        <th>Judul Buku</th>
                        <th>Penulis</th>
                        <th>Kategori</th>
                        <th>Harga</th>
                        <th>Stock</th>
                        <th>Aksi</th>
                    </tr>

                </thead>

                <tbody>

                <?php
                $no = 1;

                while ($b = mysqli_fetch_assoc($list)):
                ?>

                    <tr>

                        <td>
                            <?php echo $no++; ?>
                        </td>

                        <!-- COVER BUKU -->

                        <td class="cover-cell">

                            <img
                                src="../assets/img/<?php echo e(basename($b['gambar'])); ?>"
                                alt="<?php echo e($b['judul']); ?>"
                                class="cover-admin"
                                style="width:42px !important;height:58px !important;object-fit:cover !important;"
                                onerror="this.style.display='none';"
                            >

                        </td>

                        <td>
                            <?php echo e($b['judul']); ?>
                        </td>

                        <td>
                            <?php echo e($b['penulis']); ?>
                        </td>

                        <td>
                            <?php echo e($b['nama_kategori']); ?>
                        </td>

                        <td>
                            <?php echo formatRupiah($b['harga']); ?>
                        </td>

                        <td>
                            <?php echo (int)$b['stock']; ?>
                        </td>

                        <td>

                            <button
                                class="icon-action"
                                onclick='openEdit(<?php echo json_encode($b); ?>)'
                            >
                                &#9998;
                            </button>

                            <a
                                class="icon-action"
                                href="data_buku.php?delete=<?php echo $b['id']; ?>"
                                onclick="return confirm('Hapus buku ini?')"
                            >
                                &#128465;
                            </a>

                        </td>

                    </tr>

                <?php endwhile; ?>

                </tbody>

            </table>

        </div>

    </div>

</div>


<!-- ================================= -->
<!-- MODAL TAMBAH BUKU -->
<!-- ================================= -->

<div class="modal-overlay" id="modalAdd">

    <div class="modal-box">

        <h3>Tambah Buku</h3>

        <form
            method="post"
            action="data_buku.php"
            enctype="multipart/form-data"
        >

            <input type="hidden" name="action" value="add">


            <div class="form-group">

                <label>Judul</label>

                <input
                    type="text"
                    name="judul"
                    required
                >

            </div>


            <div class="form-group">

                <label>Penulis</label>

                <input
                    type="text"
                    name="penulis"
                    required
                >

            </div>


            <div class="form-group">

                <label>Kategori</label>

                <select
                    name="kategori_id"
                    required
                    style="width:100%;padding:12px;border-radius:8px;border:1px solid #ccc;"
                >

                    <?php foreach ($kategoriArr as $k): ?>

                        <option value="<?php echo $k['id']; ?>">
                            <?php echo e($k['nama_kategori']); ?>
                        </option>

                    <?php endforeach; ?>

                </select>

            </div>


            <div class="form-group">

                <label>Deskripsi</label>

                <textarea
                    name="deskripsi"
                    rows="3"
                ></textarea>

            </div>


            <div class="form-group">

                <label>Harga</label>

                <input
                    type="number"
                    name="harga"
                    required
                    min="0"
                >

            </div>


            <div class="form-group">

                <label>Stock</label>

                <input
                    type="number"
                    name="stock"
                    required
                    min="0"
                >

            </div>


            <!-- PILIH GAMBAR DARI LAPTOP -->

            <div class="form-group">

                <label>Cover Buku</label>

                <input
                    type="file"
                    name="gambar"
                    accept="image/jpeg,image/png,image/webp"
                    required
                >

                <div class="upload-info">
                    Pilih gambar dari laptop.
                    Format JPG, PNG, WEBP. Maksimal 5 MB.
                </div>

            </div>


            <div class="modal-actions">

                <button
                    type="button"
                    class="btn-secondary"
                    onclick="closeModal('modalAdd')"
                >
                    Batal
                </button>

                <button
                    type="submit"
                    class="btn-primary"
                    style="flex:1;"
                >
                    Simpan
                </button>

            </div>

        </form>

    </div>

</div>


<!-- ================================= -->
<!-- MODAL EDIT BUKU -->
<!-- ================================= -->

<div class="modal-overlay" id="modalEdit">

    <div class="modal-box">

        <h3>Edit Buku</h3>

        <form
            method="post"
            action="data_buku.php"
            enctype="multipart/form-data"
        >

            <input type="hidden" name="action" value="edit">

            <input type="hidden" name="id" id="edit-id">


            <div class="form-group">

                <label>Judul</label>

                <input
                    type="text"
                    name="judul"
                    id="edit-judul"
                    required
                >

            </div>


            <div class="form-group">

                <label>Penulis</label>

                <input
                    type="text"
                    name="penulis"
                    id="edit-penulis"
                    required
                >

            </div>


            <div class="form-group">

                <label>Kategori</label>

                <select
                    name="kategori_id"
                    id="edit-kategori"
                    required
                    style="width:100%;padding:12px;border-radius:8px;border:1px solid #ccc;"
                >

                    <?php foreach ($kategoriArr as $k): ?>

                        <option value="<?php echo $k['id']; ?>">
                            <?php echo e($k['nama_kategori']); ?>
                        </option>

                    <?php endforeach; ?>

                </select>

            </div>


            <div class="form-group">

                <label>Deskripsi</label>

                <textarea
                    name="deskripsi"
                    id="edit-deskripsi"
                    rows="3"
                ></textarea>

            </div>


            <div class="form-group">

                <label>Harga</label>

                <input
                    type="number"
                    name="harga"
                    id="edit-harga"
                    required
                    min="0"
                >

            </div>


            <div class="form-group">

                <label>Stock</label>

                <input
                    type="number"
                    name="stock"
                    id="edit-stock"
                    required
                    min="0"
                >

            </div>


            <!-- GANTI GAMBAR -->

            <div class="form-group">

                <label>Ganti Cover Buku (Opsional)</label>

                <input
                    type="file"
                    name="gambar"
                    accept="image/jpeg,image/png,image/webp"
                >

                <div class="upload-info">
                    Kosongkan jika tidak ingin mengganti cover.
                </div>

            </div>


            <div class="modal-actions">

                <button
                    type="button"
                    class="btn-secondary"
                    onclick="closeModal('modalEdit')"
                >
                    Batal
                </button>

                <button
                    type="submit"
                    class="btn-primary"
                    style="flex:1;"
                >
                    Simpan
                </button>

            </div>

        </form>

    </div>

</div>


<!-- JAVASCRIPT -->

<script src="../assets/js/main.js"></script>

<script>

function openEdit(data) {

    document.getElementById('edit-id').value = data.id;

    document.getElementById('edit-judul').value = data.judul;

    document.getElementById('edit-penulis').value = data.penulis;

    document.getElementById('edit-kategori').value = data.kategori_id;

    document.getElementById('edit-deskripsi').value = data.deskripsi;

    document.getElementById('edit-harga').value = data.harga;

    document.getElementById('edit-stock').value = data.stock;

    openModal('modalEdit');

}

</script>

</body>
</html>
