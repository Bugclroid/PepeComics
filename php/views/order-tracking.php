<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../helpers.php';
require_once __DIR__ . '/../models/Order.php';

startSession();

// Check if user is logged in
if (!isLoggedIn()) {
    setFlashMessage('Please log in to view order tracking.', 'error');
    header('Location: /pepecomics/php/views/login.php');
    exit();
}

// Get order ID from URL
$orderId = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;

if (!$orderId) {
    setFlashMessage('Invalid order ID.', 'error');
    header('Location: /pepecomics/php/views/orders.php');
    exit();
}

$orderModel = new Order($pdo);
$order = $orderModel->getOrder($orderId); // Using getOrder instead of getOrderById to get full details

// Verify order belongs to user
if (!$order || $order['user_id'] !== $_SESSION['user_id']) {
    setFlashMessage('Order not found or access denied.', 'error');
    header('Location: /pepecomics/php/views/orders.php');
    exit();
}

// Parse shipping address from order data
$shippingAddress = json_decode($order['shipping_address'] ?? '{}', true) ?: [];

// Set default values if any part is missing
$address = $shippingAddress['address'] ?? 'N/A';
$city = $shippingAddress['city'] ?? 'N/A';
$state = $shippingAddress['state'] ?? 'N/A';
$postalCode = $shippingAddress['postal_code'] ?? 'N/A';

// Get order items with error handling
try {
    $items = $orderModel->getOrderItems($orderId);
    $itemsError = '';
} catch (Exception $e) {
    $items = [];
    $itemsError = 'Error loading order items: ' . $e->getMessage();
    error_log("Order items error for order #$orderId: " . $e->getMessage());
}

$pageTitle = "Track Order #$orderId";
require_once 'header.php';
?>

<div class="order-tracking-page">
    <div class="container">
        <nav class="breadcrumb" aria-label="Breadcrumb">
            <ol>
                <li><a href="/pepecomics/index.php">Home</a></li>
                <li><a href="/pepecomics/php/views/orders.php">My Orders</a></li>
                <li>Order #<?= htmlspecialchars($orderId) ?></li>
            </ol>
        </nav>

        <div class="order-tracking-content">
            <h1>Track Order #<?= htmlspecialchars($orderId) ?></h1>
            
            <!-- Order Status -->
            <div class="order-status">
                <div class="status-timeline">
                    <div class="status-step active">
                        <div class="step-icon">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <div class="step-label">Order Placed</div>
                        <div class="step-date"><?= date('M d, Y', strtotime($order['order_date'])) ?></div>
                    </div>

                    <div class="status-step <?= ($order['payment_status'] === 'Completed') ? 'active' : '' ?>" id="payment-step">
                        <div class="step-icon">
                            <i class="fas fa-credit-card"></i>
                        </div>
                        <div class="step-label">Payment Confirmed</div>
                        <div class="step-date">
                            <?= ($order['payment_status'] === 'Completed' && isset($order['payment_date'])) 
                                ? date('M d, Y', strtotime($order['payment_date'])) 
                                : '' ?>
                        </div>
                    </div>

                    <div class="status-step" id="processing-step">
                        <div class="step-icon">
                            <i class="fas fa-box"></i>
                        </div>
                        <div class="step-label">Processing</div>
                        <div class="step-date"></div>
                    </div>

                    <div class="status-step" id="shipped-step">
                        <div class="step-icon">
                            <i class="fas fa-shipping-fast"></i>
                        </div>
                        <div class="step-label">Shipped</div>
                        <div class="step-date"></div>
                    </div>

                    <div class="status-step" id="delivered-step">
                        <div class="step-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="step-label">Delivered</div>
                        <div class="step-date"></div>
                    </div>
                </div>
            </div>

            <!-- Order Details -->
            <div class="order-details">
                <div class="order-info">
                    <h2>Order Information</h2>
                    <div class="info-grid">
                        <div class="info-item">
                            <span class="label">Order Date:</span>
                            <span class="value"><?= date('M d, Y', strtotime($order['order_date'])) ?></span>
                        </div>
                        <div class="info-item">
                            <span class="label">Total Amount:</span>
                            <span class="value">$<?= number_format($order['total_amount'], 2) ?></span>
                        </div>
                        <div class="info-item">
                            <span class="label">Payment Method:</span>
                            <span class="value"><?= htmlspecialchars($order['payment_method'] ?? 'Not specified') ?></span>
                        </div>
                        <div class="info-item">
                            <span class="label">Payment Status:</span>
                            <span class="value"><?= htmlspecialchars($order['payment_status'] ?? 'Pending') ?></span>
                        </div>
                    </div>
                </div>

                <div class="shipping-info">
                    <h2>Shipping Information</h2>
                    <div class="info-grid">
                        <div class="info-item">
                            <span class="label">Address:</span>
                            <span class="value"><?= htmlspecialchars($address) ?></span>
                        </div>
                        <div class="info-item">
                            <span class="label">City:</span>
                            <span class="value"><?= htmlspecialchars($city) ?></span>
                        </div>
                        <div class="info-item">
                            <span class="label">State:</span>
                            <span class="value"><?= htmlspecialchars($state) ?></span>
                        </div>
                        <div class="info-item">
                            <span class="label">Postal Code:</span>
                            <span class="value"><?= htmlspecialchars($postalCode) ?></span>
                        </div>
                    </div>
                </div>

                <div class="order-items">
                    <h2>Order Items</h2>
                    <div class="items-list">
                        <?php if (!empty($items)): ?>
                            <?php foreach ($items as $item): ?>
                                <div class="order-item">
                                    <div class="item-details">
                                        <h3 class="item-title"><?= htmlspecialchars($item['title'] ?? 'Unknown Title') ?></h3>
                                        <p class="item-author">By <?= htmlspecialchars($item['author'] ?? 'Unknown Author') ?></p>
                                        <p class="item-meta">
                                            <span class="quantity">Quantity: <?= htmlspecialchars($item['quantity'] ?? 0) ?></span>
                                            <span class="price">Price: $<?= number_format(($item['total_price'] ?? 0) / ($item['quantity'] ?? 1), 2) ?></span>
                                            <span class="total">Total: $<?= number_format($item['total_price'] ?? 0, 2) ?></span>
                                        </p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="no-items">
                                <?= !empty($itemsError) ? htmlspecialchars($itemsError) : 'No items found for this order.' ?>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Pepe Animation -->
            <div class="pepe-delivery">
                <img src="/pepecomics/images/animations/pepe-delivery.gif" alt="Pepe Delivery Animation" class="pepe-delivery-animation">
            </div>
        </div>
    </div>
