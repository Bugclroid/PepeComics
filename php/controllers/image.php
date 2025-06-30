<?php
require_once '../db.php';
require_once '../models/Comic.php';

// Get comic ID from query string
$comicId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$comicId) {
    header('HTTP/1.0 404 Not Found');
    exit('Image not found');
}

// Initialize Comic model
$comicModel = new Comic($pdo);

// Get image data
$image = $comicModel->getImage($comicId);

if (!$image || !$image['image_data']) {
    // Serve default image if no image data found
    $defaultImage = file_get_contents('../../images/default-comic.jpg');
    header('Content-Type: image/jpeg');
    echo $defaultImage;
    exit;
}

// Set proper content type
header('Content-Type: ' . $image['image_type']);

// Output image data
echo $image['image_data']; 