<?php
require_once __DIR__ . '/functions.php';
$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? sanitize($pageTitle) . " - " : "" ?>ProdukManager</title>
    <!-- Google Fonts: Inter & Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Style CSS -->
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <header class="main-header">
        <div class="container header-container">
            <a href="index.php" class="brand-logo">
                <div class="logo-icon"><i class="fa-solid fa-box-open"></i></div>
                <span>Produk<strong>Manager</strong></span>
            </a>
            <nav class="nav-menu">
                <a href="index.php" class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'active' : '' ?>">
                    <i class="fa-solid fa-list-check"></i> Daftar Produk
                </a>
                <a href="create.php" class="btn btn-primary">
                    <i class="fa-solid fa-plus"></i> Tambah Produk
                </a>
            </nav>
        </div>
    </header>

    <main class="main-content container">
        <?php if ($flash): ?>
            <div class="alert alert-<?= sanitize($flash['type']) ?> fade-in">
                <div class="alert-icon">
                    <?php if ($flash['type'] === 'success'): ?>
                        <i class="fa-solid fa-circle-check"></i>
                    <?php elseif ($flash['type'] === 'danger'): ?>
                        <i class="fa-solid fa-circle-exclamation"></i>
                    <?php else: ?>
                        <i class="fa-solid fa-circle-info"></i>
                    <?php endif; ?>
                </div>
                <div class="alert-message">
                    <?= sanitize($flash['message']) ?>
                </div>
                <button type="button" class="alert-close" onclick="this.parentElement.remove();">&times;</button>
            </div>
        <?php endif; ?>
