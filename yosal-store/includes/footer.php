</div><!-- /.page-wrap -->
<script src="<?php echo isset($inAdmin) ? '../assets/js/main.js' : 'assets/js/main.js'; ?>"></script>
</body>
</html>

<style>
.site-footer {
    background: #1E3A9E;
    color: #ffffff;
    padding: 40px 60px 30px;
    margin-top: 40px;
    border-top: 4px solid #3b82f6;
}
.site-footer .footer-grid {
    display: grid;
    grid-template-columns: 1.4fr 1fr 1.2fr 1fr;
    gap: 30px;
    max-width: 1200px;
    margin: 0 auto;
}
.site-footer h4 {
    font-size: 1.3rem;
    font-weight: 800;
    margin: 0 0 16px;
    color: #ffffff;
}
.site-footer p,
.site-footer li,
.site-footer a {
    color: #dbe4ff;
    text-decoration: none;
    font-size: 0.95rem;
    line-height: 1.9;
}
.site-footer a:hover {
    color: #ffffff;
    text-decoration: underline;
}
.site-footer ul {
    list-style: none;
    margin: 0;
    padding: 0;
}
.footer-bank-badges {
    display: flex;
    gap: 10px;
    margin-top: 8px;
}
.bank-badge {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 8px 14px;
    border-radius: 8px;
    font-weight: 800;
    font-size: 0.9rem;
}
.bank-badge.bni {
    background: #ffffff;
    color: #00693c;
}
.bank-badge.bni span.icon { color: #f26522; }
.bank-badge.bri {
    background: #0b5b64;
    color: #ffffff;
}
@media (max-width: 800px) {
    .site-footer .footer-grid {
        grid-template-columns: 1fr 1fr;
    }
}
</style>

<footer class="site-footer">
    <div class="footer-grid">
        <div>
            <h4>Books</h4>
            <p>Yosal Store Teman Terbaik Pencipta Buku</p>
        </div>
        <div>
            <h4>Quick Links</h4>
            <ul>
                <li><a href="index.php">Beranda</a></li>
                <li><a href="kategori.php">Kategori</a></li>
                <li><a href="contact.php">Contact</a></li>
                <li><a href="about.php">About Us</a></li>
            </ul>
        </div>
        <div>
            <h4>Contact</h4>
            <p>Email : Yosalstore22@Gmail.Com<br>
            Telpon : 0877-9579-9863</p>
        </div>
        <div>
            <h4>We Accept</h4>
            <div class="footer-bank-badges">
                <span class="bank-badge bni"><span class="icon">&#9670;</span>BNI</span>
                <span class="bank-badge bri">&#9670;BRI</span>
            </div>
        </div>
    </div>
</footer>

