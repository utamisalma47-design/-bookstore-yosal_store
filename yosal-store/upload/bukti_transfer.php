<div id="transferBankBox" style="display:none; margin-top:12px; padding:16px; border:1px solid #dbe0ff; border-radius:8px; background:#f5f7ff;">

    <label style="font-weight:700; color:#1E3A9E; display:block; margin-bottom:8px;">Pilih Bank Tujuan</label>
    <div style="display:flex; gap:12px; margin-bottom:16px;">
        <label style="display:flex; align-items:center; gap:6px; border:1px solid #dbe0ff; padding:10px 16px; border-radius:8px; cursor:pointer;">
            <input type="radio" name="bank_tujuan" value="BNI">
            BNI
        </label>
        <label style="display:flex; align-items:center; gap:6px; border:1px solid #dbe0ff; padding:10px 16px; border-radius:8px; cursor:pointer;">
            <input type="radio" name="bank_tujuan" value="BRI">
            BRI
        </label>
    </div>

    <label style="font-weight:700; color:#1E3A9E; display:block; margin-bottom:8px;">Upload Bukti Transfer</label>
    <input type="file" name="bukti_transfer" accept=".jpg,.jpeg,.png,.pdf" style="width:100%; padding:8px; border:1px solid #dbe0ff; border-radius:8px; background:#fff;">
    <small style="color:#6b7280;">Format: JPG, PNG, atau PDF. Maksimal 5MB.</small>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var radios = document.querySelectorAll('input[name="metode_pembayaran"]');
    var box = document.getElementById('transferBankBox');
    var bankInputs = box.querySelectorAll('input[name="bank_tujuan"]');
    var fileInput = box.querySelector('input[name="bukti_transfer"]');

    function toggleBox() {
        var selected = document.querySelector('input[name="metode_pembayaran"]:checked');
        var isTransfer = selected && selected.value === 'Transfer Bank';
        box.style.display = isTransfer ? 'block' : 'none';

        // wajib diisi hanya kalau Transfer Bank yang dipilih
        bankInputs.forEach(function (r) { r.required = isTransfer; });
        fileInput.required = isTransfer;
    }

    radios.forEach(function (r) {
        r.addEventListener('change', toggleBox);
    });

    toggleBox(); 
});
</script>