<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$pdo = getDBConnection();
$csrfToken = generate_csrf_token();

#Parameter Pencarian
$search = isset($_GET['q']) ? trim($_GET['q']) : '';

# 1. Mengambil produk menggunakan PDO Prepared Statement
try {
    if (!empty($search)) {
        $stmt = $pdo->prepare("SELECT * FROM products WHERE name LIKE :name OR category LIKE :category ORDER BY id DESC");
        $stmt->execute([
            'name' => '%' . $search . '%',
            'category' => '%' . $search . '%'
        ]);
    } else {
        $stmt = $pdo->prepare("SELECT * FROM products ORDER BY id DESC");
        $stmt->execute();
    }
    $products = $stmt->fetchAll();

    # Query Statistik Dashboard (menggunakan Prepared Statement)
    $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM products");
    $stmtCount->execute();
    $statTotalProducts = $stmtCount->fetchColumn();

    $stmtStock = $pdo->prepare("SELECT COALESCE(SUM(stock), 0) FROM products");
    $stmtStock->execute();
    $statTotalStock = $stmtStock->fetchColumn();

    $stmtOut = $pdo->prepare("SELECT COUNT(*) FROM products WHERE stock = 0");
    $stmtOut->execute();
    $statOutOfStock = $stmtOut->fetchColumn();

    $stmtVal = $pdo->prepare("SELECT COALESCE(SUM(price * stock), 0) FROM products");
    $stmtVal->execute();
    $statTotalValue = $stmtVal->fetchColumn();

} catch (PDOException $e) {
    error_log("[" . date('Y-m-d H:i:s') . "] Index Page Query Error: " . $e->getMessage());
    set_flash('danger', 'Terjadi kesalahan sistem saat mengambil data produk.');
    $products = [];
    $statTotalProducts = $statTotalStock = $statOutOfStock = $statTotalValue = 0;
}

$pageTitle = "Daftar Produk";
require_once __DIR__ . '/../includes/header.php';
?>

<!-- Grid Statistik Dashboard -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon primary">
            <i class="fa-solid fa-boxes-stacked"></i>
        </div>
        <div class="stat-info">
            <h4>Total Produk</h4>
            <div class="stat-value"><?= htmlspecialchars(number_format($statTotalProducts), ENT_QUOTES, 'UTF-8') ?></div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon success">
            <i class="fa-solid fa-layer-group"></i>
        </div>
        <div class="stat-info">
            <h4>Total Stok Unit</h4>
            <div class="stat-value"><?= htmlspecialchars(number_format($statTotalStock), ENT_QUOTES, 'UTF-8') ?></div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon warning">
            <i class="fa-solid fa-coins"></i>
        </div>
        <div class="stat-info">
            <h4>Nilai Inventaris</h4>
            <div class="stat-value" style="font-size:1.2rem;"><?= htmlspecialchars(format_rupiah($statTotalValue), ENT_QUOTES, 'UTF-8') ?></div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon danger">
            <i class="fa-solid fa-triangle-exclamation"></i>
        </div>
        <div class="stat-info">
            <h4>Stok Habis</h4>
            <div class="stat-value"><?= htmlspecialchars(number_format($statOutOfStock), ENT_QUOTES, 'UTF-8') ?></div>
        </div>
    </div>
</div>

<!-- Section Header & Search Bar -->
<div class="section-bar">
    <h2><i class="fa-solid fa-grid-2"></i> Daftar Produk</h2>
    <form action="index.php" method="GET" class="search-box">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="text" name="q" placeholder="Cari nama atau kategori..." value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>">
        <?php if (!empty($search)): ?>
            <a href="index.php" style="color: var(--text-muted); margin-left: 0.5rem; text-decoration:none;"><i class="fa-solid fa-xmark"></i></a>
        <?php endif; ?>
    </form>
</div>

<!-- 2. Tampilan Card Grid Responsif (CSS Grid) -->
<?php if (count($products) > 0): ?>
    <div class="products-grid">
        <?php foreach ($products as $p): ?>
            <div class="product-card">
                <!-- Bagian Atas: Kategori & Status Stok -->
                <div class="card-top">
                    <span class="category-tag">
                        <i class="fa-solid fa-tag"></i> <?= htmlspecialchars($p['category'], ENT_QUOTES, 'UTF-8') ?>
                    </span>
                    <div>
                        <?php if ($p['stock'] == 0): ?>
                            <span class="badge badge-danger"><i class="fa-solid fa-xmark-circle"></i> Stok Habis</span>
                        <?php elseif ($p['stock'] < 5): ?>
                            <span class="badge badge-warning"><i class="fa-solid fa-triangle-exclamation"></i> Stok Menipis</span>
                        <?php else: ?>
                            <span class="badge badge-success"><i class="fa-solid fa-check-circle"></i> Stok Aman</span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Bagian Tengah: Nama & Harga Produk -->
                <h3 class="card-title">
                    <?= htmlspecialchars($p['name'], ENT_QUOTES, 'UTF-8') ?>
                </h3>
                
                <div class="card-price">
                    <?= htmlspecialchars(format_rupiah($p['price']), ENT_QUOTES, 'UTF-8') ?>
                </div>

                <!-- Meta Informasi Stok -->
                <div class="card-meta">
                    <span>ID: #<?= htmlspecialchars((string)$p['id'], ENT_QUOTES, 'UTF-8') ?></span>
                    <span>Stok: <strong><?= htmlspecialchars(number_format($p['stock']), ENT_QUOTES, 'UTF-8') ?> unit</strong></span>
                </div>

                <!-- Bagian Bawah: Tombol Edit & Tombol Delete Form POST dengan CSRF Token -->
                <div class="card-actions">
                    <a href="edit.php?id=<?= htmlspecialchars((string)$p['id'], ENT_QUOTES, 'UTF-8') ?>" class="btn btn-sm btn-icon-edit">
                        <i class="fa-solid fa-pen-to-square"></i> Edit
                    </a>

                    <!-- Form Delete POST Kecil dengan CSRF Token -->
                    <form action="delete.php" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus produk <?= htmlspecialchars(addslashes($p['name']), ENT_QUOTES, 'UTF-8') ?>?');" style="flex: 1; display: flex;">
                        <input type="hidden" name="id" value="<?= htmlspecialchars((string)$p['id'], ENT_QUOTES, 'UTF-8') ?>">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                        <button type="submit" class="btn btn-sm btn-icon-delete" style="width: 100%;">
                            <i class="fa-solid fa-trash-can"></i> Hapus
                        </button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <div class="card-table" style="padding: 2rem;">
        <div class="empty-state">
            <i class="fa-solid fa-box-open"></i>
            <h3>Data Produk Tidak Ditemukan</h3>
            <p><?= !empty($search) ? 'Tidak ada produk yang cocok dengan kata kunci "' . htmlspecialchars($search, ENT_QUOTES, 'UTF-8') . '".' : 'Belum ada data produk tersimpan.' ?></p>
            <a href="create.php" class="btn btn-primary" style="margin-top: 1rem;">
                <i class="fa-solid fa-plus"></i> Tambah Produk Pertama
            </a>
        </div>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
