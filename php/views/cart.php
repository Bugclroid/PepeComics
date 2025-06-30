<?php
$pageTitle = 'Shopping Cart';
require_once '../db.php';
require_once '../helpers.php';
require_once '../models/Cart.php';
require_once '../models/Comic.php';

// Check if user is logged in
if (!isLoggedIn()) {
    setFlashMessage('Please log in to view your cart.');
    redirect('/pepecomics/php/views/login.php');
}

// Initialize models
$cartModel = new Cart($pdo);
$comicModel = new Comic($pdo);

// Get cart items
$cartItems = $cartModel->getCartItems($_SESSION['user_id']);
$cartTotal = $cartModel->getCartTotal($_SESSION['user_id']);

// Log debug information instead of displaying it
error_log("Cart Debug - User ID: " . $_SESSION['user_id']);
error_log("Cart Debug - Items: " . print_r($cartItems, true));
error_log("Cart Debug - Total: " . $cartTotal);

require_once 'header.php';
?>

<main class="cart-page">
    <div class="cart-container">
        <h1 class="cart-title">Shopping Cart</h1>

        <?php if (empty($cartItems)): ?>
            <div class="cart-empty">
                <img src="/pepecomics/images/animations/pepe-empty-cart.gif" alt="Empty Cart" class="cart-empty__image">
                <h2>Your Cart is Empty</h2>
                <p>Looks like you haven't added any comics to your cart yet.</p>
                <a href="/pepecomics/php/views/catalog.php" class="btn btn--primary">
                    Browse Comics
                    <img src="/pepecomics/images/animations/pepe-browse.gif" alt="Browse" class="btn__icon">
                </a>
            </div>
        <?php else: ?>
            <div class="cart-content">
                <div class="cart-items">
                    <?php foreach ($cartItems as $item): ?>
                        <div class="cart-item" data-comic-id="<?= $item['comic_id'] ?>">
                            <div class="cart-item__image">
                                <?php if (isset($item['image']) && $item['image']): ?>
                                    <img src="/pepecomics/images/products/<?= htmlspecialchars($item['image']) ?>" 
                                         alt="<?= htmlspecialchars($item['title']) ?>"
                                         loading="lazy">
                                <?php else: ?>
                                    <img src="/pepecomics/images/placeholder.jpg" 
                                         alt="<?= htmlspecialchars($item['title']) ?>"
                                         loading="lazy">
                                <?php endif; ?>
                            </div>
                            
                            <div class="cart-item__details">
                                <h3 class="cart-item__title">
                                    <?= htmlspecialchars($item['title']) ?>
                                </h3>
                                <p class="cart-item__author">
                                    By <?= htmlspecialchars($item['author'] ?? 'Unknown Author') ?>
                                </p>
                                <p class="cart-item__price">
                                    $<?= number_format($item['price'], 2) ?> each
                                </p>
                            </div>
                            
                            <div class="cart-item__quantity">
                                <button class="quantity-btn decrease" 
                                        aria-label="Decrease quantity"
                                        <?= $item['quantity'] <= 1 ? 'disabled' : '' ?>>
                                    -
                                </button>
                                <input type="number" 
                                       value="<?= $item['quantity'] ?>" 
                                       min="1" 
                                       max="<?= $item['stock'] ?>"
                                       class="quantity-input"
                                       data-comic-id="<?= $item['comic_id'] ?>"
                                       data-price="<?= $item['price'] ?>"
                                       aria-label="Item quantity">
                                <button class="quantity-btn increase" 
                                        aria-label="Increase quantity"
                                        <?= $item['quantity'] >= $item['stock'] ? 'disabled' : '' ?>>
                                    +
                                </button>
                            </div>
                            
                            <div class="cart-item__total">
                                $<?= number_format($item['total_price'], 2) ?>
                            </div>
                            
                            <button class="cart-item__remove" 
                                    data-comic-id="<?= $item['comic_id'] ?>"
                                    aria-label="Remove item">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="cart-summary">
                    <div class="cart-total">
                        <span>Total:</span>
                        <span class="cart-total__amount">$<?= number_format($cartTotal, 2) ?></span>
                    </div>
                    <a href="/pepecomics/php/views/checkout.php" class="btn btn--primary btn--large">
                        Proceed to Checkout
                        <img src="/pepecomics/images/animations/pepe-checkout.gif" alt="Checkout" class="btn__icon">
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</main>

<script type="module">
    import { Cart } from '/pepecomics/js/cart.js';
    
    // Initialize cart functionality
    document.addEventListener('DOMContentLoaded', () => {
        const cart = new Cart();
        
        // Add event listeners for quantity changes
        const cartItems = document.querySelector('.cart-items');
        if (cartItems) {
            cartItems.addEventListener('click', (e) => {
                const quantityBtn = e.target.closest('.quantity-btn');
                if (!quantityBtn) return;
                
                const input = quantityBtn.parentElement.querySelector('.quantity-input');
                const currentValue = parseInt(input.value);
                const isIncrease = quantityBtn.classList.contains('increase');
                const newValue = isIncrease ? currentValue + 1 : currentValue - 1;
                
                if (newValue >= parseInt(input.min) && newValue <= parseInt(input.max)) {
                    input.value = newValue;
                    cart.handleQuantityChange(input);
                }
            });
            
            // Handle manual quantity input
            cartItems.addEventListener('change', (e) => {
                const input = e.target;
                if (!input.classList.contains('quantity-input')) return;
                cart.handleQuantityChange(input);
            });
            
            // Handle item removal
            cartItems.addEventListener('click', (e) => {
                const removeBtn = e.target.closest('.cart-item__remove');
                if (!removeBtn) return;
                cart.handleRemoveFromCart(removeBtn);
            });
        }
    });
</script>

<?php require_once 'footer.php'; ?> 