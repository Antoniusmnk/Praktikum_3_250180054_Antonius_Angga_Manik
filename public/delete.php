<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

// Pastikan request menggunakan method POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    set_flash('danger', 'Metode request tidak diizinkan.');
    redirect('index.php');
}

// Verifikasi CSRF Token
$token = $_POST['csrf_token'] ?? '';
if (!verify_csrf_token($token)) {
    set_flash('danger', 'Token keamanan CSRF tidak valid.');
    redirect('index.php');
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if ($id <= 0) {
    set_flash('danger', 'ID Produk tidak valid untuk dihapus.');
    redirect('index.php');
}

$pdo = getDBConnection();

try {
    // Ambil nama produk terlebih dahulu untuk pesan notifikasi
    $stmtSelect = $pdo->prepare("SELECT name FROM products WHERE id = :id");
    $stmtSelect->execute(['id' => $id]);
    $productName = $stmtSelect->fetchColumn();

    if ($productName) {
        // Hapus produk dari database
        $stmtDelete = $pdo->prepare("DELETE FROM products WHERE id = :id");
        $stmtDelete->execute(['id' => $id]);

        set_flash('success', 'Produk "' . $productName . '" berhasil dihapus!');
    } else {
        set_flash('warning', 'Data produk tidak ditemukan atau sudah dihapus.');
    }
} catch (PDOException $e) {
    error_log("[" . date('Y-m-d H:i:s') . "] Delete Product Error: " . $e->getMessage());
    set_flash('danger', 'Gagal menghapus produk dari database.');
}

redirect('index.php');
