<?php
// =====================================================
// Helper Session & Auth - Yosal Store
// =====================================================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isLoggedIn() && $_SESSION['role'] === 'admin';
}

function currentUserId() {
    return $_SESSION['user_id'] ?? null;
}

// Wajib login (untuk halaman user seperti checkout)
function requireLogin($redirectTo = 'login.php') {
    if (!isLoggedIn()) {
        header('Location: ' . $redirectTo);
        exit;
    }
}

// Wajib login sebagai admin
function requireAdmin($redirectTo = '../login.php') {
    if (!isLoggedIn() || $_SESSION['role'] !== 'admin') {
        header('Location: ' . $redirectTo);
        exit;
    }
}

function formatRupiah($angka) {
    return 'Rp.' . number_format((float)$angka, 0, ',', '.');
}

function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}
