<?php
require_once '../db.php';
require_once '../helpers.php';
require_once '../models/Cart.php';
require_once '../models/Comic.php';

class CartController {
    private $pdo;
    private $cartModel;
    private $comicModel;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->cartModel = new Cart($pdo);
        $this->comicModel = new Comic($pdo);
    }

    public function handleRequest() {
        startSession();

        // Check if user is logged in
        if (!isLoggedIn()) {
            $this->sendJsonResponse([
                'success' => false,
                'message' => 'Please log in to manage your cart.'
            ], 401);
            return;
        }

        // Get request data
        $requestData = json_decode(file_get_contents('php://input'), true);
        $action = $requestData['action'] ?? '';

        try {
            switch ($action) {
                case 'add':
                    $this->handleAddToCart($requestData);
                    break;
                case 'update':
                    $this->handleUpdateCart($requestData);
                    break;
                case 'remove':
                    $this->handleRemoveFromCart($requestData);
                    break;
                case 'clear':
                    $this->handleClearCart();
                    break;
                default:
                    throw new Exception('Invalid action.');
            }
        } catch (Exception $e) {
            $this->sendJsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    private function handleAddToCart($data) {
        // Check session
        if (!isset($_SESSION['user_id'])) {
            error_log("Cart Error: User not logged in");
            throw new Exception('Please log in to add items to cart.');
        }

        error_log("Cart Add - User ID: " . $_SESSION['user_id']);
        error_log("Cart Add - Data: " . print_r($data, true));

        // Validate input
        if (!isset($data['comic_id']) || !isset($data['quantity'])) {
            error_log("Cart Error: Missing parameters");
            throw new Exception('Missing required parameters.');
        }

        $comicId = (int)$data['comic_id'];
        $quantity = (int)$data['quantity'];

        // Validate quantity
        if ($quantity < 1) {
            error_log("Cart Error: Invalid quantity - " . $quantity);
            throw new Exception('Quantity must be at least 1.');
        }

        // Check if comic exists and has enough stock
        $comic = $this->comicModel->getComicById($comicId);
        error_log("Cart Add - Comic: " . print_r($comic, true));

        if (!$comic) {
            error_log("Cart Error: Comic not found - ID: " . $comicId);
            throw new Exception('Comic not found.');
        }

        if ($comic['stock'] < $quantity) {
            error_log("Cart Error: Insufficient stock - Requested: " . $quantity . ", Available: " . $comic['stock']);
            throw new Exception('Not enough stock available.');
        }

        // Add to cart
        $result = $this->cartModel->addToCart($_SESSION['user_id'], $comicId, $quantity);
        if ($result) {
            // Get updated cart info
            $cartCount = $this->cartModel->getCartItemCount($_SESSION['user_id']);
            $cartTotal = $this->cartModel->getCartTotal($_SESSION['user_id']);

            error_log("Cart Add Success - Count: " . $cartCount . ", Total: " . $cartTotal);

            $this->sendJsonResponse([
                'success' => true,
                'message' => 'Item added to cart successfully.',
                'cartCount' => $cartCount,
                'total' => $cartTotal
            ]);
        } else {
            error_log("Cart Error: Failed to add item");
            throw new Exception('Failed to add item to cart.');
        }
    }

    private function handleUpdateCart($data) {
        // Validate input
        if (!isset($data['comic_id']) || !isset($data['quantity'])) {
            throw new Exception('Missing required parameters.');
        }

        $comicId = (int)$data['comic_id'];
        $quantity = (int)$data['quantity'];

        // Validate quantity
        if ($quantity < 0) {
            throw new Exception('Invalid quantity.');
        }

        if ($quantity === 0) {
            return $this->handleRemoveFromCart($data);
        }

        // Check if comic exists and has enough stock
        $comic = $this->comicModel->getComicById($comicId);
        if (!$comic) {
            throw new Exception('Comic not found.');
        }

        if ($comic['stock'] < $quantity) {
            throw new Exception('Not enough stock available.');
        }

        // Update cart
        if ($this->cartModel->updateCartItem($_SESSION['user_id'], $comicId, $quantity)) {
            // Get updated cart info
            $cartTotal = $this->cartModel->getCartTotal($_SESSION['user_id']);
            $itemTotal = $quantity * $comic['price'];

            $this->sendJsonResponse([
                'success' => true,
                'message' => 'Cart updated successfully.',
                'total' => $cartTotal,
                'itemTotal' => $itemTotal
            ]);
        } else {
            throw new Exception('Failed to update cart.');
        }
    }

    private function handleRemoveFromCart($data) {
        // Validate input
        if (!isset($data['comic_id'])) {
            throw new Exception('Missing comic ID.');
        }

        $comicId = (int)$data['comic_id'];

        // Remove from cart
        if ($this->cartModel->removeFromCart($_SESSION['user_id'], $comicId)) {
            // Get updated cart info
            $cartCount = $this->cartModel->getCartItemCount($_SESSION['user_id']);
            $cartTotal = $this->cartModel->getCartTotal($_SESSION['user_id']);

            $this->sendJsonResponse([
                'success' => true,
                'message' => 'Item removed from cart successfully.',
                'cartCount' => $cartCount,
                'total' => $cartTotal
            ]);
        } else {
            throw new Exception('Failed to remove item from cart.');
        }
    }

    private function handleClearCart() {
        // Clear cart
        if ($this->cartModel->clearCart($_SESSION['user_id'])) {
            $this->sendJsonResponse([
                'success' => true,
                'message' => 'Cart cleared successfully.',
                'cartCount' => 0,
                'total' => 0
            ]);
        } else {
            throw new Exception('Failed to clear cart.');
        }
    }

    private function sendJsonResponse($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}

// Entry point
try {
    $controller = new CartController($pdo);
    $controller->handleRequest();
} catch (Exception $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
} 