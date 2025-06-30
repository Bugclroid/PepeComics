<?php
$pageTitle = 'Order Confirmation';
require_once '../db.php';
require_once '../helpers.php';
require_once '../models/Order.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('/pepecomics/php/views/login.php');
}

// Get order ID from URL
$orderId = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;

// Initialize Order model
$orderModel = new Order($pdo);

// Get order details
$order = $orderModel->getOrder($orderId);

// Verify order belongs to current user
if (!$order || $order['user_id'] !== $_SESSION['user_id']) {
    redirect('/pepecomics/php/views/orders.php');
}

require_once 'header.php';
?>

<main class="confirmation-page">
    <div class="confirmation-container">
        <div class="confirmation-content">
            <!-- Success Animation -->
            <div class="success-animation">
                <img src="/pepecomics/images/animations/pepe-success.gif" alt="Success" class="success-image">
                <h1 class="confirmation-title">Order Confirmed!</h1>
                <p class="confirmation-message">
                    Thank you for your order. We'll start processing it right away!
                </p>
            </div>

            <!-- Order Details -->
            <div class="order-details">
                <div class="order-header">
                    <h2>Order #<?= $orderId ?></h2>
                    <span class="order-date">
                        <?= date('F j, Y', strtotime($order['order_date'])) ?>
                    </span>
                </div>

                <div class="order-items">
                    <?php foreach ($order['items'] as $item): ?>
                        <div class="order-item">
                            <div class="item-image">
                                <img src="/pepecomics/images/products/<?= htmlspecialchars($item['image']) ?>" 
                                     alt="<?= htmlspecialchars($item['title']) ?>"
                                     loading="lazy">
                            </div>
                            <div class="item-details">
                                <h3 class="item-title">
                                    <?= htmlspecialchars($item['title']) ?>
                                </h3>
                                <p class="item-quantity">
                                    Quantity: <?= $item['quantity'] ?>
                                </p>
                                <p class="item-price">
                                    $<?= number_format($item['total_price'], 2) ?>
                                </p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="order-summary">
                    <div class="summary-line">
                        <span>Subtotal</span>
                        <span>$<?= number_format($order['total'], 2) ?></span>
                    </div>
                    <div class="summary-line">
                        <span>Shipping</span>
                        <span>Free</span>
                    </div>
                    <?php if ($order['total'] >= 50): ?>
                        <div class="summary-line discount">
                            <span>Bulk Discount (10%)</span>
                            <span>-$<?= number_format($order['total'] * 0.1, 2) ?></span>
                        </div>
                    <?php endif; ?>
                    <div class="summary-total">
                        <span>Total</span>
                        <span>
                            $<?= number_format($order['total'] >= 50 ? $order['total'] * 0.9 : $order['total'], 2) ?>
                        </span>
                    </div>
                </div>

                <div class="order-info">
                    <div class="info-section">
                        <h3>Shipping Address</h3>
                        <p><?= htmlspecialchars($order['shipping_name']) ?></p>
                        <p><?= htmlspecialchars($order['shipping_address']) ?></p>
                        <p>
                            <?= htmlspecialchars($order['shipping_city']) ?>, 
                            <?= htmlspecialchars($order['shipping_state']) ?> 
                            <?= htmlspecialchars($order['shipping_zip']) ?>
                        </p>
                    </div>

                    <div class="info-section">
                        <h3>Payment Method</h3>
                        <p><?= htmlspecialchars($order['payment_method'] ?? 'Not specified') ?></p>
                        <p class="payment-status <?= strtolower($order['payment_status'] ?? 'pending') ?>">
                            Status: <?= htmlspecialchars($order['payment_status'] ?? 'Pending') ?>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="confirmation-actions">
                <a href="/pepecomics/php/views/orders.php" class="btn btn--primary">
                    View All Orders
                    <img src="/pepecomics/images/animations/pepe-list.gif" alt="Orders" class="btn__icon">
                </a>
                <a href="/pepecomics/php/views/catalog.php" class="btn btn--secondary">
                    Continue Shopping
                    <img src="/pepecomics/images/animations/pepe-browse.gif" alt="Shop" class="btn__icon">
                </a>
            </div>
        </div>
    </div>
</main>

<?php require_once 'footer.php'; ?> 