<?php
require_once __DIR__ . '/../../../php/db.php';
require_once __DIR__ . '/../../../php/helpers.php';
require_once __DIR__ . '/../../../php/models/User.php';
require_once __DIR__ . '/../../../php/models/Comic.php';
require_once __DIR__ . '/../../../php/models/Category.php';

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

// Get comic ID from URL
$comicId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$comicId) {
    $_SESSION['flash_message'] = 'Invalid comic ID.';
    $_SESSION['flash_type'] = 'error';
    header('Location: comics.php');
    exit();
}

// Get comic data
$comic = $comicModel->getComicById($comicId);
if (!$comic) {
    $_SESSION['flash_message'] = 'Comic not found.';
    $_SESSION['flash_type'] = 'error';
    header('Location: comics.php');
    exit();
}

// Get all categories and comic's categories
$categories = $categoryModel->getAllCategories();
$comicCategories = $categoryModel->getCategoriesForComic($comicId);
$selectedCategoryIds = array_map(function($cat) {
    return $cat['category_id'];
}, $comicCategories);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $title = sanitizeInput($_POST['title']);
        $author = sanitizeInput($_POST['author']);
        $publisher = sanitizeInput($_POST['publisher']);
        $price = floatval($_POST['price']);
        $stock = intval($_POST['stock']);
        $description = sanitizeInput($_POST['description']);
        $selectedCategories = $_POST['categories'] ?? [];

        // Handle image upload
        $imageData = [
            'image_name' => $comic['image_name'] ?? null, // Keep existing image name by default
            'image_type' => $comic['image_type'] ?? null,
            'image_data' => $comic['image_data'] ?? null
        ];

        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/pepecomics/images/products/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $fileName = uniqid() . '_' . basename($_FILES['image']['name']);
            $uploadFile = $uploadDir . $fileName;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadFile)) {
                // Delete old image if it exists
                if ($imageData['image_name'] && file_exists($uploadDir . $imageData['image_name'])) {
                    unlink($uploadDir . $imageData['image_name']);
                }
                $imageData['image_name'] = $fileName;
                $imageData['image_type'] = $_FILES['image']['type'];
                $imageData['image_data'] = file_get_contents($uploadFile);
            } else {
                throw new Exception('Failed to upload image: ' . error_get_last()['message']);
            }
        }

        // Start transaction
        $pdo->beginTransaction();

        try {
            // Update comic
            $success = $comicModel->updateComic($comicId, [
                'title' => $title,
                'author' => $author,
                'publisher' => $publisher,
                'price' => $price,
                'stock' => $stock,
                'description' => $description,
                'image_name' => $imageData['image_name'],
                'image_type' => $imageData['image_type'],
                'image_data' => $imageData['image_data']
            ]);

            // Update categories
            $success = $categoryModel->assignCategoriesToComic($comicId, $selectedCategories);

            // If everything is successful, commit the transaction
            $pdo->commit();

            $_SESSION['flash_message'] = 'Comic has been updated successfully.';
            $_SESSION['flash_type'] = 'success';
            header('Location: comics.php');
            exit();

        } catch (Exception $e) {
            // Rollback transaction on error
            $pdo->rollBack();
            throw $e;
        }

    } catch (Exception $e) {
        // Log the error
        error_log("Error updating comic: " . $e->getMessage());
        
        $_SESSION['flash_message'] = 'Error updating comic: ' . $e->getMessage();
        $_SESSION['flash_type'] = 'error';
    }
}

require_once 'header.php';
?>

<div class="admin-main">
    <div class="container">
        <div class="admin-content">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>Edit Comic</h1>
                <a href="comics.php" class="admin-btn admin-btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Comics
                </a>
            </div>

            <form method="POST" enctype="multipart/form-data" class="admin-form" data-validate>
                <!-- Title -->
                <div class="form-group">
                    <label for="title">Title <span class="required">*</span></label>
                    <input type="text" class="form-control" id="title" name="title" 
                           value="<?= htmlspecialchars($comic['title']) ?>" required
                           data-error="Please enter the comic title">
                </div>

                <!-- Author -->
                <div class="form-group">
                    <label for="author">Author <span class="required">*</span></label>
                    <input type="text" class="form-control" id="author" name="author" 
                           value="<?= htmlspecialchars($comic['author']) ?>" required
                           data-error="Please enter the author name">
                </div>

                <!-- Publisher -->
                <div class="form-group">
                    <label for="publisher">Publisher</label>
                    <input type="text" class="form-control" id="publisher" name="publisher" 
                           value="<?= htmlspecialchars($comic['publisher']) ?>">
                </div>

                <!-- Price -->
                <div class="form-group">
                    <label for="price">Price <span class="required">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input type="number" class="form-control" id="price" name="price" 
                               step="0.01" min="0" value="<?= $comic['price'] ?>" required
                               data-error="Please enter a valid price">
                    </div>
                </div>

                <!-- Stock -->
                <div class="form-group">
                    <label for="stock">Stock <span class="required">*</span></label>
                    <input type="number" class="form-control" id="stock" name="stock" 
                           min="0" value="<?= $comic['stock'] ?>" required
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
                                       value="<?= $category['category_id'] ?>"
                                       <?= in_array($category['category_id'], $selectedCategoryIds) ? 'checked' : '' ?>>
                                <label class="form-check-label" 
                                       for="category_<?= $category['category_id'] ?>">
                                    <?= htmlspecialchars($category['name']) ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Current Image -->
                <div class="form-group">
                    <label>Current Cover Image</label>
                    <?php if (!empty($comic['image_name'])): ?>
                        <div class="current-image">
                            <img src="/pepecomics/images/products/<?= htmlspecialchars($comic['image_name']) ?>" 
                                 alt="Current Cover" style="max-width: 200px;">
                        </div>
                    <?php else: ?>
                        <p class="text-muted">No image available</p>
                    <?php endif; ?>
                </div>

                <!-- New Image -->
                <div class="form-group">
                    <label for="image">New Cover Image</label>
                    <input type="file" class="form-control" id="image" name="image" 
                           accept="image/*"
                           data-preview="#image-preview">
                    <small class="form-text text-muted">Leave empty to keep the current image</small>
                    <div id="image-preview-container" style="display: none; margin-top: 10px;">
                        <img id="image-preview" src="#" alt="New Cover Preview" style="max-width: 200px;">
                    </div>
                </div>

                <!-- Add preview script -->
                <script>
                document.getElementById('image').addEventListener('change', function(e) {
                    const preview = document.getElementById('image-preview');
                    const container = document.getElementById('image-preview-container');
                    
                    if (e.target.files && e.target.files[0]) {
                        const reader = new FileReader();
                        
                        reader.onload = function(e) {
                            preview.src = e.target.result;
                            container.style.display = 'block';
                        }
                        
                        reader.readAsDataURL(e.target.files[0]);
                    } else {
                        preview.src = '#';
                        container.style.display = 'none';
                    }
                });
                </script>

                <!-- Description -->
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="4"><?= htmlspecialchars($comic['description'] ?? '') ?></textarea>
                </div>

                <!-- Submit Button -->
                <div class="form-group">
                    <button type="submit" class="admin-btn admin-btn-primary">
                        <i class="fas fa-save"></i> Update Comic
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?> 