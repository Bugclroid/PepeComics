<?php
require_once '../../../php/db.php';
require_once '../../../php/helpers.php';
require_once '../../../php/models/User.php';
require_once '../../../php/models/Comic.php';
require_once '../../../php/models/Category.php';

// Start session and check if user is logged in and is admin
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /php/views/login.php');
    exit();
}

// Get user data
$userModel = new User($pdo);
$user = $userModel->getUserById($_SESSION['user_id']);

if (!$user['is_admin']) {
    header('Location: /index.php');
    exit();
}

// Initialize models
$comicModel = new Comic($pdo);
$categoryModel = new Category($pdo);

// Get all categories
$categories = $categoryModel->getAllCategories();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitizeInput($_POST['title']);
    $author = sanitizeInput($_POST['author']);
    $publisher = sanitizeInput($_POST['publisher']);
    $price = floatval($_POST['price']);
    $stock = intval($_POST['stock']);
    $description = sanitizeInput($_POST['description']);
    $selectedCategories = $_POST['categories'] ?? [];

    // Handle image upload
    $image = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/images/products/';
        $fileName = uniqid() . '_' . basename($_FILES['image']['name']);
        $uploadFile = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadFile)) {
            $image = $fileName;
        }
    }

    // Add comic
    $comicId = $comicModel->addComic([
        'title' => $title,
        'author' => $author,
        'publisher' => $publisher,
        'price' => $price,
        'stock' => $stock,
        'description' => $description,
        'image' => $image
    ]);

    if ($comicId) {
        // Add categories
        foreach ($selectedCategories as $categoryId) {
            $categoryModel->addComicCategory($comicId, $categoryId);
        }

        $_SESSION['flash_message'] = 'Comic has been added successfully.';
        $_SESSION['flash_type'] = 'success';
        header('Location: comics.php');
        exit();
    } else {
        $_SESSION['flash_message'] = 'Error adding comic. Please try again.';
        $_SESSION['flash_type'] = 'error';
    }
}

require_once 'header.php';
?>

<div class="admin-main">
    <div class="container">
        <div class="admin-content">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>Add New Comic</h1>
                <a href="comics.php" class="admin-btn admin-btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Comics
                </a>
            </div>

            <form method="POST" enctype="multipart/form-data" class="admin-form" data-validate>
                <!-- Title -->
                <div class="form-group">
                    <label for="title">Title <span class="required">*</span></label>
                    <input type="text" class="form-control" id="title" name="title" required
                           data-error="Please enter the comic title">
                </div>

                <!-- Author -->
                <div class="form-group">
                    <label for="author">Author <span class="required">*</span></label>
                    <input type="text" class="form-control" id="author" name="author" required
                           data-error="Please enter the author name">
                </div>

                <!-- Publisher -->
                <div class="form-group">
                    <label for="publisher">Publisher</label>
                    <input type="text" class="form-control" id="publisher" name="publisher">
                </div>

                <!-- Price -->
                <div class="form-group">
                    <label for="price">Price <span class="required">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input type="number" class="form-control" id="price" name="price" 
                               step="0.01" min="0" required
                               data-error="Please enter a valid price">
                    </div>
                </div>

                <!-- Stock -->
                <div class="form-group">
                    <label for="stock">Stock <span class="required">*</span></label>
                    <input type="number" class="form-control" id="stock" name="stock" 
                           min="0" required
                           data-error="Please enter the stock quantity">
                </div>

                <!-- Categories -->
                <div class="form-group">
                    <label>Categories</label>
                    <div class="categories-grid">
                        <?php foreach ($categories as $category): ?>
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" 
                                       id="category_<?= $category['category_id'] ?>"
                                       name="categories[]" 
                                       value="<?= $category['category_id'] ?>">
                                <label class="form-check-label" 
                                       for="category_<?= $category['category_id'] ?>">
                                    <?= htmlspecialchars($category['name']) ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Image -->
                <div class="form-group">
                    <label for="image">Cover Image <span class="required">*</span></label>
                    <input type="file" class="form-control" id="image" name="image" 
                           accept="image/*" required
                           data-preview="#image-preview"
                           data-error="Please select a cover image">
                    <img id="image-preview" src="#" alt="Cover Preview" 
                         style="display: none; max-width: 200px; margin-top: 10px;">
                </div>

                <!-- Description -->
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea class="form-control" id="description" name="description" 
                              rows="5"></textarea>
                </div>

                <!-- Submit Button -->
                <div class="form-group">
                    <button type="submit" class="admin-btn admin-btn-primary">
                        <i class="fas fa-plus"></i> Add Comic
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?> 