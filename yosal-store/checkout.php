<?php
require_once 'config/db.php';
require_once 'config/auth.php';

requireLogin();

$pageTitle = 'Checkout';
$activePage = 'kategori';

$userId = currentUserId();

// Periksa apakah checkout berasal dari Beli Sekarang
$buyNow = isset($_GET['mode']) &&
          $_GET['mode'] === 'buy_now' &&
          isset($_SESSION['buy_now']);

$rows = [];
$total = 0;

if ($buyNow) {

    // Ambil hanya buku yang dipilih
    $bukuId = (int)$_SESSION['buy_now']['buku_id'];
    $jumlah = (int)$_SESSION['buy_now']['jumlah'];

    $stmt = mysqli_prepare(
        $koneksi,
        "SELECT * FROM buku WHERE id = ?"
    );

    mysqli_stmt_bind_param($stmt, 'i', $bukuId);
    mysqli_stmt_execute($stmt);

    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    if (!$row || $jumlah <= 0 || $jumlah > $row['stock']) {
        unset($_SESSION['buy_now']);
        header('Location: cart.php');
        exit;
    }

    $row['jumlah'] = $jumlah;
    $row['subtotal'] = $row['harga'] * $jumlah;

    $rows[] = $row;
    $total = $row['subtotal'];

} else {

    // Checkout normal dari keranjang
    $stmt = mysqli_prepare(
        $koneksi,
        "SELECT c.id AS cart_id, c.jumlah, b.*
         FROM cart c
         JOIN buku b ON c.buku_id = b.id
         WHERE c.user_id = ?"
    );

    mysqli_stmt_bind_param($stmt, 'i', $userId);
    mysqli_stmt_execute($stmt);

    $items = mysqli_stmt_get_result($stmt);

    while ($row = mysqli_fetch_assoc($items)) {
        $row['subtotal'] = $row['harga'] * $row['jumlah'];
        $total += $row['subtotal'];
        $rows[] = $row;
    }
}

if (empty($rows)) {
    header('Location: cart.php');
    exit;
}

// Ambil data user
$userStmt = mysqli_prepare(
    $koneksi,
    "SELECT * FROM users WHERE id = ?"
);

mysqli_stmt_bind_param($userStmt, 'i', $userId);
mysqli_stmt_execute($userStmt);

$user = mysqli_fetch_assoc(mysqli_stmt_get_result($userStmt));

require 'includes/header.php';
?>

<style>
.addr-label { display:block; font-size:13px; font-weight:600; color:#1E3AA8; margin:12px 0 6px; }
#addrEdit textarea, #addrEdit input[type="text"] { width:100%; box-sizing:border-box; padding:10px 12px; border:1px solid #ccc;
    border-radius:8px; font-size:14px; font-family:inherit; outline:none; }
