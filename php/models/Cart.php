<?php
class Cart {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function addToCart($userId, $comicId, $quantity = 1) {
        try {
            $this->pdo->beginTransaction();

            // Check if comic exists and has enough stock
            $stmt = $this->pdo->prepare('SELECT stock FROM comics WHERE comic_id = :comic_id');
            $stmt->execute(['comic_id' => $comicId]);
            $comic = $stmt->fetch();

            error_log("Adding to cart - Comic: " . print_r($comic, true));

            if (!$comic || $comic['stock'] < $quantity) {
                $this->pdo->rollBack();
                error_log("Failed to add to cart - Invalid stock. Comic: " . print_r($comic, true));
                return false;
            }

            // Check if item already exists in cart
            $stmt = $this->pdo->prepare('SELECT quantity FROM cart WHERE user_id = :user_id AND comic_id = :comic_id');
            $stmt->execute([
                'user_id' => $userId,
                'comic_id' => $comicId
            ]);
            $cartItem = $stmt->fetch();

            error_log("Existing cart item: " . print_r($cartItem, true));

            if ($cartItem) {
                // Update quantity if item exists
                $newQuantity = $cartItem['quantity'] + $quantity;
                if ($newQuantity > $comic['stock']) {
                    $this->pdo->rollBack();
                    error_log("Failed to add to cart - Exceeds stock. New quantity: " . $newQuantity);
                    return false;
                }

                $stmt = $this->pdo->prepare('UPDATE cart SET quantity = :quantity 
                                           WHERE user_id = :user_id AND comic_id = :comic_id');
                $result = $stmt->execute([
                    'quantity' => $newQuantity,
                    'user_id' => $userId,
                    'comic_id' => $comicId
                ]);
            } else {
                // Insert new item if it doesn't exist
                $stmt = $this->pdo->prepare('INSERT INTO cart (user_id, comic_id, quantity) 
                                           VALUES (:user_id, :comic_id, :quantity)');
                $result = $stmt->execute([
                    'user_id' => $userId,
                    'comic_id' => $comicId,
                    'quantity' => $quantity
                ]);
            }

            if ($result) {
                $this->pdo->commit();
                error_log("Successfully added/updated cart item");
                return true;
            } else {
                $this->pdo->rollBack();
                error_log("Failed to execute cart query");
                return false;
            }
        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log("Error adding to cart: " . $e->getMessage());
            return false;
        }
    }

    public function updateCartItem($userId, $comicId, $quantity) {
        if ($quantity <= 0) {
            return $this->removeFromCart($userId, $comicId);
        }

        // Check stock availability
        $stmt = $this->pdo->prepare('SELECT stock FROM comics WHERE comic_id = :comic_id');
        $stmt->execute(['comic_id' => $comicId]);
        $comic = $stmt->fetch();

        if (!$comic || $comic['stock'] < $quantity) {
            return false;
        }

        $stmt = $this->pdo->prepare('UPDATE cart SET quantity = :quantity 
                                   WHERE user_id = :user_id AND comic_id = :comic_id');
        return $stmt->execute([
            'quantity' => $quantity,
            'user_id' => $userId,
            'comic_id' => $comicId
        ]);
    }

    public function removeFromCart($userId, $comicId) {
        $stmt = $this->pdo->prepare('DELETE FROM cart WHERE user_id = :user_id AND comic_id = :comic_id');
        return $stmt->execute([
            'user_id' => $userId,
            'comic_id' => $comicId
        ]);
    }

    public function getCartItems($userId) {
        $query = 'SELECT c.comic_id, c.title, c.price, c.stock, c.image_name as image, c.author,
                         cart.quantity, (c.price * cart.quantity) as total_price 
                  FROM cart 
                  JOIN comics c ON cart.comic_id = c.comic_id 
                  WHERE cart.user_id = :user_id';
        
        // Debug information
        error_log("Cart Query: " . $query);
        error_log("User ID: " . $userId);
        
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['user_id' => $userId]);
        $results = $stmt->fetchAll();
        
        // Debug results
        error_log("Cart Results: " . print_r($results, true));
        
        return $results;
    }

    public function getCartTotal($userId) {
        $stmt = $this->pdo->prepare('SELECT SUM(c.price * cart.quantity) as total 
                                    FROM cart 
                                    JOIN comics c ON cart.comic_id = c.comic_id 
                                    WHERE cart.user_id = :user_id');
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetch()['total'] ?? 0;
    }

    public function clearCart($userId) {
        $stmt = $this->pdo->prepare('DELETE FROM cart WHERE user_id = :user_id');
        return $stmt->execute(['user_id' => $userId]);
    }

    public function getCartItemCount($userId) {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) as count FROM cart WHERE user_id = :user_id');
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetch()['count'];
    }
} 