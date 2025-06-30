<?php
require_once '../db.php';
require_once '../helpers.php';
require_once '../models/Payment.php';
require_once '../models/Order.php';

class PaymentsController {
    private $pdo;
    private $paymentModel;
    private $orderModel;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->paymentModel = new Payment($pdo);
        $this->orderModel = new Order($pdo);
    }

    public function handleRequest() {
        // Check if user is logged in
        if (!isLoggedIn()) {
            $this->sendJsonResponse(false, 'Please log in to process payments.');
            return;
        }

        $action = $_REQUEST['action'] ?? '';

        switch ($action) {
            case 'process':
                $this->processPayment();
                break;
            case 'verify':
                $this->verifyPayment();
                break;
            case 'status':
                $this->getPaymentStatus();
                break;
            default:
                $this->sendJsonResponse(false, 'Invalid action.');
                break;
        }
    }

    private function processPayment() {
        try {
            // Start transaction
            $this->pdo->beginTransaction();

            // Get payment details
            $orderId = isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0;
            $paymentMethod = $_POST['payment_method'] ?? '';
            $cardNumber = $_POST['card_number'] ?? '';
            $expiryDate = $_POST['expiry_date'] ?? '';
            $cvv = $_POST['cvv'] ?? '';

            // Validate input
            if (!$orderId || !$paymentMethod || !$cardNumber || !$expiryDate || !$cvv) {
                $this->sendJsonResponse(false, 'All payment fields are required.');
                return;
            }

            // Get order details
            $order = $this->orderModel->getOrderById($orderId);
            if (!$order || $order['user_id'] !== $_SESSION['user_id']) {
                $this->sendJsonResponse(false, 'Invalid order.');
                return;
            }

            // Check if payment already exists
            $existingPayment = $this->paymentModel->getPaymentByOrderId($orderId);
            if ($existingPayment && $existingPayment['status'] === 'Completed') {
                $this->sendJsonResponse(false, 'Payment has already been processed.');
                return;
            }

            // Validate card details (mock validation)
            if (!$this->validateCardDetails($cardNumber, $expiryDate, $cvv)) {
                $this->sendJsonResponse(false, 'Invalid card details.');
                return;
            }

            // Process payment (mock processing)
            $success = $this->mockPaymentProcessing();

            if ($success) {
                // Update payment record
                $paymentSuccess = $this->paymentModel->updatePayment([
                    'order_id' => $orderId,
                    'payment_method' => $paymentMethod,
                    'status' => 'Completed',
                    'amount' => $order['total_amount']
                ]);

                if (!$paymentSuccess) {
                    throw new Exception('Failed to update payment record.');
                }

                // Commit transaction
                $this->pdo->commit();

                $this->sendJsonResponse(true, 'Payment processed successfully.');
            } else {
                throw new Exception('Payment processing failed.');
            }
        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log($e->getMessage());
            $this->sendJsonResponse(false, 'An error occurred while processing your payment.');
        }
    }

    private function verifyPayment() {
        try {
            $orderId = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;
            
            if (!$orderId) {
                $this->sendJsonResponse(false, 'Invalid order ID.');
                return;
            }

            // Get payment details
            $payment = $this->paymentModel->getPaymentByOrderId($orderId);
            
            if (!$payment) {
                $this->sendJsonResponse(false, 'Payment not found.');
                return;
            }

            // Get order details to verify user
            $order = $this->orderModel->getOrderById($orderId);
            if (!$order || $order['user_id'] !== $_SESSION['user_id']) {
                $this->sendJsonResponse(false, 'Unauthorized access.');
                return;
            }

            $this->sendJsonResponse(true, 'Payment verification successful.', [
                'payment' => $payment
            ]);
        } catch (Exception $e) {
            error_log($e->getMessage());
            $this->sendJsonResponse(false, 'An error occurred while verifying payment.');
        }
    }

    private function getPaymentStatus() {
        try {
            $orderId = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;
            
            if (!$orderId) {
                $this->sendJsonResponse(false, 'Invalid order ID.');
                return;
            }

            // Get payment details
            $payment = $this->paymentModel->getPaymentByOrderId($orderId);
            
            if (!$payment) {
                $this->sendJsonResponse(false, 'Payment not found.');
                return;
            }

            // Get order details to verify user
            $order = $this->orderModel->getOrderById($orderId);
            if (!$order || $order['user_id'] !== $_SESSION['user_id']) {
                $this->sendJsonResponse(false, 'Unauthorized access.');
                return;
            }

            $this->sendJsonResponse(true, 'Payment status retrieved.', [
                'status' => $payment['status'],
                'payment_date' => $payment['payment_date'],
                'amount' => $payment['amount'],
                'payment_method' => $payment['payment_method']
            ]);
        } catch (Exception $e) {
            error_log($e->getMessage());
            $this->sendJsonResponse(false, 'An error occurred while retrieving payment status.');
        }
    }

    private function validateCardDetails($cardNumber, $expiryDate, $cvv) {
        // Mock card validation
        // In a real application, this would integrate with a payment gateway
        
        // Remove spaces and dashes from card number
        $cardNumber = preg_replace('/[\s-]/', '', $cardNumber);
        
        // Basic validation
        if (!preg_match('/^\d{16}$/', $cardNumber)) {
            return false;
        }

        // Validate expiry date (MM/YY format)
        if (!preg_match('/^(0[1-9]|1[0-2])\/([0-9]{2})$/', $expiryDate, $matches)) {
            return false;
        }

        $month = $matches[1];
        $year = '20' . $matches[2];
        $expiry = new DateTime("$year-$month-01");
        $now = new DateTime();

        if ($expiry < $now) {
            return false;
        }

        // Validate CVV (3 or 4 digits)
        if (!preg_match('/^\d{3,4}$/', $cvv)) {
            return false;
        }

        return true;
    }

    private function mockPaymentProcessing() {
        // Simulate payment processing delay
        sleep(1);
        
        // Mock success rate (90% success)
        return rand(1, 100) <= 90;
    }

    private function sendJsonResponse($success, $message, $data = []) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => $success,
            'message' => $message,
            'data' => $data
        ]);
        exit;
    }
}

// Initialize controller and handle request
$controller = new PaymentsController($pdo);
$controller->handleRequest(); 