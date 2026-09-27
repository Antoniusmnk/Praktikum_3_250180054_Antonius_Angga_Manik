<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$pdo = getDBConnection();
$errors = [];

// 1. Ambil ID dari GET/POST dan validasi ID berupa angka bulat positif
$rawId = $_GET['id'] ?? ($_POST['id'] ?? null);
if ($rawId === null || !is_numeric($rawId) || (int)$rawId <= 0) {
    set_flash('danger', 'ID Produk tidak valid.');
    redirect('index.php');
}
$id = (int)$rawId;

// Ambil data produk awal dari DB menggunakan Prepared Statement (SELECT ... WHERE id = ?)
try {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $product = $stmt->fetch();

    // 2. Jika produk tidak ditemukan, redirect ke index.php dengan pesan error
    if (!$product) {
        set_flash('danger', 'Data produk tidak ditemukan.');
        redirect('index.php');
    }
} catch (PDOException $e) {
    error_log("[" . date('Y-m-d H:i:s') . "] Fetch Edit Product Error: " . $e->getMessage());
    set_flash('danger', 'Terjadi kesalahan sistem saat memuat data produk.');
    redirect('index.php');
}

// 3. Inisialisasi data lama dari database (akan digantikan input user jika terjadi error validasi)
$name     = $product['name'];
$category = $product['category'];
$price    = $product['price'];
$stock    = $product['stock'];

// 4. Saat menerima POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verifikasi CSRF Token
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($token)) {
        http_response_code(403);
        die("<div style='padding: 20px; background: #fee2e2; color: #991b1b; font-family: sans-serif; border-radius: 8px; margin: 20px auto; max-width: 600px; border: 1px solid #f87171;'>
                <h3 style='margin-top:0;'>Permintaan Ditolak!</h3>
                <p style='margin-bottom:0;'>Token keamanan CSRF tidak valid atau telah expired. Silakan kembali dan muat ulang halaman.</p>
             </div>");
    }

    // Sanitasi input data
    $name     = sanitize_input($_POST['name'] ?? '');
    $category = sanitize_input($_POST['category'] ?? '');
    $price    = sanitize_input($_POST['price'] ?? '');
    $stock    = sanitize_input($_POST['stock'] ?? '');

    // Validasi input data via validate_product()
    $errors = validate_product([
        'name'     => $name,
        'category' => $category,
        'price'    => $price,
        'stock'    => $stock
    ]);

    // Pengecekan keunikan nama produk MENGEQUALIKAN ID produk yang sedang diedit (is_name_unique)
    if (!isset($errors['name']) && !is_name_unique($pdo, $name, $id)) {
        $errors['name'] = 'Nama produk sudah digunakan oleh produk lain. Gunakan nama yang unik.';
    }

    // 5. UPDATE menggunakan Prepared Statement berdasarkan ID jika validasi sukses
    if (empty($errors)) {
        try {
            $updateStmt = $pdo->prepare("UPDATE products SET name = :name, category = :category, price = :price, stock = :stock WHERE id = :id");
            $updateStmt->execute([
                'name'     => $name,
                'category' => $category,
                'price'    => (float)$price,
                'stock'    => (int)$stock,
                'id'       => $id
            ]);

            // 6. Setelah update berhasil, redirect (Post/Redirect/Get) ke index.php dengan flash message
            set_flash('success', 'Data produk "' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '" berhasil diperbarui!');
            header("Location: index.php");
            exit;

        } catch (PDOException $e) {
            error_log("[" . date('Y-m-d H:i:s') . "] Update Product Error: " . $e->getMessage());

            if ($e->getCode() == 23000) {
                $errors['name'] = 'Nama produk telah digunakan oleh produk lain.';
            } else {
                $errors['general'] = 'Gagal memperbarui data produk ke basis data.';
            }
        }
    }
}

$csrfToken = generate_csrf_token();
$pageTitle = "Edit Produk #" . $id;
require_once __DIR__ . '/../includes/header.php';
?>

<div class="form-card">
    <div class="form-header">
        <h2><i class="fa-solid fa-pen-to-square"></i> Edit Data Produk #<?= htmlspecialchars((string)$id, ENT_QUOTES, 'UTF-8') ?></h2>
        <p style="color: var(--text-muted); font-size: 0.9rem;">Perbarui rincian informasi produk di bawah ini.</p>
    </div>

    <!-- Alert Kesalahan Umum (jika ada) -->
    <?php if (isset($errors['general'])): ?>
        <div class="alert alert-danger">
            <div class="alert-icon"><i class="fa-solid fa-circle-exclamation"></i></div>
            <div class="alert-message"><?= htmlspecialchars($errors['general'], ENT_QUOTES, 'UTF-8') ?></div>
        </div>
    <?php endif; ?>

    <form action="edit.php?id=<?= htmlspecialchars((string)$id, ENT_QUOTES, 'UTF-8') ?>" method="POST" novalidate>
        <!-- Hidden input ID & CSRF Token -->
        <input type="hidden" name="id" value="<?= htmlspecialchars((string)$id, ENT_QUOTES, 'UTF-8') ?>">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">

        <!-- Input Nama Produk -->
        <div class="form-group">
            <label for="name">Nama Produk <span style="color: var(--danger);">*</span></label>
            <input type="text" id="name" name="name" class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>" required autofocus>
            <?php if (isset($errors['name'])): ?>
                <small style="color: var(--danger); font-weight: 500; margin-top: 0.3rem; display: block;">
                    <i class="fa-solid fa-circle-info"></i> <?= htmlspecialchars($errors['name'], ENT_QUOTES, 'UTF-8') ?>
                </small>
            <?php endif; ?>
        </div>

        <!-- Input Kategori -->
        <div class="form-group">
            <label for="category">Kategori <span style="color: var(--danger);">*</span></label>
            <input type="text" id="category" name="category" class="form-control <?= isset($errors['category']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($category, ENT_QUOTES, 'UTF-8') ?>" required>
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
                <input type="number" id="price" name="price" step="0.01" class="form-control <?= isset($errors['price']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars((string)$price, ENT_QUOTES, 'UTF-8') ?>" required>
                <?php if (isset($errors['price'])): ?>
                    <small style="color: var(--danger); font-weight: 500; margin-top: 0.3rem; display: block;">
                        <i class="fa-solid fa-circle-info"></i> <?= htmlspecialchars($errors['price'], ENT_QUOTES, 'UTF-8') ?>
                    </small>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="stock">Stok Unit <span style="color: var(--danger);">*</span></label>
                <input type="number" id="stock" name="stock" class="form-control <?= isset($errors['stock']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars((string)$stock, ENT_QUOTES, 'UTF-8') ?>" required>
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
            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Perbarui Produk</button>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
