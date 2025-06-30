<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../helpers.php';
require_once __DIR__ . '/../models/User.php';

startSession();

// Redirect if not logged in
if (!isLoggedIn()) {
    setFlashMessage('Please log in to view your profile.', 'error');
    header('Location: /pepecomics/php/views/login.php');
    exit();
}

// Get user data
$userModel = new User($pdo);
$user = $userModel->getUserById($_SESSION['user_id']);

if (!$user) {
    setFlashMessage('User not found.', 'error');
    header('Location: /pepecomics/index.php');
    exit();
}

$pageTitle = 'My Profile';
require_once 'header.php';
?>

<div class="profile-container">
    <h1 class="profile-title">My Profile</h1>
    
    <?php if (isset($_SESSION['flash_message'])): ?>
        <div class="alert alert--<?= $_SESSION['flash_type'] ?? 'info' ?>">
            <?= htmlspecialchars($_SESSION['flash_message']) ?>
        </div>
        <?php unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?>
    <?php endif; ?>

    <div class="profile-content">
        <div class="profile-section">
            <h2>Personal Information</h2>
            <form action="/pepecomics/php/controllers/auth.php?action=update_profile" method="POST" class="profile-form">
                <div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" id="name" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" value="<?= htmlspecialchars($user['email']) ?>" disabled>
                    <small>Email cannot be changed</small>
                </div>

                <div class="form-group">
                    <label for="phone">Phone</label>
                    <input type="tel" id="phone" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label for="address">Shipping Address</label>
                    <textarea id="address" name="address" rows="3"><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
                </div>

                <button type="submit" class="btn btn-primary">Update Profile</button>
            </form>
        </div>

        <div class="profile-section">
            <h2>Change Password</h2>
            <form action="/pepecomics/php/controllers/auth.php?action=change_password" method="POST" class="profile-form" id="password-form">
                <div class="form-group">
                    <label for="current_password">Current Password</label>
                    <input type="password" id="current_password" name="current_password" required>
                </div>

                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <input type="password" id="new_password" name="new_password" required>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm New Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>

                <button type="submit" class="btn btn-primary">Change Password</button>
            </form>
        </div>

        <div class="profile-section">
            <h2>Account Actions</h2>
            <div class="account-actions">
                <a href="/pepecomics/php/views/orders.php" class="btn btn-secondary">View My Orders</a>
                <a href="/pepecomics/php/controllers/auth.php?action=logout" class="btn btn-danger">Logout</a>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('password-form').addEventListener('submit', function(e) {
    const newPassword = document.getElementById('new_password').value;
    const confirmPassword = document.getElementById('confirm_password').value;

    if (newPassword !== confirmPassword) {
        e.preventDefault();
        showNotification('Passwords do not match!', 'error');
    }
});
</script>

<style>
.profile-container {
    max-width: 800px;
    margin: 2rem auto;
    padding: 0 1rem;
}

.profile-title {
    text-align: center;
    margin-bottom: 2rem;
    color: #333;
}

.profile-content {
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

.profile-section {
    padding: 2rem;
    border-bottom: 1px solid #eee;
}

.profile-section:last-child {
    border-bottom: none;
}

.profile-section h2 {
    margin-bottom: 1.5rem;
    color: #444;
}

.profile-form .form-group {
    margin-bottom: 1.5rem;
}

.profile-form label {
    display: block;
    margin-bottom: 0.5rem;
    color: #666;
}

.profile-form input,
.profile-form textarea {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 1rem;
}

.profile-form input:disabled {
    background-color: #f5f5f5;
    cursor: not-allowed;
}

.profile-form small {
    display: block;
    margin-top: 0.25rem;
    color: #666;
}

.account-actions {
    display: flex;
    gap: 1rem;
    justify-content: flex-start;
}

.btn {
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 1rem;
    transition: background-color 0.3s ease;
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

.btn-danger {
    background-color: #f44336;
    color: white;
}

.btn-danger:hover {
    background-color: #e53935;
}

@media (max-width: 768px) {
    .profile-section {
        padding: 1.5rem;
    }

    .account-actions {
        flex-direction: column;
    }

    .btn {
        width: 100%;
        text-align: center;
    }
}
</style>

<?php require_once 'footer.php'; ?> 