#addrEdit textarea { resize:vertical; }
#addrEdit textarea:focus, #addrEdit input[type="text"]:focus { border-color:#2F5AF3; }
.addr-msg { color:#b91c1c; font-size:13px; min-height:18px; margin-top:8px; }
.addr-actions { display:flex; gap:10px; margin-top:6px; }
.btn-ghost { background:#fff; border:1px solid #ccc; border-radius:8px; padding:10px 18px; cursor:pointer; font-size:14px; }
.btn-ghost:hover { background:#f3f4f6; }
</style>

<h1 class="section-title">Detail Pembelian</h1>

<?php if (isset($_GET['error'])): ?>
<div style="background:#fee2e2;color:#991b1b;padding:12px;border-radius:8px;margin-bottom:16px;">
    Terjadi kesalahan. Silakan periksa kembali data pembayaran.
</div>
<?php endif; ?>

<form method="post"
      action="process_checkout.php"
      enctype="multipart/form-data">

<input type="hidden" name="mode"
       value="<?php echo $buyNow ? 'buy_now' : 'cart'; ?>">

<div class="checkout-grid">

    <div>

        <div class="checkout-box" id="addrBox">
            <h3>
                Alamat Pengiriman
                <span class="ubah" id="btnUbahAlamat">Ubah</span>
            </h3>

            <div class="addr-name">
                <?php echo e($user['nama']); ?>
            </div>

            <!-- Mode tampil -->
            <div class="addr-line" id="addrView">
                <span id="addrText"><?php echo e($user['alamat'] ?: 'Belum ada alamat tersimpan'); ?></span>
                <br>
                <span id="telpText"><?php echo e($user['no_telp'] ?: ''); ?></span>
            </div>

            <!-- Mode edit (tanpa <form> karena sudah di dalam form checkout) -->
            <div id="addrEdit" style="display:none;">
                <label class="addr-label" for="inAlamat">Alamat lengkap</label>
                <textarea id="inAlamat" rows="3" maxlength="255"
                          placeholder="Jalan, nomor rumah, kelurahan, kecamatan, kota, kode pos"><?php echo e($user['alamat']); ?></textarea>

                <label class="addr-label" for="inTelp">No. Telepon</label>
                <input type="text" id="inTelp" maxlength="30"
                       placeholder="08xxxxxxxxxx"
                       value="<?php echo e($user['no_telp']); ?>">

                <div id="addrMsg" class="addr-msg"></div>

                <div class="addr-actions">
                    <button type="button" class="btn-primary" id="btnSimpanAlamat">Simpan</button>
                    <button type="button" class="btn-ghost" id="btnBatalAlamat">Batal</button>
                </div>
            </div>
        </div>

        <div class="checkout-box">
            <h3>Metode Pengiriman</h3>

            <label class="radio-option">
                <span class="label-left">
                    <input type="radio"
                           name="metode_pengiriman"
                           value="Reguler (2-4 Hari)"
                           data-ongkir="0"
                           checked>
                    Reguler (2-4 Hari)
                </span>
                <span class="price-tag">Gratis</span>
            </label>

            <label class="radio-option">
                <span class="label-left">
                    <input type="radio"
                           name="metode_pengiriman"
                           value="Express (1-2 Hari)"
                           data-ongkir="15000">
                    Express (1-2 Hari)
                </span>
                <span class="price-tag">Rp.15.000</span>
            </label>
        </div>

        <div class="checkout-box">
            <h3>Metode Pembayaran</h3>

            <label class="radio-option">
                <span class="label-left">
                    <input type="radio"
                           name="metode_pembayaran"
                           value="COD"
                           checked>
                    COD
                </span>
            </label>

            <label class="radio-option">
                <span class="label-left">
                    <input type="radio"
                           name="metode_pembayaran"
                           value="Transfer Bank">
                    Transfer Bank
                </span>
            </label>

            <div id="transferBankBox"
                 style="display:none;margin-top:12px;padding:16px;border:1px solid #dbe0ff;border-radius:8px;background:#f5f7ff;">

                <label style="font-weight:700;color:#1E3A9E;display:block;margin-bottom:8px;">
                    Pilih Bank Tujuan
                </label>

                <div style="display:flex;gap:12px;margin-bottom:16px;">

                    <label>
                        <input type="radio" name="bank_tujuan" value="BNI">
                        BNI
                    </label>

                    <label>
                        <input type="radio" name="bank_tujuan" value="BRI">
                        BRI
                    </label>
             <!-- Informasi Rekening Toko -->
                <div id="infoRekening" class="info-rekening">

                   <h4>Informasi Rekening Toko</h4>

                   <p>Silakan transfer ke rekening berikut:</p>

                 <div class="rekening-detail">
                     <span id="namaBank">BNI</span>

                      <h3 id="nomorRekening">1234567890</h3>

                       <p>
                         Atas Nama:
                         <strong id="namaPemilik">YosalStore</strong>
                     </p>
                 </div>

</div>

                </div>

                <label style="font-weight:700;color:#1E3A9E;display:block;margin-bottom:8px;">
                    Upload Bukti Transfer
                </label>

                <input type="file"
                       name="bukti_transfer"
                       accept=".jpg,.jpeg,.png,.pdf"
                       style="width:100%;">

                <small style="color:#6b7280;">
                    Format JPG, PNG, atau PDF. Maksimal 5MB.
                </small>

            </div>
        </div>

    </div>

    <div class="checkout-box">

        <h3>Rincian Pesanan</h3>

        <?php foreach ($rows as $r): ?>

        <div class="checkout-item">

            <img src="assets/img/<?php echo e($r['gambar']); ?>"
                 alt="<?php echo e($r['judul']); ?>">

            <div>
                <h4><?php echo e($r['judul']); ?></h4>

                <div class="author">
                    <?php echo e($r['penulis']); ?>
                    &times;
                    <?php echo (int)$r['jumlah']; ?>
                </div>
            </div>

            <div class="price">
                <?php echo formatRupiah($r['subtotal']); ?>
            </div>

        </div>

        <?php endforeach; ?>

        <div class="divider"></div>

        <div class="summary-row">
            <span>Total Harga (<?php echo count($rows); ?> item)</span>
            <span><?php echo formatRupiah($total); ?></span>
        </div>

        <div class="summary-row">
            <span>Ongkos Kirim</span>
            <span id="ongkir-val">Rp.0</span>
        </div>

        <div class="summary-row total">
            <span>Total Pembayaran</span>
            <span id="grand-total">
                <?php echo formatRupiah($total); ?>
            </span>
        </div>

        <input type="hidden"
               name="ongkos_kirim"
               id="input-ongkir"
               value="0">

        <button type="submit"
                class="btn-primary"
                style="width:100%;margin-top:16px;">
            Bayar Sekarang
        </button>

    </div>

</div>
</form>

<script>

// Ongkos kirim
document.querySelectorAll('input[name="metode_pengiriman"]').forEach(function(radio) {

    radio.addEventListener('change', function() {

        const ongkir = parseInt(this.dataset.ongkir) || 0;

        document.getElementById('input-ongkir').value = ongkir;

        document.getElementById('ongkir-val').innerText =
            'Rp.' + ongkir.toLocaleString('id-ID');

        const total = <?php echo (int)$total; ?> + ongkir;

        document.getElementById('grand-total').innerText =
            'Rp.' + total.toLocaleString('id-ID');

    });

});

// Transfer Bank
const radios = document.querySelectorAll('input[name="metode_pembayaran"]');
const box = document.getElementById('transferBankBox');

const bankInputs = box.querySelectorAll('input[name="bank_tujuan"]');
const fileInput = box.querySelector('input[name="bukti_transfer"]');

function toggleBox() {

    const selected = document.querySelector(
        'input[name="metode_pembayaran"]:checked'
    );

    const isTransfer = selected.value === 'Transfer Bank';

    box.style.display = isTransfer ? 'block' : 'none';

    bankInputs.forEach(function(input) {
        input.required = isTransfer;
    });

    fileInput.required = isTransfer;
}

radios.forEach(function(radio) {
    radio.addEventListener('change', toggleBox);
});

toggleBox();

// ===== Alamat pengiriman: ubah & simpan via AJAX =====
(function () {
    const view   = document.getElementById('addrView');
    const edit   = document.getElementById('addrEdit');
    const btnUbah   = document.getElementById('btnUbahAlamat');
    const btnSimpan = document.getElementById('btnSimpanAlamat');
    const btnBatal  = document.getElementById('btnBatalAlamat');
    const inAlamat  = document.getElementById('inAlamat');
    const inTelp    = document.getElementById('inTelp');
    const msg       = document.getElementById('addrMsg');
    const addrText  = document.getElementById('addrText');
    const telpText  = document.getElementById('telpText');

    let saved = { alamat: inAlamat.value, telp: inTelp.value };

    function openEdit() {
        view.style.display = 'none';
        edit.style.display = 'block';
        btnUbah.style.display = 'none';
        msg.textContent = '';
        inAlamat.focus();
    }
    function closeEdit() {
        edit.style.display = 'none';
        view.style.display = 'block';
        btnUbah.style.display = '';
    }

    btnUbah.addEventListener('click', openEdit);

    btnBatal.addEventListener('click', function () {
        inAlamat.value = saved.alamat;
        inTelp.value   = saved.telp;
        closeEdit();
    });

    btnSimpan.addEventListener('click', function () {
        const alamat = inAlamat.value.trim();
        const telp   = inTelp.value.trim();

        if (alamat === '') {
            msg.textContent = 'Alamat tidak boleh kosong.';
            return;
        }

        btnSimpan.disabled = true;
        msg.textContent = 'Menyimpan...';

        const body = new URLSearchParams({ alamat: alamat, no_telp: telp });

        fetch('update_alamat.php', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: body
        })
        .then(function (res) { return res.json(); })
        .then(function (data) {
            if (!data.success) {
                msg.textContent = data.message || 'Gagal menyimpan alamat.';
                return;
            }
            saved = { alamat: data.alamat, telp: data.no_telp };
            addrText.textContent = data.alamat;
            telpText.textContent = data.no_telp;
            closeEdit();
        })
        .catch(function () {
            msg.textContent = 'Terjadi kesalahan jaringan. Coba lagi.';
        })
        .finally(function () {
            btnSimpan.disabled = false;
        });
    });

    // Cegah checkout kalau alamat masih kosong / sedang diedit
    document.querySelector('form[action="process_checkout.php"]')
        .addEventListener('submit', function (e) {
            if (edit.style.display === 'block') {
                e.preventDefault();
                msg.textContent = 'Simpan atau batalkan perubahan alamat dulu.';
                edit.scrollIntoView({ behavior: 'smooth', block: 'center' });
                return;
            }
            if (saved.alamat.trim() === '') {
                e.preventDefault();
                alert('Isi alamat pengiriman dulu ya (klik "Ubah").');
                openEdit();
            }
        });
})();

</script>

<?php require 'includes/footer.php'; ?>