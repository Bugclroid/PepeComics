<?php
require_once __DIR__ . '/../../../php/db.php';
require_once __DIR__ . '/../../../php/helpers.php';

startSession();

// Check admin access for all admin pages
if (!isLoggedIn() || !isAdmin()) {
    setFlashMessage('Access denied. Admin privileges required.', 'error');
    header('Location: /pepecomics/php/views/login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PepeComics Admin Panel</title>
    
    <!-- Stylesheets -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="/pepecomics/admin/css/admin.css">
    
    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" defer></script>
    <script src="/pepecomics/admin/js/admin.js" defer></script>
</head>
<body class="admin-panel">
    <!-- Header -->
    <header class="admin-header">
        <div class="container">
            <div class="admin-nav">
                <!-- Logo -->
                <a href="/pepecomics/admin/php/views/dashboard.php" class="admin-logo">
                    <i class="fas fa-laugh-wink"></i> PepeComics Admin
                </a>
                
                <!-- Desktop Menu -->
                <nav class="admin-menu d-none d-md-flex">
                    <a href="/pepecomics/admin/php/views/dashboard.php" class="menu-item">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                    <a href="/pepecomics/admin/php/views/comics.php" class="menu-item">
                        <i class="fas fa-book-open"></i> Comics
                    </a>
                    <a href="/pepecomics/admin/php/views/orders.php" class="menu-item">
                        <i class="fas fa-shopping-cart"></i> Orders
                    </a>
                    <a href="/pepecomics/admin/php/views/users.php" class="menu-item">
                        <i class="fas fa-users"></i> Users
                    </a>
                    <a href="/pepecomics/admin/php/views/categories.php" class="menu-item">
                        <i class="fas fa-tags"></i> Categories
                    </a>
                    
                    <!-- User Dropdown -->
                    <div class="dropdown">
                        <button class="dropdown-toggle menu-item">
                            <i class="fas fa-user-circle"></i>
                            <?= htmlspecialchars($_SESSION['user_name']) ?>
                        </button>
                        <div class="dropdown-menu">
                            <a href="/pepecomics/admin/php/views/profile.php">
                                <i class="fas fa-user"></i> Profile
                            </a>
                            <a href="/pepecomics/admin/php/views/settings.php">
                                <i class="fas fa-cog"></i> Settings
                            </a>
                            <a href="/pepecomics/php/controllers/auth.php?action=logout">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>
                        </div>
                    </div>
                </nav>
                
                <!-- Mobile Menu Button -->
                <button class="mobile-menu-button d-md-none">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
            </div>
        </div>
    </header>
    
    <!-- Mobile Menu -->
    <div class="mobile-menu d-md-none">
        <nav class="mobile-nav">
            <ul>
                <li>
                    <a href="/pepecomics/admin/php/views/dashboard.php">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                </li>
                <li>
                    <a href="/pepecomics/admin/php/views/comics.php">
                        <i class="fas fa-book-open"></i> Comics
                    </a>
                </li>
                <li>
                    <a href="/pepecomics/admin/php/views/orders.php">
                        <i class="fas fa-shopping-cart"></i> Orders
                    </a>
                </li>
                <li>
                    <a href="/pepecomics/admin/php/views/users.php">
                        <i class="fas fa-users"></i> Users
                    </a>
                </li>
                <li>
                    <a href="/pepecomics/admin/php/views/categories.php">
                        <i class="fas fa-tags"></i> Categories
                    </a>
                </li>
                <li>
                    <a href="/pepecomics/admin/php/views/profile.php">
                        <i class="fas fa-user"></i> Profile
                    </a>
                </li>
                <li>
                    <a href="/pepecomics/admin/php/views/settings.php">
                        <i class="fas fa-cog"></i> Settings
                    </a>
                </li>
                <li>
                    <a href="/pepecomics/php/controllers/auth.php?action=logout">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </li>
            </ul>
        </nav>
    </div>
    
    <!-- Flash Messages -->
    <?php if (isset($_SESSION['flash_message'])): ?>
        <div class="flash-message flash-<?= $_SESSION['flash_type'] ?? 'info' ?>">
            <?= htmlspecialchars($_SESSION['flash_message']) ?>
        </div>
        <?php
        unset($_SESSION['flash_message']);
        unset($_SESSION['flash_type']);
        ?>
    <?php endif; ?>