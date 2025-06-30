<?php
$pageTitle = 'Checkout';
require_once '../db.php';
require_once '../helpers.php';
require_once '../models/Cart.php';
require_once '../models/Comic.php';

// Check if user is logged in
if (!isLoggedIn()) {
    setFlashMessage('Please log in to proceed with checkout.');
    redirect('/php/views/login.php');
}

// Initialize models
$cartModel = new Cart($pdo);
$comicModel = new Comic($pdo);

// Get cart items
$cartItems = $cartModel->getCartItems($_SESSION['user_id']);
$cartTotal = $cartModel->getCartTotal($_SESSION['user_id']);

// Redirect if cart is empty
if (empty($cartItems)) {
    setFlashMessage('Your cart is empty.');
    redirect('/php/views/cart.php');
}

require_once 'header.php';
?>

<main class="checkout-page">
    <div class="checkout-container">
        <h1 class="checkout-title">Checkout</h1>

        <div class="checkout-content">
            <!-- Order Summary -->
            <div class="order-summary">
                <h2 class="summary-title">Order Summary</h2>
                <div class="summary-items">
                    <?php foreach ($cartItems as $item): ?>
                        <div class="summary-item">
                            <div class="item-image">
                                <img src="/images/products/<?= htmlspecialchars($item['image']) ?>" 
                                     alt="<?= htmlspecialchars($item['title']) ?>"
                                     loading="lazy">
                            </div>
                            <div class="item-details">
                                <h3 class="item-title"><?= htmlspecialchars($item['title']) ?></h3>
                                <p class="item-quantity">Quantity: <?= $item['quantity'] ?></p>
                                <p class="item-price">$<?= number_format($item['total_price'], 2) ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="summary-totals">
                    <div class="summary-line">
                        <span>Subtotal</span>
                        <span>$<?= number_format($cartTotal, 2) ?></span>
                    </div>
                    <div class="summary-line">
                        <span>Shipping</span>
                        <span>Free</span>
                    </div>
                    <?php if ($cartTotal >= 50): ?>
                        <div class="summary-line discount">
                            <span>Bulk Discount (10%)</span>
                            <span>-$<?= number_format($cartTotal * 0.1, 2) ?></span>
                        </div>
                    <?php endif; ?>
                    <div class="summary-total">
                        <span>Total</span>
                        <span>$<?= number_format($cartTotal >= 50 ? $cartTotal * 0.9 : $cartTotal, 2) ?></span>
                    </div>
                </div>
            </div>

            <!-- Checkout Form -->
            <form id="checkout-form" class="checkout-form">
                <input type="hidden" name="action" value="create">
                <input type="hidden" name="subtotal" value="<?= htmlspecialchars($cartTotal) ?>">
                <input type="hidden" name="discount" value="<?= $cartTotal >= 50 ? ($cartTotal * 0.1) : 0 ?>">
                <input type="hidden" name="total" value="<?= $cartTotal >= 50 ? ($cartTotal * 0.9) : $cartTotal ?>">
                
                <!-- Shipping Information -->
                <section class="form-section">
                    <h2 class="section-title">Shipping Information</h2>
                    
                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <input type="text" id="name" name="name" required 
                               value="<?= isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : '' ?>">
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" required 
                                   value="<?= isset($_SESSION['user_email']) ? htmlspecialchars($_SESSION['user_email']) : '' ?>">
                        </div>
                        <div class="form-group">
                            <label for="phone">Phone</label>
                            <input type="tel" id="phone" name="phone" required 
                                   value="<?= isset($_SESSION['user_phone']) ? htmlspecialchars($_SESSION['user_phone']) : '' ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="address">Street Address</label>
                        <input type="text" id="address" name="address" required
                               value="<?= isset($_SESSION['user_address']) ? htmlspecialchars($_SESSION['user_address']) : '' ?>">
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="city">City</label>
                            <input type="text" id="city" name="city" required>
                        </div>
                        <div class="form-group">
                            <label for="state">State</label>
                            <input type="text" id="state" name="state" required>
                        </div>
                        <div class="form-group">
                            <label for="postal_code">ZIP Code</label>
                            <input type="text" id="postal_code" name="postal_code" required>
                        </div>
                    </div>
                </section>

                <!-- Payment Information -->
                <section class="form-section">
                    <h2 class="section-title">Payment Information</h2>
                    
                    <div class="form-group">
                        <label>Payment Method</label>
                        <div class="payment-methods">
                            <label class="payment-method">
                                <input type="radio" name="payment_method" value="Credit Card" required checked>
                                <img src="/pepecomics/images/icons/credit-card.svg" alt="Credit Card">
                                <span>Credit Card</span>
                            </label>
                            <label class="payment-method">
                                <input type="radio" name="payment_method" value="Debit Card">
                                <img src="/pepecomics/images/icons/debit-card.svg" alt="Debit Card">
                                <span>Debit Card</span>
                            </label>
                            <label class="payment-method">
                                <input type="radio" name="payment_method" value="UPI">
                                <img src="/pepecomics/images/icons/upi.svg" alt="UPI">
                                <span>UPI</span>
                            </label>
                            <label class="payment-method">
                                <input type="radio" name="payment_method" value="Cash">
                                <img src="/pepecomics/images/icons/cash.svg" alt="Cash on Delivery">
                                <span>Cash on Delivery</span>
                            </label>
                        </div>
                    </div>

                    <!-- Card Fields (for Credit/Debit Card) -->
                    <div id="card-fields" style="display: none;">
                        <div class="form-group">
                            <label for="card_number">Card Number</label>
                            <input type="text" id="card_number" name="card_number" 
                                   pattern="\d{16}" placeholder="1234 5678 9012 3456">
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="expiry">Expiry Date</label>
                                <input type="text" id="expiry" name="expiry" 
                                       pattern="\d{2}/\d{2}" placeholder="MM/YY">
                            </div>
                            <div class="form-group">
                                <label for="cvv">CVV</label>
                                <input type="text" id="cvv" name="cvv" 
                                       pattern="\d{3,4}" placeholder="123">
                            </div>
                        </div>
                    </div>

                    <!-- UPI Field -->
                    <div id="upi-field" style="display: none;">
                        <div class="form-group">
                            <label for="upi_id">UPI ID</label>
                            <input type="text" id="upi_id" name="upi_id" 
                                   placeholder="username@bank" pattern="[a-zA-Z0-9\.\-]{2,256}@[a-zA-Z][a-zA-Z]{2,64}">
                            <small class="help-text">Enter your UPI ID (e.g., username@upi)</small>
                        </div>
                    </div>

                    <!-- Cash/COD Message -->
                    <div id="cash-message" style="display: none;">
                        <div class="form-group">
                            <div class="info-box">
                                <p>You will need to pay <?= number_format($cartTotal >= 50 ? $cartTotal * 0.9 : $cartTotal, 2) ?> at the time of delivery.</p>
                                <p>Please keep exact change ready.</p>
                            </div>
                        </div>
                    </div>
                </section>

                <div class="form-actions">
                    <button type="submit" class="btn btn--primary place-order-btn">
                        Place Order
                        <img src="/pepecomics/images/animations/pepe-checkout.gif" alt="Checkout" class="btn__icon">
                    </button>
                    <a href="/pepecomics/php/views/cart.php" class="btn btn--secondary">
                        Back to Cart
                        <img src="/pepecomics/images/animations/pepe-back.gif" alt="Back" class="btn__icon">
                    </a>
                </div>
            </form>
        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const checkoutForm = document.getElementById('checkout-form');
    
    checkoutForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        try {
            const formData = new FormData(checkoutForm);
            const data = Object.fromEntries(formData.entries());
            
            const response = await fetch('/pepecomics/php/controllers/orders.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            });
            
            if (!response.ok) {
                throw new Error('Server returned non-JSON response');
            }
            
            const result = await response.json();
            
            if (result.success) {
                window.location.href = `/pepecomics/php/views/order-confirmation.php?order_id=${result.data.order_id}`;
            } else {
                throw new Error(result.message || 'Failed to create order');
            }
        } catch (error) {
            console.error('Error:', error);
            showNotification(error.message, 'error');
        }
    });
});
</script>

<?php require_once 'footer.php'; ?> 