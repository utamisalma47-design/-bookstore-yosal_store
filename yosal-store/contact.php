<?php
require_once 'config/db.php';
require_once 'config/auth.php';
$pageTitle = 'Contact';
$activePage = 'contact';

$success = false;
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subjek = trim($_POST['subjek'] ?? '');
    $pesan = trim($_POST['pesan'] ?? '');

    if ($nama === '' || $email === '' || $subjek === '' || $pesan === '') {
        $error = 'Semua kolom wajib diisi.';
    } else {
        $stmt = mysqli_prepare($koneksi, "INSERT INTO pesan_masuk (nama, email, subjek, pesan) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, 'ssss', $nama, $email, $subjek, $pesan);
        if (mysqli_stmt_execute($stmt)) {
            $success = true;
        } else {
            $error = 'Gagal mengirim pesan, silakan coba lagi.';
        }
    }
}

require 'includes/header.php';
?>

<h1 class="section-title">Contact</h1>
<p style="color:var(--text-gray); margin-top:-14px; margin-bottom:10px;">kami siap membantu kebutuhan bukumu</p>

<div class="contact-grid">
    <div>
        <div class="contact-item">
            <div class="icon-circle">&#9993;</div>
            <div><h4>Email</h4><p>yosalstore22@gmail.com</p></div>
        </div>
        <div class="contact-item">
            <div class="icon-circle">&#128241;</div>
            <div><h4>WhatsApp</h4><p>0880-4342-8654</p></div>
        </div>
        <div class="contact-item">
            <div class="icon-circle">&#9742;</div>
            <div><h4>Telepon</h4><p>0877-9579-9863</p></div>
        </div>
        <div class="contact-item">
            <div class="icon-circle">&#128337;</div>
            <div><h4>Jam Operasional</h4><p>Senin - Minggu<br>06.00 - 20.00 WIB</p></div>
        </div>
    </div>

    <div class="contact-form-box">
        <h3>Kirim Pesan</h3>
        <?php if ($success): ?><div class="alert alert-success">Pesan kamu berhasil terkirim, tim kami akan segera merespon.</div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-error"><?php echo e($error); ?></div><?php endif; ?>
        <form method="post" action="contact.php">
            <div class="form-group"><input type="text" name="nama" placeholder="Nama Lengkap" required value="<?php echo isset($nama) ? e($nama) : ''; ?>"></div>
            <div class="form-group"><input type="email" name="email" placeholder="Email" required value="<?php echo isset($email) ? e($email) : ''; ?>"></div>
            <div class="form-group"><input type="text" name="subjek" placeholder="Subjek" required value="<?php echo isset($subjek) ? e($subjek) : ''; ?>"></div>
            <div class="form-group">
                <label>Pesan</label>
                <textarea name="pesan" rows="5" required><?php echo isset($pesan) ? e($pesan) : ''; ?></textarea>
            </div>
            <button type="submit" class="btn-full" style="width:auto; padding:12px 28px;">Kirim Pesan</button>
        </form>
    </div>
</div>

<?php require 'includes/footer.php'; ?>
