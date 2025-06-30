<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../helpers.php';
require_once __DIR__ . '/../models/Order.php';

startSession();

// Redirect if not logged in
if (!isLoggedIn()) {
    setFlashMessage('Please log in to view your orders.', 'error');
    header('Location: /pepecomics/php/views/login.php');
    exit();
}

// Get user's orders
$orderModel = new Order($pdo);
$orders = $orderModel->getUserOrders($_SESSION['user_id']);

// Pre-fetch order items for each order to avoid AJAX calls
$orderItems = [];
foreach ($orders as $order) {
    $orderItems[$order['order_id']] = $orderModel->getOrderItems($order['order_id']);
}

$pageTitle = 'My Orders';
require_once 'header.php';
?>

<div class="orders-container">
    <h1 class="orders-title">My Orders</h1>

    <?php if (empty($orders)): ?>
        <div class="orders-empty">
            <p>You haven't placed any orders yet.</p>
            <a href="/pepecomics/php/views/catalog.php" class="btn btn-primary">Browse Comics</a>
        </div>
    <?php else: ?>
        <div class="orders-list">
            <?php foreach ($orders as $order): ?>
                <div class="order-card" data-order-id="<?= htmlspecialchars($order['order_id']) ?>">
                    <div class="order-header">
                        <div class="order-info">
                            <h3>Order #<?= htmlspecialchars($order['order_id']) ?></h3>
                            <p class="order-date">
                                Placed on: <?= date('F j, Y', strtotime($order['order_date'])) ?>
                            </p>
                        </div>
                        <div class="order-total">
                            <p>Total: $<?= number_format($order['total'], 2) ?></p>
                        </div>
                    </div>

                    <div class="order-details" style="display: none;">
                        <div class="order-items">
                            <h4>Order Items</h4>
                            <div class="items-list">
                                <?php if (!empty($orderItems[$order['order_id']])): ?>
                                    <?php foreach ($orderItems[$order['order_id']] as $item): ?>
                                        <div class="order-item">
                                            <div class="item-info">
                                                <h5><?= htmlspecialchars($item['title']) ?></h5>
                                                <p>By <?= htmlspecialchars($item['author']) ?></p>
                                            </div>
                                            <div class="item-details">
                                                <p>Quantity: <?= htmlspecialchars($item['quantity']) ?></p>
                                                <p>Price: $<?= number_format($item['total_price'] / $item['quantity'], 2) ?></p>
                                                <p>Total: $<?= number_format($item['total_price'], 2) ?></p>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p class="no-items">No items found for this order.</p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <?php
                        // Parse shipping address
                        $shippingAddress = $order['shipping_address'] ?? '';
                        $addressParts = array_map('trim', explode(',', $shippingAddress));
                        $address = $addressParts[0] ?? '';
                        $city = $addressParts[1] ?? '';
                        $stateZip = isset($addressParts[2]) ? explode(' ', trim($addressParts[2]), 2) : ['', ''];
                        $state = $stateZip[0] ?? '';
                        $postalCode = $stateZip[1] ?? '';
                        ?>

                        <div class="shipping-info">
                            <h4>Shipping Address</h4>
                            <p>
                                <?= htmlspecialchars($address) ?><br>
                                <?php if ($city): ?>
                                    <?= htmlspecialchars($city) ?><br>
                                <?php endif; ?>
                                <?php if ($state || $postalCode): ?>
                                    <?= htmlspecialchars($state) ?> <?= htmlspecialchars($postalCode) ?>
                                <?php endif; ?>
                            </p>
                        </div>

                        <div class="order-actions">
                            <a href="/pepecomics/php/views/order-tracking.php?order_id=<?= htmlspecialchars($order['order_id']) ?>" 
                               class="btn btn-secondary">Track Order</a>
                        </div>
                    </div>

                    <button class="btn btn-text toggle-details">
                        <span class="show-text">Show Details</span>
                        <span class="hide-text" style="display: none;">Hide Details</span>
                    </button>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<style>
.orders-container {
    max-width: 1000px;
    margin: 2rem auto;
    padding: 0 1rem;
}

.orders-title {
    text-align: center;
    margin-bottom: 2rem;
    color: #333;
}

.orders-empty {
    text-align: center;
    padding: 3rem;
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.orders-empty p {
    margin-bottom: 1.5rem;
    color: #666;
}

.order-card {
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    margin-bottom: 1.5rem;
    padding: 1.5rem;
}

.order-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1rem;
}

.order-info h3 {
    margin: 0;
    color: #333;
}

.order-date {
    color: #666;
    font-size: 0.9rem;
    margin: 0.5rem 0;
}

.order-total {
    font-weight: bold;
    color: #2196F3;
}

.order-details {
    border-top: 1px solid #eee;
    padding-top: 1.5rem;
    margin-top: 1rem;
}

.order-items,
.shipping-info {
    margin-bottom: 1.5rem;
}

.order-items h4,
.shipping-info h4 {
    color: #444;
    margin-bottom: 1rem;
}

.order-item {
    display: flex;
    justify-content: space-between;
    padding: 1rem;
    border: 1px solid #eee;
    border-radius: 4px;
    margin-bottom: 0.5rem;
    background-color: #f9f9f9;
}

.item-info h5 {
    margin: 0;
    color: #333;
    font-size: 1.1rem;
}

.item-info p {
    color: #666;
    margin: 0.5rem 0;
}

.item-details {
    text-align: right;
}

.item-details p {
    margin: 0.25rem 0;
    color: #666;
}

.shipping-info p {
    color: #666;
    line-height: 1.5;
}

.order-actions {
    margin-top: 1.5rem;
}

.btn {
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 1rem;
    transition: all 0.3s ease;
    text-decoration: none;
    display: inline-block;
}

.btn-primary {
    background-color: #4CAF50;
    color: white;
}

.btn-primary:hover {
    background-color: #45a049;
}

.btn-secondary {
    background-color: #2196F3;
    color: white;
}

.btn-secondary:hover {
    background-color: #1e88e5;
}

.btn-text {
    background: none;
    color: #2196F3;
    padding: 0.5rem;
    text-decoration: underline;
    width: 100%;
    text-align: center;
    margin-top: 1rem;
}

.btn-text:hover {
    color: #1e88e5;
}

.no-items {
    text-align: center;
    padding: 1rem;
    color: #666;
    font-style: italic;
}

@media (max-width: 768px) {
    .order-header {
        flex-direction: column;
    }

    .order-total {
        margin-top: 1rem;
    }

    .order-item {
        flex-direction: column;
    }

    .item-details {
        text-align: left;
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 1px solid #eee;
    }

    .btn {
        width: 100%;
        text-align: center;
        margin-bottom: 0.5rem;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle order details toggle
    document.querySelectorAll('.toggle-details').forEach(button => {
        button.addEventListener('click', function() {
            const orderCard = this.closest('.order-card');
            const details = orderCard.querySelector('.order-details');
            const showText = this.querySelector('.show-text');
            const hideText = this.querySelector('.hide-text');

            if (details.style.display === 'none') {
                details.style.display = 'block';
                showText.style.display = 'none';
                hideText.style.display = 'inline';
            } else {
                details.style.display = 'none';
                showText.style.display = 'inline';
                hideText.style.display = 'none';
            }
        });
    });
});
</script>

<?php require_once 'footer.php'; ?>