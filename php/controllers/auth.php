<?php
require_once '../db.php';
require_once '../helpers.php';
require_once '../models/User.php';

class AuthController {
    private $pdo;
    private $userModel;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->userModel = new User($pdo);
    }

    public function handleRequest() {
        startSession();

        $action = $_GET['action'] ?? '';
        
        switch ($action) {
            case 'login':
                $this->handleLogin();
                break;
            case 'register':
                $this->handleRegister();
                break;
            case 'logout':
                $this->handleLogout();
                break;
            default:
                $this->redirect('/pepecomics/php/views/login.php');
        }
    }

    private function handleLogin() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->sendJsonResponse(false, 'Invalid request method');
            return;
        }

        $email = sanitizeInput($_POST['email']);
        $password = $_POST['password'];
        $remember = isset($_POST['remember']);

        try {
            $user = $this->userModel->getUserByEmail($email);

            if ($user && password_verify($password, $user['password_hash'])) {
                // Regenerate session ID for security
                session_regenerate_id(true);
                
                // Set session variables
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['is_admin'] = $user['is_admin'];

                // Set remember me cookie if requested
                if ($remember) {
                    $token = generateToken();
                    $expiry = time() + (30 * 24 * 60 * 60); // 30 days
                    
                    // Store token in database
                    $this->userModel->storeRememberToken($user['user_id'], $token, $expiry);
                    
                    // Set cookie
                    setcookie('remember_token', $token, $expiry, '/', '', true, true);
                }

                $this->sendJsonResponse(true, 'Login successful', [
                    'redirect' => '/pepecomics/index.php',
                    'user' => [
                        'name' => $user['name'],
                        'is_admin' => $user['is_admin']
                    ]
                ]);
            } else {
                $this->sendJsonResponse(false, 'Invalid email or password');
            }
        } catch (Exception $e) {
            error_log($e->getMessage());
            $this->sendJsonResponse(false, 'An error occurred during login. Please try again.');
        }
    }

    private function sendJsonResponse($success, $message, $data = []) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => $success,
            'message' => $message,
            'data' => $data
        ]);
        exit();
    }

    private function handleRegister() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->sendJsonResponse(false, 'Invalid request method');
            return;
        }

        $name = sanitizeInput($_POST['name']);
        $email = sanitizeInput($_POST['email']);
        $password = $_POST['password'];
        $confirmPassword = $_POST['confirm_password'];

        try {
            // Validate input
            if (empty($name) || empty($email) || empty($password) || empty($confirmPassword)) {
                throw new Exception('All fields are required.');
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Invalid email address.');
            }

            if ($password !== $confirmPassword) {
                throw new Exception('Passwords do not match.');
            }

            if (strlen($password) < 8 || !preg_match('/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$/', $password)) {
                throw new Exception('Password must be at least 8 characters long and include both letters and numbers.');
            }

            // Check if email already exists
            if ($this->userModel->getUserByEmail($email)) {
                throw new Exception('Email address is already registered.');
            }

            // Create user
            if ($this->userModel->createUser($name, $email, $password)) {
                // Get the newly created user
                $user = $this->userModel->getUserByEmail($email);
                
                // Start session
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['is_admin'] = $user['is_admin'];

                $this->sendJsonResponse(true, 'Registration successful! Welcome to PepeComics, ' . $name . '!', [
                    'redirect' => '/pepecomics/index.php',
                    'user' => [
                        'name' => $user['name'],
                        'is_admin' => $user['is_admin']
                    ]
                ]);
            } else {
                throw new Exception('Failed to create account. Please try again.');
            }
        } catch (Exception $e) {
            $this->sendJsonResponse(false, $e->getMessage());
        }
    }

    private function handleLogout() {
        // Clear session
        session_unset();
        session_destroy();
        
        // Clear remember me cookie
        if (isset($_COOKIE['remember_token'])) {
            $token = $_COOKIE['remember_token'];
            
            // Remove token from database
            $this->userModel->removeRememberToken($token);
            
            // Delete cookie
            setcookie('remember_token', '', time() - 3600, '/', '', true, true);
        }

        setFlashMessage('You have been logged out successfully.');
        $this->redirect('/pepecomics/php/views/login.php');
    }

    private function redirect($url) {
        header("Location: $url");
        exit();
    }
}

// Initialize controller and handle request
$controller = new AuthController($pdo);
$controller->handleRequest(); 