</div>

<style>
.order-tracking-page {
    padding: 2rem 0;
}

.breadcrumb {
    margin-bottom: 2rem;
}

.breadcrumb ol {
    list-style: none;
    padding: 0;
    display: flex;
    gap: 0.5rem;
}

.breadcrumb li:not(:last-child):after {
    content: ">";
    margin-left: 0.5rem;
    color: #666;
}

.order-tracking-content {
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    padding: 2rem;
}

.status-timeline {
    display: flex;
    justify-content: space-between;
    margin: 3rem 0;
    position: relative;
}

.status-timeline:before {
    content: "";
    position: absolute;
    top: 25px;
    left: 0;
    right: 0;
    height: 2px;
    background: #eee;
    z-index: 1;
}

.status-step {
    position: relative;
    z-index: 2;
    text-align: center;
    flex: 1;
}

.step-icon {
    width: 50px;
    height: 50px;
    background: #fff;
    border: 2px solid #ddd;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1rem;
}

.status-step.active .step-icon {
    border-color: #4CAF50;
    color: #4CAF50;
}

.step-label {
    font-weight: bold;
    margin-bottom: 0.5rem;
}

.step-date {
    font-size: 0.9rem;
    color: #666;
}

.order-details {
    margin-top: 3rem;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
    margin-top: 1rem;
}

.info-item {
    display: flex;
    flex-direction: column;
}

.info-item .label {
    font-weight: bold;
    color: #666;
    margin-bottom: 0.5rem;
}

.order-items {
    margin-top: 2rem;
}

.order-item {
    border: 1px solid #eee;
    border-radius: 4px;
    padding: 1rem;
    margin-bottom: 1rem;
}

.item-title {
    margin: 0 0 0.5rem 0;
    color: #333;
}

.item-author {
    color: #666;
    margin: 0 0 0.5rem 0;
}

.item-meta {
    display: flex;
    gap: 1.5rem;
    color: #666;
}

.pepe-delivery {
    text-align: center;
    margin-top: 3rem;
}

.pepe-delivery-animation {
    max-width: 200px;
}

@media (max-width: 768px) {
    .status-timeline {
        flex-direction: column;
        gap: 2rem;
        margin: 2rem 0;
    }

    .status-timeline:before {
        top: 0;
        bottom: 0;
        left: 25px;
        width: 2px;
        height: auto;
    }

    .status-step {
        display: flex;
        align-items: center;
        text-align: left;
    }

    .step-icon {
        margin: 0 1rem 0 0;
    }

    .info-grid {
        grid-template-columns: 1fr;
    }

    .item-meta {
        flex-direction: column;
        gap: 0.5rem;
    }
}
</style>

<script>
// Fetch order tracking information
async function fetchTrackingInfo() {
    try {
        const response = await fetch(`/pepecomics/php/controllers/orders.php?action=track&order_id=<?= $orderId ?>`);
        const data = await response.json();

        if (data.success) {
            updateTrackingDisplay(data.tracking);
        }
    } catch (error) {
        console.error('Error fetching tracking info:', error);
    }
}

// Update tracking display
function updateTrackingDisplay(tracking) {
    const steps = ['processing', 'shipped', 'delivered'];
    
    steps.forEach(step => {
        const element = document.getElementById(`${step}-step`);
        if (element && tracking[step]) {
            element.classList.add('active');
            const dateElement = element.querySelector('.step-date');
            if (dateElement && tracking[`${step}_date`]) {
                dateElement.textContent = new Date(tracking[`${step}_date`]).toLocaleDateString('en-US', {
                    month: 'short',
                    day: 'numeric',
                    year: 'numeric'
                });
            }
        }
    });
}

// Initial fetch
fetchTrackingInfo();

// Refresh tracking info every 30 seconds
setInterval(fetchTrackingInfo, 30000);
</script>

<?php require_once 'footer.php'; ?> 