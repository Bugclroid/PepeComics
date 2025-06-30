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
    die('Invalid order ID.');
}

// Get order data
$order = $orderModel->getOrderById($orderId);
if (!$order) {
    die('Order not found.');
}

// Get order items and payment info
$orderItems = $orderModel->getOrderItems($orderId);
$payment = $paymentModel->getPaymentByOrderId($orderId);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order #<?= $orderId ?> - Print</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
        }

        .print-header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #eee;
        }

        .print-header h1 {
            margin: 0;
            color: #1a237e;
        }

        .print-header p {
            margin: 5px 0;
            color: #666;
        }

        .order-info {
            margin-bottom: 30px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }

        .info-section {
            padding: 15px;
            background: #f8f9fa;
            border-radius: 4px;
        }

        .info-section h2 {
            margin: 0 0 15px 0;
            font-size: 18px;
            color: #333;
        }

        .info-section p {
            margin: 5px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #f8f9fa;
            font-weight: bold;
            color: #333;
        }

        .totals {
            margin-left: auto;
            width: 300px;
        }

        .totals tr:last-child {
            font-weight: bold;
            font-size: 1.1em;
        }

        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-pending { background-color: #fff3cd; color: #856404; }
        .status-processing { background-color: #cce5ff; color: #004085; }
        .status-shipped { background-color: #d4edda; color: #155724; }
        .status-delivered { background-color: #d1e7dd; color: #0f5132; }
        .status-cancelled { background-color: #f8d7da; color: #721c24; }

        .footer {
            margin-top: 50px;
            text-align: center;
            color: #666;
            font-size: 14px;
        }

        @media print {
            body {
                padding: 0;
            }

            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="print-header">
        <h1>PepeComics</h1>
        <p>Order #<?= $orderId ?></p>
        <p><?= date('F j, Y g:i A', strtotime($order['order_date'])) ?></p>
    </div>

    <div class="info-grid">
        <!-- Order Information -->
        <div class="info-section">
            <h2>Order Information</h2>
            <p><strong>Customer:</strong> <?= htmlspecialchars($order['customer_name']) ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($order['email']) ?></p>
            <p><strong>Phone:</strong> <?= htmlspecialchars($order['phone']) ?></p>
            <p><strong>Order Status:</strong> 
                <span class="status-badge status-<?= strtolower($order['status']) ?>">
                    <?= ucfirst($order['status']) ?>
                </span>
            </p>
        </div>

        <!-- Shipping Information -->
        <div class="info-section">
            <h2>Shipping Information</h2>
            <p><strong>Address:</strong> <?= htmlspecialchars($order['shipping_address']) ?></p>
            <p><strong>City:</strong> <?= htmlspecialchars($order['shipping_city']) ?></p>
            <p><strong>State:</strong> <?= htmlspecialchars($order['shipping_state']) ?></p>
            <p><strong>Postal Code:</strong> <?= htmlspecialchars($order['shipping_postal_code']) ?></p>
        </div>
    </div>

    <!-- Order Items -->
    <table>
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
                        <div>
                            <strong><?= htmlspecialchars($item['title']) ?></strong><br>
                            <small>By <?= htmlspecialchars($item['author']) ?></small>
                        </div>
                    </td>
                    <td>$<?= number_format($item['price'], 2) ?></td>
                    <td><?= $item['quantity'] ?></td>
                    <td>$<?= number_format($item['total_price'], 2) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Order Totals -->
    <table class="totals">
        <tr>
            <td>Subtotal:</td>
            <td>$<?= number_format($order['subtotal'], 2) ?></td>
        </tr>
        <tr>
            <td>Tax:</td>
            <td>$<?= number_format($order['tax'], 2) ?></td>
        </tr>
        <tr>
            <td>Total:</td>
            <td>$<?= number_format($order['total_amount'], 2) ?></td>
        </tr>
    </table>

    <!-- Payment Information -->
    <div class="info-section">
        <h2>Payment Information</h2>
        <p><strong>Payment Method:</strong> <?= htmlspecialchars($payment['payment_method']) ?></p>
        <p><strong>Payment Date:</strong> <?= date('F j, Y g:i A', strtotime($payment['payment_date'])) ?></p>
        <p><strong>Payment Status:</strong> 
            <span class="status-badge status-<?= strtolower($payment['status']) ?>">
                <?= ucfirst($payment['status']) ?>
            </span>
        </p>
    </div>

    <div class="footer">
        <p>Thank you for shopping with PepeComics!</p>
        <p>For any questions, please contact us at support@pepecomics.com</p>
    </div>

    <!-- Print Button (visible only on screen) -->
    <div class="no-print" style="text-align: center; margin-top: 30px;">
        <button onclick="window.print()" style="
            padding: 10px 20px;
            background-color: #1a237e;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;">
            <i class="fas fa-print"></i> Print Order
        </button>
    </div>
</body>
</html> 