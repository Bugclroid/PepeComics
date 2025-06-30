<?php
require_once '../db.php';
require_once '../helpers.php';
require_once '../models/Comic.php';

class ComicsController {
    private $pdo;
    private $comicModel;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->comicModel = new Comic($pdo);
    }

    public function handleRequest() {
        $action = $_REQUEST['action'] ?? '';

        switch ($action) {
            case 'list':
                $this->listComics();
                break;
            case 'search':
                $this->searchComics();
                break;
            case 'filter':
                $this->filterComics();
                break;
            case 'details':
                $this->getComicDetails();
                break;
            case 'categories':
                $this->getCategories();
                break;
            default:
                $this->sendJsonResponse(false, 'Invalid action.');
                break;
        }
    }

    private function listComics() {
        try {
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 12;
            $sortBy = $_GET['sort_by'] ?? 'title';
            $sortOrder = $_GET['sort_order'] ?? 'asc';

            // Validate sort parameters
            $allowedSortFields = ['title', 'price', 'author', 'publisher'];
            $allowedSortOrders = ['asc', 'desc'];

            if (!in_array($sortBy, $allowedSortFields)) {
                $sortBy = 'title';
            }

            if (!in_array(strtolower($sortOrder), $allowedSortOrders)) {
                $sortOrder = 'asc';
            }

            // Get comics
            $comics = $this->comicModel->getAllComics($page, $limit, $sortBy, $sortOrder);
            $total = $this->comicModel->getTotalComics();

            // Get categories for each comic
            foreach ($comics as &$comic) {
                $comic['categories'] = $this->comicModel->getComicCategories($comic['comic_id']);
            }

            $this->sendJsonResponse(true, 'Comics retrieved successfully.', [
                'comics' => $comics,
                'total' => $total,
                'page' => $page,
                'limit' => $limit,
                'total_pages' => ceil($total / $limit)
            ]);
        } catch (Exception $e) {
            error_log($e->getMessage());
            $this->sendJsonResponse(false, 'An error occurred while retrieving comics.');
        }
    }

    private function searchComics() {
        try {
            $query = $_GET['query'] ?? '';
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 12;

            if (empty($query)) {
                $this->sendJsonResponse(false, 'Search query is required.');
                return;
            }

            // Search comics
            $comics = $this->comicModel->searchComics($query, $page, $limit);
            $total = $this->comicModel->getTotalSearchResults($query);

            // Get categories for each comic
            foreach ($comics as &$comic) {
                $comic['categories'] = $this->comicModel->getComicCategories($comic['comic_id']);
            }

            $this->sendJsonResponse(true, 'Search results retrieved successfully.', [
                'comics' => $comics,
                'total' => $total,
                'page' => $page,
                'limit' => $limit,
                'total_pages' => ceil($total / $limit)
            ]);
        } catch (Exception $e) {
            error_log($e->getMessage());
            $this->sendJsonResponse(false, 'An error occurred while searching comics.');
        }
    }

    private function filterComics() {
        try {
            $categories = isset($_GET['categories']) ? explode(',', $_GET['categories']) : [];
            $minPrice = isset($_GET['min_price']) ? (float)$_GET['min_price'] : null;
            $maxPrice = isset($_GET['max_price']) ? (float)$_GET['max_price'] : null;
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 12;

            // Filter comics
            $comics = $this->comicModel->filterComics($categories, $minPrice, $maxPrice, $page, $limit);
            $total = $this->comicModel->getTotalFilteredResults($categories, $minPrice, $maxPrice);

            // Get categories for each comic
            foreach ($comics as &$comic) {
                $comic['categories'] = $this->comicModel->getComicCategories($comic['comic_id']);
            }

            $this->sendJsonResponse(true, 'Filtered results retrieved successfully.', [
                'comics' => $comics,
                'total' => $total,
                'page' => $page,
                'limit' => $limit,
                'total_pages' => ceil($total / $limit)
            ]);
        } catch (Exception $e) {
            error_log($e->getMessage());
            $this->sendJsonResponse(false, 'An error occurred while filtering comics.');
        }
    }

    private function getComicDetails() {
        try {
            $comicId = isset($_GET['comic_id']) ? (int)$_GET['comic_id'] : 0;

            if (!$comicId) {
                $this->sendJsonResponse(false, 'Comic ID is required.');
                return;
            }

            // Get comic details
            $comic = $this->comicModel->getComicById($comicId);

            if (!$comic) {
                $this->sendJsonResponse(false, 'Comic not found.');
                return;
            }

            // Get categories
            $comic['categories'] = $this->comicModel->getComicCategories($comicId);

            // Get reviews
            $comic['reviews'] = $this->comicModel->getComicReviews($comicId);

            // Calculate average rating
            $totalRating = 0;
            $reviewCount = count($comic['reviews']);
            
            foreach ($comic['reviews'] as $review) {
                $totalRating += $review['rating'];
            }

            $comic['average_rating'] = $reviewCount > 0 ? round($totalRating / $reviewCount, 1) : 0;
            $comic['review_count'] = $reviewCount;

            $this->sendJsonResponse(true, 'Comic details retrieved successfully.', [
                'comic' => $comic
            ]);
        } catch (Exception $e) {
            error_log($e->getMessage());
            $this->sendJsonResponse(false, 'An error occurred while retrieving comic details.');
        }
    }

    private function getCategories() {
        try {
            $categories = $this->comicModel->getAllCategories();

            $this->sendJsonResponse(true, 'Categories retrieved successfully.', [
                'categories' => $categories
            ]);
        } catch (Exception $e) {
            error_log($e->getMessage());
            $this->sendJsonResponse(false, 'An error occurred while retrieving categories.');
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
$controller = new ComicsController($pdo);
$controller->handleRequest(); 