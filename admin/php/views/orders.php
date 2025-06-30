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

// Get filters
$status = $_GET['status'] ?? '';
$dateRange = $_GET['date_range'] ?? '';
$search = $_GET['search'] ?? '';
$sort = $_GET['sort'] ?? 'date_desc';

// Handle status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_status') {
        $orderId = intval($_POST['order_id']);
        $newStatus = sanitizeInput($_POST['status']);
        
        if ($orderModel->updateOrderStatus($orderId, $newStatus)) {
            $_SESSION['flash_message'] = 'Order status has been updated successfully.';
            $_SESSION['flash_type'] = 'success';
        } else {
            $_SESSION['flash_message'] = 'Error updating order status. Please try again.';
            $_SESSION['flash_type'] = 'error';
        }
        
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
    }
}

// Get orders with filters
$orders = $orderModel->getOrdersWithFilters($status, $dateRange, $search, $sort);

require_once 'header.php';
?>

<div class="admin-main">
    <div class="container">
        <div class="admin-content">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>Manage Orders</h1>
                <div class="order-stats">
                    <span class="stat-item">
                        <i class="fas fa-shopping-cart"></i>
                        Total Orders: <?= $orderModel->getTotalOrders() ?>
                    </span>
                    <span class="stat-item">
                        <i class="fas fa-dollar-sign"></i>
                        Total Revenue: $<?= number_format($orderModel->getTotalRevenue(), 2) ?>
                    </span>
                </div>
            </div>

            <!-- Filters -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-control" id="status" name="status">
                                <option value="">All Statuses</option>
                                <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="processing" <?= $status === 'processing' ? 'selected' : '' ?>>Processing</option>
                                <option value="shipped" <?= $status === 'shipped' ? 'selected' : '' ?>>Shipped</option>
                                <option value="delivered" <?= $status === 'delivered' ? 'selected' : '' ?>>Delivered</option>
                                <option value="cancelled" <?= $status === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                            </select>
                        </div>
                        
                        <div class="col-md-3">
                            <label for="date_range" class="form-label">Date Range</label>
                            <select class="form-control" id="date_range" name="date_range">
                                <option value="">All Time</option>
                                <option value="today" <?= $dateRange === 'today' ? 'selected' : '' ?>>Today</option>
                                <option value="yesterday" <?= $dateRange === 'yesterday' ? 'selected' : '' ?>>Yesterday</option>
                                <option value="last_7_days" <?= $dateRange === 'last_7_days' ? 'selected' : '' ?>>Last 7 Days</option>
                                <option value="last_30_days" <?= $dateRange === 'last_30_days' ? 'selected' : '' ?>>Last 30 Days</option>
                                <option value="this_month" <?= $dateRange === 'this_month' ? 'selected' : '' ?>>This Month</option>
                                <option value="last_month" <?= $dateRange === 'last_month' ? 'selected' : '' ?>>Last Month</option>
                            </select>
                        </div>
                        
                        <div class="col-md-3">
                            <label for="search" class="form-label">Search</label>
                            <input type="text" class="form-control" id="search" name="search" 
                                   value="<?= htmlspecialchars($search) ?>" 
                                   placeholder="Order ID or Customer Name">
                        </div>
                        
                        <div class="col-md-2">
                            <label for="sort" class="form-label">Sort By</label>
                            <select class="form-control" id="sort" name="sort">
                                <option value="date_desc" <?= $sort === 'date_desc' ? 'selected' : '' ?>>
                                    Date (Newest First)
                                </option>
                                <option value="date_asc" <?= $sort === 'date_asc' ? 'selected' : '' ?>>
                                    Date (Oldest First)
                                </option>
                                <option value="total_desc" <?= $sort === 'total_desc' ? 'selected' : '' ?>>
                                    Total (High to Low)
                                </option>
                                <option value="total_asc" <?= $sort === 'total_asc' ? 'selected' : '' ?>>
                                    Total (Low to High)
                                </option>
                            </select>
                        </div>
                        
                        <div class="col-md-1 d-flex align-items-end">
                            <button type="submit" class="admin-btn admin-btn-secondary w-100">
                                <i class="fas fa-filter"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Orders Table -->
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Date</th>
                            <th>Total</th>
                            <th>Payment Status</th>
                            <th>Order Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td>#<?= $order['order_id'] ?></td>
                                <td><?= htmlspecialchars($order['customer_name']) ?></td>
                                <td><?= date('M j, Y g:i A', strtotime($order['order_date'])) ?></td>
                                <td>$<?= number_format($order['total_amount'], 2) ?></td>
                                <td>
                                    <span class="status-badge status-<?= strtolower($order['payment_status']) ?>">
                                        <?= $order['payment_status'] ?>
                                    </span>
                                </td>
                                <td>
                                    <form method="POST" class="status-form">
                                        <input type="hidden" name="action" value="update_status">
                                        <input type="hidden" name="order_id" value="<?= $order['order_id'] ?>">
                                        <select class="form-control form-control-sm status-select" 
                                                name="status" 
                                                onchange="this.form.submit()"
                                                style="background-color: <?= getStatusColor($order['order_status']) ?>">
                                            <option value="pending" <?= $order['order_status'] === 'pending' ? 'selected' : '' ?>>
                                                Pending
                                            </option>
                                            <option value="processing" <?= $order['order_status'] === 'processing' ? 'selected' : '' ?>>
                                                Processing
                                            </option>
                                            <option value="shipped" <?= $order['order_status'] === 'shipped' ? 'selected' : '' ?>>
                                                Shipped
                                            </option>
                                            <option value="delivered" <?= $order['order_status'] === 'delivered' ? 'selected' : '' ?>>
                                                Delivered
                                            </option>
                                            <option value="cancelled" <?= $order['order_status'] === 'cancelled' ? 'selected' : '' ?>>
                                                Cancelled
                                            </option>
                                        </select>
                                    </form>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="order-details.php?id=<?= $order['order_id'] ?>" 
                                           class="admin-btn admin-btn-secondary btn-sm">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="#" class="admin-btn admin-btn-secondary btn-sm"
                                           onclick="printOrder(<?= $order['order_id'] ?>)">
                                            <i class="fas fa-print"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function getStatusColor(status) {
    const colors = {
        'pending': '#ffc107',
        'processing': '#17a2b8',
        'shipped': '#007bff',
        'delivered': '#28a745',
        'cancelled': '#dc3545'
    };
    return colors[status] || '#6c757d';
}

function printOrder(orderId) {
    window.open(`order-print.php?id=${orderId}`, '_blank');
}

// Initialize status select colors
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.status-select').forEach(select => {
        select.style.backgroundColor = getStatusColor(select.value);
        select.addEventListener('change', function() {
            this.style.backgroundColor = getStatusColor(this.value);
        });
    });
});
</script>

<?php
function getStatusColor($status) {
    $colors = [
        'pending' => '#ffc107',
        'processing' => '#17a2b8',
        'shipped' => '#007bff',
        'delivered' => '#28a745',
        'cancelled' => '#dc3545'
    ];
    return $colors[$status] ?? '#6c757d';
}

require_once 'footer.php';
?> 