<?php
// Pastikan config sudah di-include sebelum file ini dipanggil
if (!isset($koneksi)) { require_once __DIR__ . '/../config/db.php'; }
if (!function_exists('isLoggedIn')) { require_once __DIR__ . '/../config/auth.php'; }

$cartCount = 0;
if (isLoggedIn() && $_SESSION['role'] === 'user') {
    $q = mysqli_query($koneksi, "SELECT COALESCE(SUM(jumlah),0) as total FROM cart WHERE user_id = " . (int)currentUserId());
    $cartCount = mysqli_fetch_assoc($q)['total'];
}
$activePage = $activePage ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo isset($pageTitle) ? e($pageTitle) . ' - Yosal Store' : 'Yosal Store'; ?></title>
<?php $cssPath = isset($inAdmin) ? '../assets/css/style.css' : 'assets/css/style.css'; ?>
<link rel="stylesheet" href="<?php echo $cssPath . '?v=' . @filemtime(__DIR__ . '/../assets/css/style.css'); ?>">
<style>
/* Live search dropdown */
.search-wrap { position: relative; }
.search-dropdown { display:none; position:absolute; top:calc(100% + 6px); right:0; width:380px; max-width:90vw;
    background:#fff; border:1px solid #e5e7eb; border-radius:10px; box-shadow:0 8px 24px rgba(0,0,0,.14);
    z-index:9999; overflow:hidden; }
.search-dropdown.show { display:block; }
.search-item { display:flex; align-items:center; gap:12px; padding:10px 14px; color:#1a1a2e; text-decoration:none; }
.search-item:hover { background:#f5f7ff; }
.search-item img { width:36px; height:50px; object-fit:cover; border-radius:4px; background:#eee; flex-shrink:0; }
.search-item .s-info { flex:1; min-width:0; }
.s-title { font-size:14px; font-weight:600; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.s-author { font-size:12px; color:#555; }
.s-price { font-size:13px; font-weight:700; color:#1E3AA8; white-space:nowrap; }
.search-empty { padding:14px; font-size:14px; color:#555; }
.search-all { display:block; text-align:center; padding:10px; font-size:13px; font-weight:600; color:#2F5AF3; border-top:1px solid #eee; text-decoration:none; }
.search-all:hover { background:#f5f7ff; }
</style>
</head>
<body>
<div class="page-wrap">
    <nav class="navbar">
        <a href="index.php" class="logo">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#1E3AA8" stroke-width="2"><path d="M2 5c3-1.5 6-1.5 9 0v14c-3-1.5-6-1.5-9 0V5z"/><path d="M22 5c-3-1.5-6-1.5-9 0v14c3-1.5 6-1.5 9 0V5z"/></svg>
            Yosal Store
        </a>
        <ul class="nav-links">
            <li><a href="index.php" class="<?php echo $activePage=='beranda'?'active':''; ?>">Beranda</a></li>
            <li><a href="kategori.php" class="<?php echo $activePage=='kategori'?'active':''; ?>">Kategori</a></li>
            <li><a href="contact.php" class="<?php echo $activePage=='contact'?'active':''; ?>">Contact</a></li>
            <li><a href="about.php" class="<?php echo $activePage=='about'?'active':''; ?>">About Us</a></li>
        </ul>
        <div class="nav-right">
            <div class="search-wrap">
                <form class="search-box" action="search.php" method="get" autocomplete="off">
                    <input type="text" name="q" placeholder="Cari Buku" value="<?php echo isset($_GET['q']) ? e($_GET['q']) : ''; ?>">
                    <button type="submit">&#128269;</button>
                </form>
                <div class="search-dropdown" id="searchDropdown"></div>
            </div>
            <script>
(function () {
    var form = document.querySelector('.search-box');
    var dropdown = document.getElementById('searchDropdown');
    if (!form || !dropdown) return;
    var input = form.querySelector('input[name="q"]');
    var base = form.action.replace(/search\.php.*$/, '');
    var timer = null, controller = null;

    function esc(t) { var d = document.createElement('div'); d.textContent = t == null ? '' : t; return d.innerHTML; }
    function hide() { dropdown.classList.remove('show'); dropdown.innerHTML = ''; }

    function render(items, keyword) {
        if (!items.length) {
            dropdown.innerHTML = '<div class="search-empty">Tidak ada buku yang ditemukan.</div>';
            dropdown.classList.add('show');
            return;
        }
        var html = items.map(function (b) {
            return '<a class="search-item" href="' + base + 'produk.php?id=' + b.id + '">' +
                '<img src="' + base + 'assets/img/' + encodeURI(b.gambar) + '" alt="" onerror="this.style.visibility=\'hidden\'">' +
                '<div class="s-info"><div class="s-title">' + esc(b.judul) + '</div>' +
                '<div class="s-author">' + esc(b.penulis) + '</div></div>' +
                '<div class="s-price">' + esc(b.harga) + '</div></a>';
        }).join('');
        html += '<a class="search-all" href="' + base + 'search.php?q=' + encodeURIComponent(keyword) + '">Lihat semua hasil</a>';
        dropdown.innerHTML = html;
        dropdown.classList.add('show');
    }

    function doSearch() {
        var keyword = input.value.trim();
        if (keyword.length < 1) { hide(); return; }
        if (controller) controller.abort();
        controller = new AbortController();
        fetch(base + 'search_live.php?q=' + encodeURIComponent(keyword), { signal: controller.signal })
            .then(function (r) { return r.json(); })
            .then(function (items) { if (input.value.trim() === keyword) render(items, keyword); })
            .catch(function (err) { if (err.name !== 'AbortError') hide(); });
    }

    input.addEventListener('input', function () { clearTimeout(timer); timer = setTimeout(doSearch, 150); });
    input.addEventListener('focus', function () { if (input.value.trim().length >= 1) doSearch(); });
    input.addEventListener('keydown', function (e) { if (e.key === 'Escape') hide(); });
    document.addEventListener('click', function (e) { if (!e.target.closest('.search-wrap')) hide(); });
})();
</script>
            <?php if (isLoggedIn() && $_SESSION['role'] === 'user'): ?>
                <a href="wishlist.php" class="icon-link" title="Wishlist">&#9825;</a>
                <a href="cart.php" class="icon-link" title="Keranjang">
                    &#128722;
                    <?php if ($cartCount > 0): ?><span class="icon-badge"><?php echo $cartCount; ?></span><?php endif; ?>
                </a>
                <a href="profile.php" class="user-chip">&#128100; <?php echo e($_SESSION['nama']); ?></a>
                <a href="logout.php" class="login-link" title="Logout">Keluar</a>
            <?php elseif (isLoggedIn() && $_SESSION['role'] === 'admin'): ?>
                <a href="admin/dashboard.php" class="login-link">&#128274; Dashboard Admin</a>
                <a href="logout.php" class="login-link">Keluar</a>
            <?php else: ?>
                <a href="wishlist.php" class="icon-link" title="Wishlist">&#9825;</a>
                <a href="cart.php" class="icon-link" title="Keranjang">&#128722;</a>
                <a href="login.php" class="login-link">Login &rarr;</a>
            <?php endif; ?>
        </div>
    </nav>

    
