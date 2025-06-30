<?php
class Payment {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function createPayment($data) {
        try {
            $stmt = $this->pdo->prepare('INSERT INTO payments (order_id, amount, payment_method, status) 
                                       VALUES (:order_id, :amount, :payment_method, :status)');
            
            $success = $stmt->execute([
                'order_id' => $data['order_id'],
                'amount' => $data['amount'],
                'payment_method' => $data['payment_method'],
                'status' => $data['status']
            ]);

            if (!$success) {
                throw new Exception('Failed to create payment record');
            }

            return $this->pdo->lastInsertId();
        } catch (Exception $e) {
            error_log($e->getMessage());
            throw $e; // Re-throw to let controller handle it
        }
    }

    public function updatePaymentStatus($paymentId, $status) {
        $validStatuses = ['Pending', 'Completed', 'Failed'];
        if (!in_array($status, $validStatuses)) {
            return false;
        }

        $stmt = $this->pdo->prepare('UPDATE payments SET status = :status WHERE payment_id = :payment_id');
        return $stmt->execute([
            'status' => $status,
            'payment_id' => $paymentId
        ]);
    }

    public function getPayment($paymentId) {
        $stmt = $this->pdo->prepare('SELECT p.*, o.user_id, u.name as user_name, u.email 
                                    FROM payments p 
                                    JOIN orders o ON p.order_id = o.order_id 
                                    JOIN users u ON o.user_id = u.user_id 
                                    WHERE p.payment_id = :payment_id');
        $stmt->execute(['payment_id' => $paymentId]);
        return $stmt->fetch();
    }

    public function getOrderPayments($orderId) {
        $stmt = $this->pdo->prepare('SELECT * FROM payments WHERE order_id = :order_id 
                                    ORDER BY payment_date DESC');
        $stmt->execute(['order_id' => $orderId]);
        return $stmt->fetchAll();
    }

    public function getUserPayments($userId) {
        $stmt = $this->pdo->prepare('SELECT p.*, o.order_date 
                                    FROM payments p 
                                    JOIN orders o ON p.order_id = o.order_id 
                                    WHERE o.user_id = :user_id 
                                    ORDER BY p.payment_date DESC');
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public function processPayment($paymentId) {
        // This is a mock implementation
        // In a real application, this would integrate with a payment gateway
        $payment = $this->getPayment($paymentId);
        
        if (!$payment || $payment['status'] !== 'Pending') {
            return false;
        }

        // Simulate payment processing
        $success = (rand(1, 10) > 2); // 80% success rate for demonstration
        $status = $success ? 'Completed' : 'Failed';
        
        return $this->updatePaymentStatus($paymentId, $status);
    }

    public function getPaymentByOrderId($orderId) {
        try {
            $stmt = $this->pdo->prepare('
                SELECT p.*, o.user_id, u.name as user_name, u.email 
                FROM payments p 
                JOIN orders o ON p.order_id = o.order_id 
                JOIN users u ON o.user_id = u.user_id 
                WHERE p.order_id = :order_id 
                ORDER BY p.payment_date DESC 
                LIMIT 1
            ');
            $stmt->execute(['order_id' => $orderId]);
            $payment = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // If no payment found, return a default structure with Pending status
            if (!$payment) {
                return [
                    'status' => 'Pending',
                    'payment_method' => null,
                    'amount' => 0,
                    'payment_date' => null
                ];
            }
            
            return $payment;
        } catch (Exception $e) {
            error_log("Error getting payment for order: " . $e->getMessage());
            return [
                'status' => 'Error',
                'payment_method' => null,
                'amount' => 0,
                'payment_date' => null
            ];
        }
    }

    public function updatePayment($data) {
        try {
            $stmt = $this->pdo->prepare('UPDATE payments 
                                       SET payment_method = :payment_method,
                                           status = :status,
                                           updated_at = CURRENT_TIMESTAMP
                                       WHERE order_id = :order_id');
            
            return $stmt->execute([
                'order_id' => $data['order_id'],
                'payment_method' => $data['payment_method'],
                'status' => 'completed'
            ]);
        } catch (Exception $e) {
            error_log("Error updating payment: " . $e->getMessage());
            return false;
        }
    }
} 