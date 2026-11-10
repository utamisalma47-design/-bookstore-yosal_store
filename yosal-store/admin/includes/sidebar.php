<?php $activeAdmin = $activeAdmin ?? ''; ?>
<div class="admin-sidebar">
    <div class="logo">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#1E3AA8" stroke-width="2"><path d="M2 5c3-1.5 6-1.5 9 0v14c-3-1.5-6-1.5-9 0V5z"/><path d="M22 5c-3-1.5-6-1.5-9 0v14c3-1.5 6-1.5 9 0V5z"/></svg>
        Yosal Store Admin
    </div>
    <div class="admin-nav">
        <a href="dashboard.php" class="<?php echo $activeAdmin=='dashboard'?'active':''; ?>">Dashboard</a>
        <a href="kategori.php" class="<?php echo $activeAdmin=='kategori'?'active':''; ?>">Kategori Buku</a>
        <a href="data_buku.php" class="<?php echo $activeAdmin=='buku'?'active':''; ?>">Data Buku</a>
        <a href="users.php" class="<?php echo $activeAdmin=='users'?'active':''; ?>">Users</a>
        <a href="pesanan.php" class="<?php echo $activeAdmin=='pesanan'?'active':''; ?>">Pesanan</a>
        <a href="laporan.php" class="<?php echo $activeAdmin=='laporan'?'active':''; ?>">Laporan</a>
        <a href="pesan_masuk.php" class="<?php echo $activeAdmin=='pesan_masuk'?'active':''; ?>">Pesan Masuk</a>
        <a href="pengaturan.php" class="<?php echo $activeAdmin=='pengaturan'?'active':''; ?>">Pengaturan</a>
        <a href="../logout.php">Logout</a>
    </div>
    <div class="admin-profile">
        <span class="avatar">&#128100;</span>
        Admin
    </div>
</div>
