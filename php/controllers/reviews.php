<?php
require_once '../db.php';
require_once '../helpers.php';
require_once '../models/Comic.php';

class ReviewsController {
    private $pdo;
    private $comicModel;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->comicModel = new Comic($pdo);
    }

    public function handleRequest() {
        // Check if user is logged in
        if (!isLoggedIn()) {
            $this->sendJsonResponse(false, 'Please log in to submit a review.');
            return;
        }

        // Get action from request
        $action = $_POST['action'] ?? '';

        switch ($action) {
            case 'create':
                $this->createReview();
                break;
            default:
                $this->sendJsonResponse(false, 'Invalid action.');
                break;
        }
    }

    private function createReview() {
        try {
            // Validate input
            $comicId = isset($_POST['comic_id']) ? (int)$_POST['comic_id'] : 0;
            $rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;
            $content = $_POST['content'] ?? '';

            if (!$comicId || !$rating || !$content) {
                $this->sendJsonResponse(false, 'All fields are required.');
                return;
            }

            if ($rating < 1 || $rating > 5) {
                $this->sendJsonResponse(false, 'Invalid rating value.');
                return;
            }

            // Check if comic exists
            $comic = $this->comicModel->getComicById($comicId);
            if (!$comic) {
                $this->sendJsonResponse(false, 'Comic not found.');
                return;
            }

            // Check if user has already reviewed this comic
            $existingReview = $this->comicModel->getUserReview($comicId, $_SESSION['user_id']);
            if ($existingReview) {
                $this->sendJsonResponse(false, 'You have already reviewed this comic.');
                return;
            }

            // Create review
            $success = $this->comicModel->createReview([
                'comic_id' => $comicId,
                'user_id' => $_SESSION['user_id'],
                'rating' => $rating,
                'content' => $content
            ]);

            if ($success) {
                $this->sendJsonResponse(true, 'Review submitted successfully.');
            } else {
                $this->sendJsonResponse(false, 'Failed to submit review.');
            }
        } catch (Exception $e) {
            error_log($e->getMessage());
            $this->sendJsonResponse(false, 'An error occurred while submitting your review.');
        }
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
$controller = new ReviewsController($pdo);
$controller->handleRequest(); 