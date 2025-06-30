<?php
require_once __DIR__ . '/../php/db.php';
require_once __DIR__ . '/../php/helpers.php';

startSession();

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    setFlashMessage('Access denied. Admin privileges required.', 'error');
    header('Location: /pepecomics/php/views/login.php');
    exit();
}

// Redirect to admin dashboard
header('Location: /pepecomics/admin/php/views/dashboard.php');
exit();
?>