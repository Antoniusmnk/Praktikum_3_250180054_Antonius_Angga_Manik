<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$pdo = getDBConnection();
$errors = [];

// Variabel penampung nilai form (sticky form input)
$name     = '';
$category = '';
$price    = '';
$stock    = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Verifikasi CSRF Token, tolak jika tidak valid
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($token)) {
        http_response_code(403);
        die("<div style='padding: 20px; background: #fee2e2; color: #991b1b; font-family: sans-serif; border-radius: 8px; margin: 20px auto; max-width: 600px; border: 1px solid #f87171;'>
                <h3 style='margin-top:0;'>Permintaan Ditolak!</h3>
                <p style='margin-bottom:0;'>Token keamanan CSRF tidak valid atau telah expired. Silakan kembali, muat ulang halaman, dan coba lagi.</p>
             </div>");
    }

    // 2. Sanitasi Input Data
    $name     = sanitize_input($_POST['name'] ?? '');
    $category = sanitize_input($_POST['category'] ?? '');
    $price    = sanitize_input($_POST['price'] ?? '');
    $stock    = sanitize_input($_POST['stock'] ?? '');

    // 3. Validasi Input Data dengan fungsi validate_product()
    $errors = validate_product([
        'name'     => $name,
        'category' => $category,
        'price'    => $price,
        'stock'    => $stock
    ]);

    // 4. Cek Keunikan Nama Produk dengan fungsi is_name_unique()
    if (!isset($errors['name']) && !is_name_unique($pdo, $name)) {
        $errors['name'] = 'Nama produk sudah terdaftar. Gunakan nama produk lain yang unik.';
    }

    // 5. Jika tidak ada error validasi, lakukan INSERT dengan PDO Prepared Statement
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO products (name, category, price, stock) VALUES (:name, :category, :price, :stock)");
            $stmt->execute([
                'name'     => $name,
                'category' => $category,
                'price'    => (float)$price,
                'stock'    => (int)$stock
            ]);

            // Set pesan flash notifikasi sukses
            set_flash('success', 'Produk "' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '" berhasil disimpan!');

            // Pola Post/Redirect/Get (PRG) untuk mencegah duplikasi data saat refresh
            header("Location: index.php");
            exit;

        } catch (PDOException $e) {
            // Catat log kesalahan sistem
            error_log("[" . date('Y-m-d H:i:s') . "] Create Product Error: " . $e->getMessage());

            if ($e->getCode() == 23000) {
                $errors['name'] = 'Nama produk sudah terdaftar di basis data.';
            } else {
                $errors['general'] = 'Gagal menyimpan data produk ke basis data.';
            }
        }
    }
    // Jika ada error, variabel $name, $category, $price, $stock tetap menyimpan data yang diisi user
}

$csrfToken = generate_csrf_token();
$pageTitle = "Tambah Produk";
require_once __DIR__ . '/../includes/header.php';
?>

<div class="form-card">
    <div class="form-header">
        <h2><i class="fa-solid fa-plus-circle"></i> Tambah Produk Baru</h2>
        <p style="color: var(--text-muted); font-size: 0.9rem;">Isi formulir berikut untuk menambahkan produk ke sistem.</p>
    </div>

    <!-- Alert Kesalahan Umum (jika ada) -->
    <?php if (isset($errors['general'])): ?>
        <div class="alert alert-danger">
            <div class="alert-icon"><i class="fa-solid fa-circle-exclamation"></i></div>
            <div class="alert-message"><?= htmlspecialchars($errors['general'], ENT_QUOTES, 'UTF-8') ?></div>
        </div>
    <?php endif; ?>

    <form action="create.php" method="POST" novalidate>
        <!-- Hidden input CSRF Token -->
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">

        <!-- Input Nama Produk -->
        <div class="form-group">
            <label for="name">Nama Produk <span style="color: var(--danger);">*</span></label>
            <input type="text" id="name" name="name" class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>" placeholder="Contoh: Laptop Asus ZenBook 14" required autofocus>
            <?php if (isset($errors['name'])): ?>
                <small style="color: var(--danger); font-weight: 500; margin-top: 0.3rem; display: block;">
                    <i class="fa-solid fa-circle-info"></i> <?= htmlspecialchars($errors['name'], ENT_QUOTES, 'UTF-8') ?>
                </small>
            <?php endif; ?>
        </div>

        <!-- Input Kategori -->
        <div class="form-group">
            <label for="category">Kategori <span style="color: var(--danger);">*</span></label>
            <input type="text" id="category" name="category" class="form-control <?= isset($errors['category']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($category, ENT_QUOTES, 'UTF-8') ?>" placeholder="Contoh: Elektronik, Aksesoris, Furniture" required>
            <?php if (isset($errors['category'])): ?>
                <small style="color: var(--danger); font-weight: 500; margin-top: 0.3rem; display: block;">
                    <i class="fa-solid fa-circle-info"></i> <?= htmlspecialchars($errors['category'], ENT_QUOTES, 'UTF-8') ?>
                </small>
            <?php endif; ?>
        </div>

        <!-- Input Harga & Stok -->
        <div class="form-grid-2">
            <div class="form-group">
                <label for="price">Harga (Rp) <span style="color: var(--danger);">*</span></label>
                <input type="number" id="price" name="price" step="0.01" class="form-control <?= isset($errors['price']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($price, ENT_QUOTES, 'UTF-8') ?>" placeholder="Contoh: 1500000" required>
                <?php if (isset($errors['price'])): ?>
                    <small style="color: var(--danger); font-weight: 500; margin-top: 0.3rem; display: block;">
                        <i class="fa-solid fa-circle-info"></i> <?= htmlspecialchars($errors['price'], ENT_QUOTES, 'UTF-8') ?>
                    </small>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="stock">Stok Unit <span style="color: var(--danger);">*</span></label>
                <input type="number" id="stock" name="stock" class="form-control <?= isset($errors['stock']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($stock, ENT_QUOTES, 'UTF-8') ?>" placeholder="Contoh: 10" required>
                <?php if (isset($errors['stock'])): ?>
                    <small style="color: var(--danger); font-weight: 500; margin-top: 0.3rem; display: block;">
                        <i class="fa-solid fa-circle-info"></i> <?= htmlspecialchars($errors['stock'], ENT_QUOTES, 'UTF-8') ?>
                    </small>
                <?php endif; ?>
            </div>
        </div>

        <!-- Tombol Aksi -->
        <div class="form-actions">
            <a href="index.php" class="btn btn-secondary"><i class="fa-solid fa-arrow-left"></i> Batal</a>
            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Simpan Produk</button>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
