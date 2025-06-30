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

// Get search query and filters
$search = $_GET['search'] ?? '';
$role = $_GET['role'] ?? '';
$sort = $_GET['sort'] ?? 'name_asc';

// Handle user actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $userId = intval($_POST['user_id'] ?? 0);
    
    if ($userId && $userId !== $user['user_id']) { // Prevent self-modification
        switch ($action) {
            case 'toggle_admin':
                $isAdmin = filter_var($_POST['is_admin'], FILTER_VALIDATE_BOOLEAN);
                if ($userModel->updateUserRole($userId, $isAdmin)) {
                    $_SESSION['flash_message'] = 'User role has been updated successfully.';
                    $_SESSION['flash_type'] = 'success';
                } else {
                    $_SESSION['flash_message'] = 'Error updating user role. Please try again.';
                    $_SESSION['flash_type'] = 'error';
                }
                break;
                
            case 'delete':
                if ($userModel->deleteUser($userId)) {
                    $_SESSION['flash_message'] = 'User has been deleted successfully.';
                    $_SESSION['flash_type'] = 'success';
                } else {
                    $_SESSION['flash_message'] = 'Error deleting user. Please try again.';
                    $_SESSION['flash_type'] = 'error';
                }
                break;
        }
        
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
    }
}

// Get users with filters
$users = $userModel->getUsersWithFilters($role, $search, $sort);

require_once 'header.php';
?>

<div class="admin-main">
    <div class="container">
        <div class="admin-content">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>Manage Users</h1>
                <div class="user-stats">
                    <span class="stat-item">
                        <i class="fas fa-users"></i>
                        Total Users: <?= $userModel->getTotalUsers() ?>
                    </span>
                    <span class="stat-item">
                        <i class="fas fa-user-shield"></i>
                        Admins: <?= $userModel->getTotalAdmins() ?>
                    </span>
                </div>
            </div>

            <!-- Filters -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-4">
                            <label for="search" class="form-label">Search</label>
                            <input type="text" class="form-control" id="search" name="search" 
                                   value="<?= htmlspecialchars($search) ?>" 
                                   placeholder="Search by name or email">
                        </div>
                        
                        <div class="col-md-3">
                            <label for="role" class="form-label">Role</label>
                            <select class="form-control" id="role" name="role">
                                <option value="">All Roles</option>
                                <option value="admin" <?= $role === 'admin' ? 'selected' : '' ?>>Admin</option>
                                <option value="user" <?= $role === 'user' ? 'selected' : '' ?>>User</option>
                            </select>
                        </div>
                        
                        <div class="col-md-3">
                            <label for="sort" class="form-label">Sort By</label>
                            <select class="form-control" id="sort" name="sort">
                                <option value="name_asc" <?= $sort === 'name_asc' ? 'selected' : '' ?>>Name (A-Z)</option>
                                <option value="name_desc" <?= $sort === 'name_desc' ? 'selected' : '' ?>>Name (Z-A)</option>
                                <option value="orders" <?= $sort === 'orders' ? 'selected' : '' ?>>Most Orders</option>
                                <option value="spent" <?= $sort === 'spent' ? 'selected' : '' ?>>Highest Spent</option>
                            </select>
                        </div>
                        
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-filter"></i> Apply Filters
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Users Table -->
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Orders</th>
                            <th>Total Spent</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $userData): ?>
                            <tr>
                                <td><?= htmlspecialchars($userData['name']) ?></td>
                                <td><?= htmlspecialchars($userData['email']) ?></td>
                                <td>
                                    <?php if ($userData['user_id'] !== $user['user_id']): ?>
                                        <form method="POST" class="role-form">
                                            <input type="hidden" name="action" value="toggle_admin">
                                            <input type="hidden" name="user_id" value="<?= $userData['user_id'] ?>">
                                            <input type="hidden" name="is_admin" value="<?= !$userData['is_admin'] ?>">
                                            <button type="submit" class="role-badge <?= $userData['is_admin'] ? 'role-admin' : 'role-user' ?>">
                                                <?= $userData['is_admin'] ? 'Admin' : 'User' ?>
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <span class="role-badge role-admin">Admin (You)</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $orderCount = $orderModel->getUserOrderCount($userData['user_id']);
                                    if ($orderCount > 0):
                                    ?>
                                        <a href="orders.php?user_id=<?= $userData['user_id'] ?>" 
                                           class="order-count">
                                            <?= $orderCount ?> order<?= $orderCount !== 1 ? 's' : '' ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">No orders</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $totalSpent = $orderModel->getUserTotalSpent($userData['user_id']);
                                    if ($totalSpent > 0):
                                    ?>
                                        $<?= number_format($totalSpent, 2) ?>
                                    <?php else: ?>
                                        <span class="text-muted">$0.00</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="user-details.php?id=<?= $userData['user_id'] ?>" 
                                           class="admin-btn admin-btn-secondary btn-sm">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <?php if ($userData['user_id'] !== $user['user_id']): ?>
                                            <button type="button" 
                                                    class="admin-btn admin-btn-danger btn-sm"
                                                    onclick="deleteUser(<?= $userData['user_id'] ?>, '<?= htmlspecialchars($userData['name']) ?>')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        <?php endif; ?>
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

<!-- Delete User Form (Hidden) -->
<form id="deleteUserForm" method="POST" style="display: none;">
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
</script>

<?php require_once 'footer.php'; ?> 