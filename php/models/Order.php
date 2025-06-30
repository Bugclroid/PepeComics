<?php
class Order {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function createOrder($data) {
        try {
            // Create order
            $stmt = $this->pdo->prepare('INSERT INTO orders (user_id, shipping_address, total_amount) 
                                       VALUES (:user_id, :shipping_address, :total_amount)');
            $stmt->execute([
                'user_id' => $data['user_id'],
                'shipping_address' => $data['shipping_address'],
                'total_amount' => $data['total_amount']
            ]);
            $orderId = $this->pdo->lastInsertId();

            // Add order items
            $stmt = $this->pdo->prepare('INSERT INTO order_items (order_id, comic_id, quantity, total_price) 
                                       VALUES (:order_id, :comic_id, :quantity, :total_price)');

            // Get cart items
            $cartModel = new Cart($this->pdo);
            $cartItems = $cartModel->getCartItems($data['user_id']);

            if (empty($cartItems)) {
                throw new Exception('Cart is empty');
            }

            foreach ($cartItems as $item) {
                // Verify stock availability
                $comicStmt = $this->pdo->prepare('SELECT stock FROM comics WHERE comic_id = :comic_id FOR UPDATE');
                $comicStmt->execute(['comic_id' => $item['comic_id']]);
                $comic = $comicStmt->fetch();

                if ($comic['stock'] < $item['quantity']) {
                    throw new Exception("Insufficient stock for comic ID: {$item['comic_id']}");
                }

                // Update stock
                $updateStmt = $this->pdo->prepare('UPDATE comics SET stock = stock - :quantity 
                                                 WHERE comic_id = :comic_id');
                $updateStmt->execute([
                    'quantity' => $item['quantity'],
                    'comic_id' => $item['comic_id']
                ]);

                // Add order item
                $stmt->execute([
                    'order_id' => $orderId,
                    'comic_id' => $item['comic_id'],
                    'quantity' => $item['quantity'],
                    'total_price' => $item['total_price']
                ]);
            }

            return $orderId;
        } catch (Exception $e) {
            error_log($e->getMessage());
            throw $e; // Re-throw to let controller handle it
        }
    }

    public function getOrder($orderId) {
        // Get basic order details
        $stmt = $this->pdo->prepare('
            SELECT o.*, 
                   u.name as shipping_name, 
                   u.email,
                   p.payment_method,
                   p.status as payment_status
            FROM orders o 
            JOIN users u ON o.user_id = u.user_id 
            LEFT JOIN payments p ON o.order_id = p.order_id
            WHERE o.order_id = :order_id
        ');
        $stmt->execute(['order_id' => $orderId]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($order) {
            // Parse shipping address
            $addressParts = explode(',', $order['shipping_address']);
            $order['shipping_address'] = trim($addressParts[0]);
            $order['shipping_city'] = isset($addressParts[1]) ? trim($addressParts[1]) : '';
            $order['shipping_state'] = isset($addressParts[2]) ? trim(explode(' ', trim($addressParts[2]))[0]) : '';
            $order['shipping_zip'] = isset($addressParts[2]) ? trim(substr(strstr($addressParts[2], ' '), 1)) : '';

            // Get order items
            $order['items'] = $this->getOrderItems($orderId);
            $order['total'] = $this->getOrderTotal($orderId);
        }

        return $order;
    }

    public function getOrderItems($orderId) {
        try {
            $stmt = $this->pdo->prepare('
                SELECT oi.*,
                       c.title,
                       c.author,
                       c.price,
                       oi.quantity,
                       oi.total_price
                FROM order_items oi
                JOIN comics c ON oi.comic_id = c.comic_id
                WHERE oi.order_id = :order_id
                ORDER BY oi.order_item_id ASC
            ');
            
            $stmt->execute(['order_id' => $orderId]);
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // If no items found, log an error
            if (empty($items)) {
                error_log("No items found for order ID: " . $orderId);
            }
            
            return $items;
        } catch (Exception $e) {
            error_log("Error getting order items: " . $e->getMessage());
            return [];
        }
    }

    public function getOrderTotal($orderId) {
        $stmt = $this->pdo->prepare('SELECT SUM(total_price) as total 
                                    FROM order_items 
                                    WHERE order_id = :order_id');
        $stmt->execute(['order_id' => $orderId]);
        return $stmt->fetch()['total'];
    }

    public function getUserOrders($userId, $page = 1, $limit = 10) {
        $offset = ($page - 1) * $limit;
        $stmt = $this->pdo->prepare('SELECT o.*, 
                                           p.status as payment_status,
                                           (SELECT SUM(total_price) FROM order_items WHERE order_id = o.order_id) as total 
                                    FROM orders o 
                                    LEFT JOIN payments p ON o.order_id = p.order_id
                                    WHERE o.user_id = :user_id 
                                    ORDER BY o.order_date DESC
                                    LIMIT :limit OFFSET :offset');
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getUserOrdersCount($userId) {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) as count FROM orders WHERE user_id = :user_id');
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetch()['count'];
    }

    public function getOrderById($orderId) {
        try {
            $stmt = $this->pdo->prepare('
                SELECT o.*,
                       u.name as customer_name,
                       u.email,
                       u.phone,
                       p.status as payment_status,
                       p.payment_method,
                       (SELECT SUM(total_price) FROM order_items WHERE order_id = o.order_id) as subtotal,
                       (SELECT SUM(total_price) * 0.1 FROM order_items WHERE order_id = o.order_id) as tax,
                       (SELECT SUM(total_price) * 1.1 FROM order_items WHERE order_id = o.order_id) as total_amount
                FROM orders o
                LEFT JOIN users u ON o.user_id = u.user_id
                LEFT JOIN payments p ON o.order_id = p.order_id
                WHERE o.order_id = :order_id
            ');
            $stmt->execute(['order_id' => $orderId]);
            $order = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($order) {
                // Parse shipping address if it exists
                if (!empty($order['shipping_address'])) {
                    $addressParts = explode(',', $order['shipping_address']);
                    $order['shipping_address'] = isset($addressParts[0]) ? trim($addressParts[0]) : '';
                    $order['shipping_city'] = isset($addressParts[1]) ? trim($addressParts[1]) : '';
                    $order['shipping_state'] = isset($addressParts[2]) ? trim(explode(' ', trim($addressParts[2] ?? ''))[0] ?? '') : '';
                    $order['shipping_postal_code'] = isset($addressParts[2]) ? trim(substr(strstr($addressParts[2] ?? '', ' ') ?? '', 1)) : '';
                } else {
                    $order['shipping_address'] = '';
                    $order['shipping_city'] = '';
                    $order['shipping_state'] = '';
                    $order['shipping_postal_code'] = '';
                }

                // Set default status if not set
                if (!isset($order['status'])) {
                    $order['status'] = 'pending';
                }

                // Set default payment status if not set
                if (!isset($order['payment_status'])) {
                    $order['payment_status'] = 'pending';
                }
            }

            return $order;
        } catch (Exception $e) {
            error_log("Error getting order by ID: " . $e->getMessage());
            return null;
        }
    }

    public function getTotalOrders() {
        $stmt = $this->pdo->query('SELECT COUNT(*) as total FROM orders');
        return $stmt->fetch()['total'];
    }

    public function getRecentOrders($limit = 5) {
        $stmt = $this->pdo->prepare('
            SELECT o.*, u.name as customer_name, 
                   (SELECT SUM(total_price) FROM order_items WHERE order_id = o.order_id) as total_amount,
                   p.status
            FROM orders o 
            JOIN users u ON o.user_id = u.user_id 
            LEFT JOIN payments p ON o.order_id = p.order_id
            ORDER BY o.order_date DESC 
            LIMIT :limit
        ');
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getMonthlyRevenue() {
        $stmt = $this->pdo->prepare('
            SELECT COALESCE(SUM(oi.total_price), 0) as revenue
            FROM orders o
            JOIN order_items oi ON o.order_id = oi.order_id
            WHERE o.order_date >= DATE_SUB(CURRENT_DATE, INTERVAL 1 MONTH)
        ');
        $stmt->execute();
        return $stmt->fetch()['revenue'];
    }

    public function getOrdersWithFilters($status = '', $dateRange = '', $search = '', $sort = 'date_desc') {
        try {
            $params = [];
            $conditions = [];

            // Base query
            $sql = "SELECT o.*, 
                          u.name as customer_name,
                          u.email as customer_email,
                          p.status as payment_status,
                          o.status as order_status,
                          (SELECT SUM(total_price) FROM order_items WHERE order_id = o.order_id) as total_amount
                   FROM orders o
                   JOIN users u ON o.user_id = u.user_id
                   LEFT JOIN payments p ON o.order_id = p.order_id";

            // Add status filter
            if (!empty($status)) {
                $conditions[] = "o.status = :status";
                $params['status'] = strtolower($status);
            }

            // Add date range filter
            if (!empty($dateRange)) {
                switch ($dateRange) {
                    case 'today':
                        $conditions[] = "DATE(o.order_date) = CURDATE()";
                        break;
                    case 'week':
                        $conditions[] = "o.order_date >= DATE_SUB(CURDATE(), INTERVAL 1 WEEK)";
                        break;
                    case 'month':
                        $conditions[] = "o.order_date >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)";
                        break;
                }
            }

            // Add search filter
            if (!empty($search)) {
                $conditions[] = "(o.order_id LIKE :search 
                                OR u.name LIKE :search 
                                OR u.email LIKE :search)";
                $params['search'] = "%$search%";
            }

            // Add WHERE clause if there are conditions
            if (!empty($conditions)) {
                $sql .= " WHERE " . implode(" AND ", $conditions);
            }

            // Add sorting
            switch ($sort) {
                case 'date_asc':
                    $sql .= " ORDER BY o.order_date ASC";
                    break;
                case 'date_desc':
                    $sql .= " ORDER BY o.order_date DESC";
                    break;
                case 'total_asc':
                    $sql .= " ORDER BY total_amount ASC";
                    break;
                case 'total_desc':
                    $sql .= " ORDER BY total_amount DESC";
                    break;
                case 'status':
                    $sql .= " ORDER BY o.status ASC";
                    break;
            }

            // Prepare and execute query
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error getting filtered orders: " . $e->getMessage());
            return [];
        }
    }

    public function updateOrderStatus($orderId, $status) {
        try {
            $this->pdo->beginTransaction();

            // Update order status
            $stmt = $this->pdo->prepare('UPDATE orders SET status = :status WHERE order_id = :order_id');
            $success = $stmt->execute([
                'order_id' => $orderId,
                'status' => $status
            ]);

            if (!$success) {
                throw new Exception("Failed to update order status");
            }

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log("Error updating order status: " . $e->getMessage());
            return false;
        }
    }

    public function getTotalRevenue() {
        try {
            $stmt = $this->pdo->prepare('
                SELECT COALESCE(SUM(oi.total_price), 0) as total_revenue
                FROM orders o
                JOIN order_items oi ON o.order_id = oi.order_id
                JOIN payments p ON o.order_id = p.order_id
                WHERE p.status = "Completed"
            ');
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC)['total_revenue'];
        } catch (Exception $e) {
            error_log("Error calculating total revenue: " . $e->getMessage());
            return 0;
        }
    }

    public function getUserOrderCount($userId) {
        try {
            $stmt = $this->pdo->prepare('
                SELECT COUNT(*) as count
                FROM orders
                WHERE user_id = :user_id
            ');
            $stmt->execute(['user_id' => $userId]);
            return $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        } catch (Exception $e) {
            error_log("Error getting user order count: " . $e->getMessage());
            return 0;
        }
    }

    public function getUserTotalSpent($userId) {
        try {
            $stmt = $this->pdo->prepare('
                SELECT COALESCE(SUM(oi.total_price), 0) as total_spent
                FROM orders o
                JOIN order_items oi ON o.order_id = oi.order_id
                WHERE o.user_id = :user_id
            ');
            $stmt->execute(['user_id' => $userId]);
            return $stmt->fetch(PDO::FETCH_ASSOC)['total_spent'];
        } catch (Exception $e) {
            error_log("Error getting user total spent: " . $e->getMessage());
            return 0;
        }
    }
} 