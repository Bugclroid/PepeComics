<?php
require_once '../../../php/db.php';
require_once '../../../php/helpers.php';
require_once '../../../php/models/User.php';
require_once '../../../php/models/Order.php';
require_once '../../../php/models/Payment.php';

// Start session and check if user is logged in and is admin
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /php/views/login.php');
    exit();
}

// Get user data
$userModel = new User($pdo);
$user = $userModel->getUserById($_SESSION['user_id']);

if (!$user['is_admin']) {
    header('Location: /index.php');
    exit();
}

// Initialize models
$orderModel = new Order($pdo);
$paymentModel = new Payment($pdo);

// Get order ID from URL
$orderId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$orderId) {
    $_SESSION['flash_message'] = 'Invalid order ID.';
    $_SESSION['flash_type'] = 'error';
    header('Location: orders.php');
    exit();
}

// Get order data
$order = $orderModel->getOrderById($orderId);
if (!$order) {
    $_SESSION['flash_message'] = 'Order not found.';
    $_SESSION['flash_type'] = 'error';
    header('Location: orders.php');
    exit();
}

// Get order items
$orderItems = $orderModel->getOrderItems($orderId);

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_status') {
        $newStatus = sanitizeInput($_POST['status']);
        
        if ($orderModel->updateOrderStatus($orderId, $newStatus)) {
            $_SESSION['flash_message'] = 'Order status has been updated successfully.';
            $_SESSION['flash_type'] = 'success';
            header('Location: ' . $_SERVER['PHP_SELF'] . '?id=' . $orderId);
            exit();
        } else {
            $_SESSION['flash_message'] = 'Error updating order status. Please try again.';
            $_SESSION['flash_type'] = 'error';
        }
    }
}

// Helper function to get status color
function getStatusColor($status) {
    switch (strtolower($status)) {
        case 'pending':
            return '#ffc107';
        case 'processing':
            return '#17a2b8';
        case 'shipped':
            return '#28a745';
        case 'delivered':
            return '#28a745';
        case 'cancelled':
            return '#dc3545';
        default:
            return '#6c757d';
    }
}

require_once 'header.php';
?>

<div class="admin-main">
    <div class="container">
        <div class="admin-content">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>Order Details #<?= $orderId ?></h1>
                <div class="action-buttons">
                    <a href="orders.php" class="admin-btn admin-btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Orders
                    </a>
                    <button type="button" class="admin-btn admin-btn-secondary" 
                            onclick="printOrder(<?= $orderId ?>)">
                        <i class="fas fa-print"></i> Print Order
                    </button>
                </div>
            </div>

            <?php if (isset($_SESSION['flash_message'])): ?>
                <div class="alert alert-<?= $_SESSION['flash_type'] ?>">
                    <?= $_SESSION['flash_message'] ?>
                </div>
                <?php unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?>
            <?php endif; ?>

            <!-- Order Status -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Order Status</h5>
                            <form method="POST" class="status-form">
                                <input type="hidden" name="action" value="update_status">
                                <select class="form-control status-select" name="status" 
                                        onchange="this.form.submit()"
                                        style="background-color: <?= getStatusColor($order['status']) ?>">
                                    <option value="pending" <?= $order['status'] === 'pending' ? 'selected' : '' ?>>
                                        Pending
                                    </option>
                                    <option value="processing" <?= $order['status'] === 'processing' ? 'selected' : '' ?>>
                                        Processing
                                    </option>
                                    <option value="shipped" <?= $order['status'] === 'shipped' ? 'selected' : '' ?>>
                                        Shipped
                                    </option>
                                    <option value="delivered" <?= $order['status'] === 'delivered' ? 'selected' : '' ?>>
                                        Delivered
                                    </option>
                                    <option value="cancelled" <?= $order['status'] === 'cancelled' ? 'selected' : '' ?>>
                                        Cancelled
                                    </option>
                                </select>
                            </form>
                        </div>
                        <div class="col-md-6">
                            <h5>Payment Status</h5>
                            <span class="status-badge status-<?= strtolower($order['payment_status']) ?>">
                                <?= ucfirst($order['payment_status']) ?>
                            </span>
                            <?php if (!empty($order['payment_method'])): ?>
                                <div class="mt-2">
                                    <strong>Payment Method:</strong> <?= ucfirst($order['payment_method']) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Order Information -->
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Order Information</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-borderless">
                                <tr>
                                    <th>Order Date:</th>
                                    <td><?= date('M j, Y g:i A', strtotime($order['order_date'])) ?></td>
                                </tr>
                                <tr>
                                    <th>Customer Name:</th>
                                    <td><?= htmlspecialchars($order['customer_name']) ?></td>
                                </tr>
                                <tr>
                                    <th>Email:</th>
                                    <td><?= htmlspecialchars($order['email']) ?></td>
                                </tr>
                                <?php if (!empty($order['phone'])): ?>
                                    <tr>
                                        <th>Phone:</th>
                                        <td><?= htmlspecialchars($order['phone']) ?></td>
                                    </tr>
                                <?php endif; ?>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Shipping Information -->
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Shipping Information</h5>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($order['shipping_address'])): ?>
                                <table class="table table-borderless">
                                    <tr>
                                        <th>Address:</th>
                                        <td><?= htmlspecialchars($order['shipping_address']) ?></td>
                                    </tr>
                                    <?php if (!empty($order['shipping_city'])): ?>
                                        <tr>
                                            <th>City:</th>
                                            <td><?= htmlspecialchars($order['shipping_city']) ?></td>
                                        </tr>
                                    <?php endif; ?>
                                    <?php if (!empty($order['shipping_state'])): ?>
                                        <tr>
                                            <th>State:</th>
                                            <td><?= htmlspecialchars($order['shipping_state']) ?></td>
                                        </tr>
                                    <?php endif; ?>
                                    <?php if (!empty($order['shipping_postal_code'])): ?>
                                        <tr>
                                            <th>Postal Code:</th>
                                            <td><?= htmlspecialchars($order['shipping_postal_code']) ?></td>
                                        </tr>
                                    <?php endif; ?>
                                </table>
                            <?php else: ?>
                                <p class="text-muted">No shipping information available.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Items -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Order Items</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($orderItems)): ?>
                        <div class="table-responsive">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Price</th>
                                        <th>Quantity</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($orderItems as $item): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="ms-0">
                                                        <h6 class="mb-0"><?= htmlspecialchars($item['title']) ?></h6>
                                                        <?php if (!empty($item['author'])): ?>
                                                            <small class="text-muted">
                                                                By <?= htmlspecialchars($item['author']) ?>
                                                            </small>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>$<?= number_format($item['price'], 2) ?></td>
                                            <td><?= $item['quantity'] ?></td>
                                            <td>$<?= number_format($item['total_price'], 2) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="3" class="text-end"><strong>Subtotal:</strong></td>
                                        <td>$<?= number_format($order['subtotal'], 2) ?></td>
                                    </tr>
                                    <tr>
                                        <td colspan="3" class="text-end"><strong>Tax:</strong></td>
                                        <td>$<?= number_format($order['tax'], 2) ?></td>
                                    </tr>
                                    <tr>
                                        <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                        <td>$<?= number_format($order['total_amount'], 2) ?></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">No order items found.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?> 