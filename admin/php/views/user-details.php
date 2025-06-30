<?php
require_once '../../../php/db.php';
require_once '../../../php/helpers.php';
require_once '../../../php/models/User.php';
require_once '../../../php/models/Order.php';

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

// Get user ID from URL
$userId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$userId) {
    $_SESSION['flash_message'] = 'Invalid user ID.';
    $_SESSION['flash_type'] = 'error';
    header('Location: users.php');
    exit();
}

// Get user data
$userData = $userModel->getUserById($userId);
if (!$userData) {
    $_SESSION['flash_message'] = 'User not found.';
    $_SESSION['flash_type'] = 'error';
    header('Location: users.php');
    exit();
}

// Get user's orders
$orders = $orderModel->getUserOrders($userId);

// Calculate user statistics
$totalOrders = count($orders);
$totalSpent = $orderModel->getUserTotalSpent($userId);
$averageOrderValue = $totalOrders > 0 ? $totalSpent / $totalOrders : 0;
$lastOrderDate = $totalOrders > 0 ? $orders[0]['order_date'] : null;

require_once 'header.php';
?>

<div class="admin-main">
    <div class="container">
        <div class="admin-content">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>User Details</h1>
                <div class="action-buttons">
                    <a href="users.php" class="admin-btn admin-btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Users
                    </a>
                    <?php if ($userId !== $user['user_id']): ?>
                        <button type="button" class="admin-btn admin-btn-danger"
                                onclick="deleteUser(<?= $userId ?>, '<?= htmlspecialchars($userData['name']) ?>')">
                            <i class="fas fa-trash"></i> Delete User
                        </button>
                    <?php endif; ?>
                </div>
            </div>

            <div class="row">
                <!-- User Information -->
                <div class="col-md-4">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">User Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="user-info">
                                <div class="user-avatar mb-3 text-center">
                                    <i class="fas fa-user-circle fa-5x"></i>
                                    <?php if ($userId !== $user['user_id']): ?>
                                        <div class="mt-2">
                                            <form method="POST" class="role-form">
                                                <input type="hidden" name="action" value="toggle_admin">
                                                <input type="hidden" name="user_id" value="<?= $userId ?>">
                                                <input type="hidden" name="is_admin" value="<?= !$userData['is_admin'] ?>">
                                                <button type="submit" class="role-badge <?= $userData['is_admin'] ? 'role-admin' : 'role-user' ?>">
                                                    <?= $userData['is_admin'] ? 'Admin' : 'User' ?>
                                                </button>
                                            </form>
                                        </div>
                                    <?php else: ?>
                                        <div class="mt-2">
                                            <span class="role-badge role-admin">Admin (You)</span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <table class="table table-borderless">
                                    <tr>
                                        <th>Name:</th>
                                        <td><?= htmlspecialchars($userData['name']) ?></td>
                                    </tr>
                                    <tr>
                                        <th>Email:</th>
                                        <td><?= htmlspecialchars($userData['email']) ?></td>
                                    </tr>
                                    <tr>
                                        <th>Phone:</th>
                                        <td><?= htmlspecialchars($userData['phone'] ?? 'Not provided') ?></td>
                                    </tr>
                                    <tr>
                                        <th>Address:</th>
                                        <td><?= htmlspecialchars($userData['address'] ?? 'Not provided') ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- User Statistics -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Statistics</h5>
                        </div>
                        <div class="card-body">
                            <div class="stat-grid">
                                <div class="stat-item">
                                    <h6>Total Orders</h6>
                                    <div class="stat-value"><?= $totalOrders ?></div>
                                </div>
                                <div class="stat-item">
                                    <h6>Total Spent</h6>
                                    <div class="stat-value">$<?= number_format($totalSpent, 2) ?></div>
                                </div>
                                <div class="stat-item">
                                    <h6>Average Order Value</h6>
                                    <div class="stat-value">$<?= number_format($averageOrderValue, 2) ?></div>
                                </div>
                                <div class="stat-item">
                                    <h6>Last Order</h6>
                                    <div class="stat-value">
                                        <?= $lastOrderDate ? date('M j, Y', strtotime($lastOrderDate)) : 'Never' ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order History -->
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Order History</h5>
                        </div>
                        <div class="card-body">
                            <?php if ($totalOrders > 0): ?>
                                <div class="table-responsive">
                                    <table class="admin-table">
                                        <thead>
                                            <tr>
                                                <th>Order ID</th>
                                                <th>Date</th>
                                                <th>Total</th>
                                                <th>Status</th>
                                                <th>Payment</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($orders as $order): ?>
                                                <tr>
                                                    <td>#<?= $order['order_id'] ?></td>
                                                    <td><?= date('M j, Y g:i A', strtotime($order['order_date'])) ?></td>
                                                    <td>$<?= number_format($order['total_amount'], 2) ?></td>
                                                    <td>
                                                        <span class="status-badge status-<?= strtolower($order['status']) ?>">
                                                            <?= ucfirst($order['status']) ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="status-badge status-<?= strtolower($order['payment_status'] ?? 'pending') ?>">
                                                            <?= ucfirst($order['payment_status'] ?? 'Pending') ?>
                                                        </span>
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
                            <?php else: ?>
                                <div class="text-center text-muted py-4">
                                    <i class="fas fa-shopping-cart fa-3x mb-3"></i>
                                    <p>This user hasn't placed any orders yet.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete User Form (Hidden) -->
<form id="deleteUserForm" method="POST" action="users.php" style="display: none;">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="user_id" id="deleteUserId">
</form>

<script>
function deleteUser(userId, userName) {
    if (confirm(`Are you sure you want to delete the user "${userName}"? This action cannot be undone.`)) {
        document.getElementById('deleteUserId').value = userId;
        document.getElementById('deleteUserForm').submit();
    }
}

function printOrder(orderId) {
    window.open(`order-print.php?id=${orderId}`, '_blank');
}
</script>

<?php require_once 'footer.php'; ?> 