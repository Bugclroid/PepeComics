<?php
// Set error handling
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', dirname(__FILE__) . '/../../logs/php_errors.log');

set_error_handler(function($errno, $errstr, $errfile, $errline) {
    $message = sprintf(
        "[%s] Error: [%d] %s in %s:%d\n",
        date('Y-m-d H:i:s'),
        $errno,
        $errstr,
        $errfile,
        $errline
    );
    error_log($message);
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
});

require_once '../db.php';
require_once '../helpers.php';
require_once '../models/Order.php';
require_once '../models/Cart.php';
require_once '../models/Payment.php';

// Set JSON response headers
header('Content-Type: application/json');

class OrdersController {
    private $pdo;
    private $orderModel;
    private $cartModel;
    private $paymentModel;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->orderModel = new Order($pdo);
        $this->cartModel = new Cart($pdo);
        $this->paymentModel = new Payment($pdo);
    }

    public function handleRequest() {
        try {
            // Check if user is logged in
            if (!isLoggedIn()) {
                $this->sendJsonResponse(false, 'Please log in to manage orders.');
                return;
            }

            // Get JSON input
            $jsonInput = file_get_contents('php://input');
            if (empty($jsonInput)) {
                $this->sendJsonResponse(false, 'No data received.');
                return;
            }

            $data = json_decode($jsonInput, true);
            if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
                $this->sendJsonResponse(false, 'Invalid JSON data received: ' . json_last_error_msg());
                return;
            }

            $action = $data['action'] ?? '';

            switch ($action) {
                case 'create':
                    $this->createOrder($data);
                    break;
                case 'track':
                    $this->trackOrder();
                    break;
                case 'list':
                    $this->listOrders();
                    break;
                case 'details':
                    $this->getOrderDetails();
                    break;
                default:
                    $this->sendJsonResponse(false, 'Invalid action.');
                    break;
            }
        } catch (Exception $e) {
            error_log($e->getMessage());
            $this->sendJsonResponse(false, 'An error occurred: ' . $e->getMessage());
        }
    }

    private function createOrder($data) {
        try {
            error_log("[" . date('Y-m-d H:i:s') . "] Starting order creation process");
            error_log("[" . date('Y-m-d H:i:s') . "] Data received: " . print_r($data, true));

            // Validate required fields first, before starting transaction
            $requiredFields = ['name', 'email', 'phone', 'address', 'city', 'state', 'postal_code', 'payment_method'];
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    error_log("[" . date('Y-m-d H:i:s') . "] Error: Missing required field: {$field}");
                    $this->sendJsonResponse(false, "Please provide your {$field}.");
                    return;
                }
            }

            // Get cart items before starting transaction
            $cartItems = $this->cartModel->getCartItems($_SESSION['user_id']);
            error_log("[" . date('Y-m-d H:i:s') . "] Cart items retrieved: " . print_r($cartItems, true));

            if (empty($cartItems)) {
                error_log("[" . date('Y-m-d H:i:s') . "] Error: Cart is empty");
                $this->sendJsonResponse(false, 'Your cart is empty.');
                return;
            }

            // Make sure there are no active transactions
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            // Start transaction
            $this->pdo->beginTransaction();
            error_log("[" . date('Y-m-d H:i:s') . "] Transaction started");

            // Format shipping address
            $shippingAddress = sprintf(
                "%s, %s, %s %s",
                $data['address'],
                $data['city'],
                $data['state'],
                $data['postal_code']
            );
            error_log("[" . date('Y-m-d H:i:s') . "] Shipping address formatted: " . $shippingAddress);

            // Create order
            $orderData = [
                'user_id' => $_SESSION['user_id'],
                'shipping_address' => $shippingAddress,
                'total_amount' => $data['total']
            ];
            error_log("[" . date('Y-m-d H:i:s') . "] Attempting to create order with data: " . print_r($orderData, true));

            $orderId = $this->orderModel->createOrder($orderData);
            error_log("[" . date('Y-m-d H:i:s') . "] Order created with ID: " . ($orderId ?: 'Failed'));

            if (!$orderId) {
                error_log("[" . date('Y-m-d H:i:s') . "] Error: Failed to create order");
                throw new Exception('Failed to create order.');
            }

            // Create payment record
            $paymentData = [
                'order_id' => $orderId,
                'amount' => $data['total'],
                'payment_method' => $data['payment_method'],
                'status' => $data['payment_method'] === 'Cash' ? 'Pending' : 'Completed'
            ];
            error_log("[" . date('Y-m-d H:i:s') . "] Attempting to create payment with data: " . print_r($paymentData, true));

            $paymentSuccess = $this->paymentModel->createPayment($paymentData);
            error_log("[" . date('Y-m-d H:i:s') . "] Payment creation result: " . ($paymentSuccess ? 'Success' : 'Failed'));

            if (!$paymentSuccess) {
                error_log("[" . date('Y-m-d H:i:s') . "] Error: Failed to create payment record");
                throw new Exception('Failed to create payment record.');
            }

            // Clear cart
            $clearCartResult = $this->cartModel->clearCart($_SESSION['user_id']);
            error_log("[" . date('Y-m-d H:i:s') . "] Cart cleared result: " . ($clearCartResult ? 'Success' : 'Failed'));

            if (!$clearCartResult) {
                throw new Exception('Failed to clear cart.');
            }

            // Commit transaction
            $this->pdo->commit();
            error_log("[" . date('Y-m-d H:i:s') . "] Transaction committed successfully");

            $this->sendJsonResponse(true, 'Order created successfully.', [
                'order_id' => $orderId
            ]);
        } catch (Exception $e) {
            // Make sure to roll back if there's an active transaction
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
                error_log("[" . date('Y-m-d H:i:s') . "] Transaction rolled back");
            }
            error_log("[" . date('Y-m-d H:i:s') . "] Error in createOrder: " . $e->getMessage());
            error_log("[" . date('Y-m-d H:i:s') . "] Stack trace: " . $e->getTraceAsString());
            $this->sendJsonResponse(false, 'An error occurred while processing your order: ' . $e->getMessage());
        }
    }

    private function trackOrder() {
        try {
            $orderId = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;
            
            if (!$orderId) {
                $this->sendJsonResponse(false, 'Invalid order ID.');
                return;
            }

            // Get order details
            $order = $this->orderModel->getOrderById($orderId);
            
            if (!$order || $order['user_id'] !== $_SESSION['user_id']) {
                $this->sendJsonResponse(false, 'Order not found.');
                return;
            }

            // Get order items
            $items = $this->orderModel->getOrderItems($orderId);
            
            // Get payment status
            $payment = $this->paymentModel->getPaymentByOrderId($orderId);

            // Mock tracking stages
            $stages = [
                'Order Placed' => true,
                'Payment Confirmed' => $payment['status'] === 'Completed',
                'Processing' => $payment['status'] === 'Completed',
                'Shipped' => false,
                'Delivered' => false
            ];

            $this->sendJsonResponse(true, 'Order tracking information retrieved.', [
                'order' => $order,
                'items' => $items,
                'payment' => $payment,
                'tracking' => $stages
            ]);
        } catch (Exception $e) {
            error_log($e->getMessage());
            $this->sendJsonResponse(false, 'An error occurred while tracking your order.');
        }
    }

    private function listOrders() {
        try {
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
            
            $orders = $this->orderModel->getUserOrders($_SESSION['user_id'], $page, $limit);
            $total = $this->orderModel->getUserOrdersCount($_SESSION['user_id']);

            $this->sendJsonResponse(true, 'Orders retrieved successfully.', [
                'orders' => $orders,
                'total' => $total,
                'page' => $page,
                'limit' => $limit
            ]);
        } catch (Exception $e) {
            error_log($e->getMessage());
            $this->sendJsonResponse(false, 'An error occurred while retrieving your orders.');
        }
    }

    private function getOrderDetails() {
        try {
            $orderId = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;
            
            if (!$orderId) {
                $this->sendJsonResponse(false, 'Invalid order ID.');
                return;
            }

            $order = $this->orderModel->getOrderById($orderId);
            
            if (!$order || $order['user_id'] !== $_SESSION['user_id']) {
                $this->sendJsonResponse(false, 'Order not found.');
                return;
            }

            $items = $this->orderModel->getOrderItems($orderId);
            $payment = $this->paymentModel->getPaymentByOrderId($orderId);

            $this->sendJsonResponse(true, 'Order details retrieved successfully.', [
                'order' => $order,
                'items' => $items,
                'payment' => $payment
            ]);
        } catch (Exception $e) {
            error_log($e->getMessage());
            $this->sendJsonResponse(false, 'An error occurred while retrieving order details.');
        }
    }

    private function sendJsonResponse($success, $message, $data = []) {
        echo json_encode([
            'success' => $success,
            'message' => $message,
            'data' => $data
        ]);
        exit;
    }
}

// Initialize controller and handle request
try {
    $controller = new OrdersController($pdo);
    $controller->handleRequest();
} catch (Exception $e) {
    error_log($e->getMessage());
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred: ' . $e->getMessage()
    ]);
} 