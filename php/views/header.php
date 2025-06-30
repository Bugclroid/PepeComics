<?php
require_once __DIR__ . '/../helpers.php';
require_once __DIR__ . '/../models/Cart.php';
startSession();

// Get initial cart count if user is logged in
$cartCount = 0;
if (isLoggedIn()) {
    require_once __DIR__ . '/../db.php';
    $cartModel = new Cart($pdo);
    $cartCount = $cartModel->getCartItemCount($_SESSION['user_id']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' - ' : '' ?>PepeComics</title>
    <link rel="stylesheet" href="/pepecomics/css/main.css">
    <link rel="stylesheet" href="/pepecomics/css/animations.css">
    <link rel="stylesheet" href="/pepecomics/css/components/auth.css">
    <link rel="stylesheet" href="/pepecomics/css/components/cart.css">
    <link rel="stylesheet" href="/pepecomics/css/components/catalog.css">
    <link rel="stylesheet" href="/pepecomics/css/components/checkout.css">
    <link rel="stylesheet" href="/pepecomics/css/components/home.css">
    <link rel="stylesheet" href="/pepecomics/css/components/product.css">
    <link rel="stylesheet" href="/pepecomics/css/components/order-tracking.css">
    <link rel="stylesheet" href="/pepecomics/css/components/footer.css">
    <script type="module" src="/pepecomics/js/main.js"></script>
    <script type="module" src="/pepecomics/js/animations.js"></script>
    <script type="module" src="/pepecomics/js/cart.js"></script>
    <script type="module" src="/pepecomics/js/reviews.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Add utility functions -->
    <script type="module">
        import { showNotification, updateCartCount } from '/pepecomics/js/main.js';
        window.showNotification = showNotification;
        window.updateCartCount = updateCartCount;

        // Update cart count when page loads
        document.addEventListener('DOMContentLoaded', () => {
            const cartCountElement = document.querySelector('.cart-count');
            if (cartCountElement) {
                cartCountElement.textContent = '<?= $cartCount ?>';
                cartCountElement.dataset.previousCount = '<?= $cartCount ?>';
            }
        });
    </script>
</head>
<body>
    <header class="header">
        <div class="container">
            <nav class="header__nav">
                <a href="/pepecomics/index.php" class="header__logo">
                    PepeComics
                </a>

                <div class="header__search">
                    <input type="text" id="search-input" class="search-input" placeholder="Search comics...">
                    <div id="search-results" class="search-results"></div>
                </div>

                <ul class="header__menu">
                    <li><a href="/pepecomics/index.php" class="header__menu-item">Home</a></li>
                    <li><a href="/pepecomics/php/views/catalog.php" class="header__menu-item">Catalog</a></li>
                    <?php if (isLoggedIn()): ?>
                        <li>
                            <a href="/pepecomics/php/views/cart.php" class="header__menu-item cart-link">
                                Cart
                                <span class="cart-count" data-previous-count="<?= $cartCount ?>"><?= $cartCount ?></span>
                                <i class="fas fa-shopping-cart"></i>
                            </a>
                        </li>
                        <li class="dropdown">
                            <button class="header__menu-item dropdown-toggle">
                                Account
                                <i class="fas fa-user"></i>
                            </button>
                            <ul class="dropdown-menu">
                                <li><a href="/pepecomics/php/views/profile.php">Profile</a></li>
                                <li><a href="/pepecomics/php/views/orders.php">Orders</a></li>
                                <li><a href="/pepecomics/php/controllers/auth.php?action=logout">Logout</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li>
                            <a href="/pepecomics/php/views/login.php" class="header__menu-item">
                                Login/Register
                                <i class="fas fa-sign-in-alt"></i>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>

                <button id="mobile-menu-button" class="mobile-menu-button">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
            </nav>
        </div>
    </header>

    <div id="mobile-menu" class="mobile-menu" style="display: none;">
        <nav class="mobile-menu__nav">
            <ul class="mobile-menu__list">
                <li><a href="/pepecomics/index.php" class="mobile-menu__item">Home</a></li>
                <li><a href="/pepecomics/php/views/catalog.php" class="mobile-menu__item">Catalog</a></li>
                <?php if (isLoggedIn()): ?>
                    <li><a href="/pepecomics/php/views/cart.php" class="mobile-menu__item">Cart</a></li>
                    <li><a href="/pepecomics/php/views/profile.php" class="mobile-menu__item">Profile</a></li>
                    <li><a href="/pepecomics/php/views/orders.php" class="mobile-menu__item">Orders</a></li>
                    <li><a href="/pepecomics/php/controllers/auth.php?action=logout" class="mobile-menu__item">Logout</a></li>
                <?php else: ?>
                    <li><a href="/pepecomics/php/views/login.php" class="mobile-menu__item">Login/Register</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>

    <?php if ($flashMessage = getFlashMessage()): ?>
        <div class="alert alert--<?= $flashMessage['type'] ?? 'info' ?>">
            <?= htmlspecialchars($flashMessage['message']) ?>
        </div>
    <?php endif; ?>

    <main class="main">
        <div class="container page-content"> 