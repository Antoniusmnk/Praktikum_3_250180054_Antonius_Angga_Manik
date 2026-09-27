<?php
/**
 * Helper & Utility Functions Application
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * 1. Generator CSRF Token untuk proteksi form POST
 *
 * @return string
 */
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * 1. Verifikasi CSRF Token
 *
 * @param string|null $token
 * @return bool
 */
function verify_csrf_token($token) {
    if (empty($token) || empty($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * 2. Sanitasi input data (trim, strip_tags, dan htmlspecialchars)
 *
 * @param mixed $data
 * @return string
 */
function sanitize_input($data) {
    if ($data === null) {
        return '';
    }
    // Trim whitespace, strip HTML tags, lalu konversi karakter khusus HTML
    return htmlspecialchars(strip_tags(trim((string)$data)), ENT_QUOTES, 'UTF-8');
}

/**
 * Alias helper sanitasi untuk kompatibilitas tampilan
 */
function sanitize($data) {
    return sanitize_input($data);
}

/**
 * 3. Validasi Data Produk
 * Kriteria:
 * - Nama produk minimal 3 karakter
 * - Kategori tidak boleh kosong
 * - Harga harus numerik > 0
 * - Stok harus numerik >= 0
 *
 * @param array $data Data input (misal $_POST)
 * @return array Array berisi pesan error jika ada yang gagal (kosong jika valid)
 */
function validate_product($data) {
    $errors = [];

    $name     = isset($data['name']) ? trim((string)$data['name']) : '';
    $category = isset($data['category']) ? trim((string)$data['category']) : '';
    $price    = isset($data['price']) ? trim((string)$data['price']) : '';
    $stock    = isset($data['stock']) ? trim((string)$data['stock']) : '';

    // Validasi Nama Produk (minimal 3 karakter)
    if (empty($name)) {
        $errors['name'] = 'Nama produk wajib diisi.';
    } elseif (mb_strlen($name, 'UTF-8') < 3) {
        $errors['name'] = 'Nama produk harus memiliki panjang minimal 3 karakter.';
    }

    // Validasi Kategori
    if (empty($category)) {
        $errors['category'] = 'Kategori produk wajib diisi.';
    }

    // Validasi Harga (harus > 0)
    if ($price === '') {
        $errors['price'] = 'Harga produk wajib diisi.';
    } elseif (!is_numeric($price) || (float)$price <= 0) {
        $errors['price'] = 'Harga produk harus berupa angka bernilai lebih besar dari 0.';
    }

    // Validasi Stok (harus >= 0)
    if ($stock === '') {
        $errors['stock'] = 'Stok produk wajib diisi.';
    } elseif (!is_numeric($stock) || (int)$stock < 0) {
        $errors['stock'] = 'Stok produk harus berupa angka bernilai 0 atau lebih.';
    }

    return $errors;
}

/**
 * 4. Pengecekan Duplikat Nama Produk (Is Name Unique)
 *
 * @param PDO $pdo Koneksi PDO
 * @param string $name Nama produk yang dicek
 * @param int|null $exclude_id ID produk yang diabaikan saat mode edit
 * @return bool Return true jika nama unik (belum pernah terpakai), false jika duplikat
 */
function is_name_unique($pdo, $name, $exclude_id = null) {
    $cleanName = trim((string)$name);
    if (empty($cleanName)) {
        return false;
    }

    if ($exclude_id !== null && (int)$exclude_id > 0) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE name = :name AND id != :id");
        $stmt->execute([
            'name' => $cleanName,
            'id'   => (int)$exclude_id
        ]);
    } else {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE name = :name");
        $stmt->execute([
            'name' => $cleanName
        ]);
    }

    // Mengembalikan true jika jumlah record == 0 (artinya unik)
    return (int)$stmt->fetchColumn() === 0;
}

/**
 * Alias helper untuk keunikan nama
 */
function is_product_name_exists($pdo, $name, $excludeId = null) {
    return !is_name_unique($pdo, $name, $excludeId);
}

/**
 * Helper pesan notifikasi flash session
 */
function set_flash($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

function get_flash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Helper format mata uang Rupiah (Rp)
 */
function format_rupiah($number) {
    return 'Rp ' . number_format((float)$number, 0, ',', '.');
}

/**
 * Helper redirect halaman
 */
function redirect($url) {
    header("Location: " . $url);
    exit;
}
