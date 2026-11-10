// =====================================================
// Yosal Store - Main JS
// =====================================================

// Qty +/- pada kartu produk / cart / checkout preview
document.addEventListener('click', function (e) {
    if (e.target.matches('.qty-plus')) {
        const input = e.target.closest('.qty-box').querySelector('input');
        input.value = parseInt(input.value || 1) + 1;
        input.dispatchEvent(new Event('change'));
    }
    if (e.target.matches('.qty-minus')) {
        const input = e.target.closest('.qty-box').querySelector('input');
        const val = parseInt(input.value || 1) - 1;
        input.value = val < 1 ? 1 : val;
        input.dispatchEvent(new Event('change'));
    }
});

// Update jumlah di keranjang (cart.php) via AJAX
function updateCartQty(cartId, input) {
    const jumlah = parseInt(input.value) || 1;
    fetch('update_cart.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `cart_id=${cartId}&jumlah=${jumlah}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            document.getElementById('subtotal-' + cartId).innerText = data.subtotal_format;
            document.getElementById('cart-total').innerText = data.total_format;
            document.getElementById('cart-grand-total').innerText = data.total_format;
        }
    });
}

// Hapus item keranjang
function removeCartItem(cartId) {
    if (!confirm('Hapus buku ini dari keranjang?')) return;
    fetch('remove_cart.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `cart_id=${cartId}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            window.location.reload();
        }
    });
}

// Modal helper (dipakai di halaman admin)
function openModal(id) {
    document.getElementById(id).classList.add('show');
}
function closeModal(id) {
    document.getElementById(id).classList.remove('show');
}


// =====================================================
// Pencarian otomatis (tanpa perlu tekan Enter)
// =====================================================
(function () {
    const form  = document.querySelector('.search-box');
    const dropdown = document.getElementById('searchDropdown');
    if (!form || !dropdown) return;

    const input = form.querySelector('input[name="q"]');
    const base  = form.action.replace(/search\.php.*$/, ''); // folder root situs
    let timer = null;
    let controller = null;

    function esc(str) {
        const d = document.createElement('div');
        d.textContent = str == null ? '' : str;
        return d.innerHTML;
    }

    function hide() {
        dropdown.classList.remove('show');
        dropdown.innerHTML = '';
    }

    function render(items, keyword) {
        if (!items.length) {
            dropdown.innerHTML = '<div class="search-empty">Tidak ada buku yang ditemukan.</div>';
            dropdown.classList.add('show');
            return;
        }
        let html = items.map(function (b) {
            return '<a class="search-item" href="' + base + 'produk.php?id=' + b.id + '">' +
                '<img src="' + base + 'assets/img/' + encodeURI(b.gambar) + '" alt="" ' +
                'onerror="this.style.visibility=\'hidden\'">' +
                '<div><div class="s-title">' + esc(b.judul) + '</div>' +
                '<div class="s-author">' + esc(b.penulis) + '</div></div>' +
                '<div class="s-price">' + esc(b.harga) + '</div></a>';
        }).join('');
        html += '<a class="search-all" href="' + base + 'search.php?q=' +
                encodeURIComponent(keyword) + '">Lihat semua hasil</a>';
        dropdown.innerHTML = html;
        dropdown.classList.add('show');
    }

    function doSearch() {
        const keyword = input.value.trim();
        if (keyword.length < 2) { hide(); return; }

        if (controller) controller.abort();   // batalkan request lama
        controller = new AbortController();

        fetch(base + 'search_live.php?q=' + encodeURIComponent(keyword),
              { signal: controller.signal })
            .then(function (res) { return res.json(); })
            .then(function (items) {
                // abaikan kalau teks sudah berubah lagi
                if (input.value.trim() === keyword) render(items, keyword);
            })
            .catch(function (err) { if (err.name !== 'AbortError') hide(); });
    }

    input.addEventListener('input', function () {
        clearTimeout(timer);
        timer = setTimeout(doSearch, 250);   // debounce 250ms
    });

    input.addEventListener('focus', function () {
        if (input.value.trim().length >= 2 && !dropdown.classList.contains('show')) doSearch();
    });

    document.addEventListener('click', function (e) {
        if (!e.target.closest('.search-wrap')) hide();
    });

    input.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') hide();
    });
})();
