<?php
require_once '../../../php/db.php';
require_once '../../../php/helpers.php';
require_once '../../../php/models/User.php';
require_once '../../../php/models/Comic.php';
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

// Get statistics
$comicModel = new Comic($pdo);
$orderModel = new Order($pdo);

$totalComics = $comicModel->getTotalComics();
$totalOrders = $orderModel->getTotalOrders();
$totalUsers = $userModel->getTotalUsers();
$recentOrders = $orderModel->getRecentOrders(5);
$lowStockComics = $comicModel->getLowStockComics(5);
$monthlyRevenue = $orderModel->getMonthlyRevenue();

// Include header
require_once 'header.php';
?>

<div class="admin-main">
    <div class="container">
        <div class="admin-content">
            <h1>Dashboard</h1>
            
            <!-- Statistics Grid -->
            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Total Comics</h3>
                    <div class="value"><?= $totalComics ?></div>
                    <a href="comics.php" class="admin-btn admin-btn-secondary">Manage Comics</a>
                </div>
                
                <div class="stat-card">
                    <h3>Total Orders</h3>
                    <div class="value"><?= $totalOrders ?></div>
                    <a href="orders.php" class="admin-btn admin-btn-secondary">View Orders</a>
                </div>
                
                <div class="stat-card">
                    <h3>Total Users</h3>
                    <div class="value"><?= $totalUsers ?></div>
                    <a href="users.php" class="admin-btn admin-btn-secondary">Manage Users</a>
                </div>
                
                <div class="stat-card">
                    <h3>Monthly Revenue</h3>
                    <div class="value">$<?= number_format($monthlyRevenue, 2) ?></div>
                    <div class="trend up">
                        <i class="fas fa-arrow-up"></i>
                        <span>12% from last month</span>
                    </div>
                </div>
            </div>
            
            <!-- Recent Orders -->
            <div class="row">
                <div class="col-md-6">
                    <h2>Recent Orders</h2>
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentOrders as $order): ?>
                                <tr>
                                    <td>#<?= $order['order_id'] ?></td>
                                    <td><?= htmlspecialchars($order['customer_name']) ?></td>
                                    <td>$<?= number_format($order['total_amount'], 2) ?></td>
                                    <td>
                                        <span class="status-badge status-<?= strtolower($order['status']) ?>">
                                            <?= $order['status'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="order-details.php?id=<?= $order['order_id'] ?>" 
                                           class="admin-btn admin-btn-secondary">View</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <a href="orders.php" class="admin-btn admin-btn-primary">View All Orders</a>
                </div>
                
                <!-- Low Stock Comics -->
                <div class="col-md-6">
                    <h2>Low Stock Comics</h2>
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Stock</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($lowStockComics as $comic): ?>
                                <tr>
                                    <td><?= htmlspecialchars($comic['title']) ?></td>
                                    <td>
                                        <span class="stock-badge <?= $comic['stock'] < 5 ? 'stock-critical' : 'stock-low' ?>">
                                            <?= $comic['stock'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="edit-comic.php?id=<?= $comic['comic_id'] ?>" 
                                           class="admin-btn admin-btn-secondary">Update Stock</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <a href="comics.php" class="admin-btn admin-btn-primary">Manage All Comics</a>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="quick-actions">
                <h2>Quick Actions</h2>
                <div class="action-buttons">
                    <a href="add-comic.php" class="admin-btn admin-btn-primary">
                        <i class="fas fa-plus"></i> Add New Comic
                    </a>
                    <a href="categories.php" class="admin-btn admin-btn-secondary">
                        <i class="fas fa-tags"></i> Manage Categories
                    </a>
                    <a href="reports.php" class="admin-btn admin-btn-secondary">
                        <i class="fas fa-chart-bar"></i> View Reports
                    </a>
                    <a href="settings.php" class="admin-btn admin-btn-secondary">
                        <i class="fas fa-cog"></i> Settings
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?> 