<?php
class User {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function createUser($name, $email, $password, $phone = null, $address = null) {
        $password_hash = password_hash($password, PASSWORD_BCRYPT);
        
        $stmt = $this->pdo->prepare('INSERT INTO users (name, email, phone, address, password_hash) 
                                   VALUES (:name, :email, :phone, :address, :password_hash)');
        
        return $stmt->execute([
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'address' => $address,
            'password_hash' => $password_hash
        ]);
    }

    public function updateExistingUsersCreatedAt() {
        try {
            // Update users who have null created_at
            $stmt = $this->pdo->prepare('
                UPDATE users 
                SET created_at = CURRENT_TIMESTAMP 
                WHERE created_at IS NULL
            ');
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error updating existing users created_at: " . $e->getMessage());
            return false;
        }
    }

    public function getUserByEmail($email) {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE email = :email');
        $stmt->execute(['email' => $email]);
        return $stmt->fetch();
    }

    public function getUserById($userId) {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE user_id = :user_id');
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetch();
    }

    public function updateUser($userId, $data) {
        $allowedFields = ['name', 'phone', 'address'];
        $updates = array_intersect_key($data, array_flip($allowedFields));
        
        if (empty($updates)) {
            return false;
        }

        $sql = 'UPDATE users SET ' . 
               implode(', ', array_map(fn($field) => "$field = :$field", array_keys($updates))) .
               ' WHERE user_id = :user_id';
        
        $updates['user_id'] = $userId;
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($updates);
    }

    public function updatePassword($userId, $newPassword) {
        $password_hash = password_hash($newPassword, PASSWORD_BCRYPT);
        $stmt = $this->pdo->prepare('UPDATE users SET password_hash = :password_hash WHERE user_id = :user_id');
        return $stmt->execute([
            'password_hash' => $password_hash,
            'user_id' => $userId
        ]);
    }

    public function verifyPassword($userId, $password) {
        $stmt = $this->pdo->prepare('SELECT password_hash FROM users WHERE user_id = :user_id');
        $stmt->execute(['user_id' => $userId]);
        $user = $stmt->fetch();
        
        return $user && password_verify($password, $user['password_hash']);
    }

    public function getTotalUsers() {
        $stmt = $this->pdo->query('SELECT COUNT(*) as total FROM users');
        return $stmt->fetch()['total'];
    }

    public function getTotalAdmins() {
        try {
            $stmt = $this->pdo->query('SELECT COUNT(*) as total FROM users WHERE is_admin = 1');
            return $stmt->fetch()['total'];
        } catch (Exception $e) {
            error_log("Error getting total admins: " . $e->getMessage());
            return 0;
        }
    }

    public function getTotalCustomers() {
        try {
            $stmt = $this->pdo->query('SELECT COUNT(*) as total FROM users WHERE is_admin = 0');
            return $stmt->fetch()['total'];
        } catch (Exception $e) {
            error_log("Error getting total customers: " . $e->getMessage());
            return 0;
        }
    }

    public function getUsersWithFilters($role = '', $search = '', $sort = 'name_asc') {
        try {
            $params = [];
            $conditions = [];

            // Base query
            $sql = "SELECT u.*,
                          (SELECT COUNT(*) FROM orders o WHERE o.user_id = u.user_id) as total_orders,
                          (SELECT COALESCE(SUM(oi.total_price), 0) 
                           FROM orders o 
                           JOIN order_items oi ON o.order_id = oi.order_id 
                           WHERE o.user_id = u.user_id) as total_spent
                   FROM users u";

            // Add role filter
            if (!empty($role)) {
                if ($role === 'admin') {
                    $conditions[] = "u.is_admin = 1";
                } else if ($role === 'user') {
                    $conditions[] = "u.is_admin = 0";
                }
            }

            // Add search filter
            if (!empty($search)) {
                $conditions[] = "(u.name LIKE :search 
                                OR u.email LIKE :search 
                                OR u.phone LIKE :search)";
                $params['search'] = "%$search%";
            }

            // Add WHERE clause if there are conditions
            if (!empty($conditions)) {
                $sql .= " WHERE " . implode(" AND ", $conditions);
            }

            // Add sorting
            switch ($sort) {
                case 'name_asc':
                    $sql .= " ORDER BY u.name ASC";
                    break;
                case 'name_desc':
                    $sql .= " ORDER BY u.name DESC";
                    break;
                case 'orders':
                    $sql .= " ORDER BY total_orders DESC";
                    break;
                case 'spent':
                    $sql .= " ORDER BY total_spent DESC";
                    break;
                default:
                    $sql .= " ORDER BY u.name ASC";
            }

            // Prepare and execute query
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error getting filtered users: " . $e->getMessage());
            return [];
        }
    }

    public function storeRememberToken($userId, $token, $expiry) {
        // Store token in session instead of database
        $_SESSION['remember_token'] = [
            'user_id' => $userId,
            'token' => $token,
            'expiry' => $expiry
        ];
        return true;
    }

    public function removeRememberToken($token) {
        if (isset($_SESSION['remember_token']) && $_SESSION['remember_token']['token'] === $token) {
            unset($_SESSION['remember_token']);
        }
        return true;
    }
} 