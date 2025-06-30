<?php
require_once '../../../php/db.php';
require_once '../../../php/helpers.php';
require_once '../../../php/models/User.php';
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
$categoryModel = new Category($pdo);

// Handle category actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'add':
            $name = trim($_POST['name'] ?? '');
            if ($name) {
                if ($categoryModel->createCategory($name)) {
                    $_SESSION['flash_message'] = 'Category has been added successfully.';
                    $_SESSION['flash_type'] = 'success';
                } else {
                    $_SESSION['flash_message'] = 'Error adding category. Please try again.';
                    $_SESSION['flash_type'] = 'error';
                }
            }
            break;
            
        case 'edit':
            $categoryId = intval($_POST['category_id'] ?? 0);
            $name = trim($_POST['name'] ?? '');
            if ($categoryId && $name) {
                if ($categoryModel->updateCategory($categoryId, $name)) {
                    $_SESSION['flash_message'] = 'Category has been updated successfully.';
                    $_SESSION['flash_type'] = 'success';
                } else {
                    $_SESSION['flash_message'] = 'Error updating category. Please try again.';
                    $_SESSION['flash_type'] = 'error';
                }
            }
            break;
            
        case 'delete':
            $categoryId = intval($_POST['category_id'] ?? 0);
            if ($categoryId) {
                if ($categoryModel->deleteCategory($categoryId)) {
                    $_SESSION['flash_message'] = 'Category has been deleted successfully.';
                    $_SESSION['flash_type'] = 'success';
                } else {
                    $_SESSION['flash_message'] = 'Error deleting category. Please try again.';
                    $_SESSION['flash_type'] = 'error';
                }
            }
            break;
    }
    
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit();
}

// Get search query and sort
$search = $_GET['search'] ?? '';
$sort = $_GET['sort'] ?? 'name_asc';

// Get categories with filters
$categories = $categoryModel->getCategoriesWithFilters($search, $sort);

require_once 'header.php';
?>

<div class="admin-main">
    <div class="container">
        <div class="admin-content">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>Manage Categories</h1>
                <button type="button" class="admin-btn admin-btn-primary" 
                        onclick="showAddCategoryModal()">
                    <i class="fas fa-plus"></i> Add Category
                </button>
            </div>

            <!-- Filters -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-6">
                            <label for="search" class="form-label">Search</label>
                            <input type="text" class="form-control" id="search" name="search" 
                                   value="<?= htmlspecialchars($search) ?>" 
                                   placeholder="Search by category name">
                        </div>
                        
                        <div class="col-md-4">
                            <label for="sort" class="form-label">Sort By</label>
                            <select class="form-control" id="sort" name="sort">
                                <option value="name_asc" <?= $sort === 'name_asc' ? 'selected' : '' ?>>
                                    Name (A-Z)
                                </option>
                                <option value="name_desc" <?= $sort === 'name_desc' ? 'selected' : '' ?>>
                                    Name (Z-A)
                                </option>
                                <option value="comics_asc" <?= $sort === 'comics_asc' ? 'selected' : '' ?>>
                                    Comics Count (Low to High)
                                </option>
                                <option value="comics_desc" <?= $sort === 'comics_desc' ? 'selected' : '' ?>>
                                    Comics Count (High to Low)
                                </option>
                            </select>
                        </div>
                        
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="admin-btn admin-btn-secondary w-100">
                                <i class="fas fa-filter"></i> Apply Filters
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Categories Table -->
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Category Name</th>
                            <th>Comics Count</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $category): ?>
                            <tr>
                                <td><?= htmlspecialchars($category['name']) ?></td>
                                <td>
                                    <?php
                                    $comicsCount = $categoryModel->getCategoryComicsCount($category['category_id']);
                                    if ($comicsCount > 0):
                                    ?>
                                        <a href="comics.php?category=<?= $category['category_id'] ?>" 
                                           class="comic-count">
                                            <?= $comicsCount ?> comic<?= $comicsCount !== 1 ? 's' : '' ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">No comics</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button type="button" 
                                                class="admin-btn admin-btn-secondary btn-sm"
                                                onclick="showEditCategoryModal(<?= $category['category_id'] ?>, '<?= htmlspecialchars($category['name']) ?>')">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" 
                                                class="admin-btn admin-btn-danger btn-sm"
                                                onclick="deleteCategory(<?= $category['category_id'] ?>, '<?= htmlspecialchars($category['name']) ?>')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Category Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    <div class="mb-3">
                        <label for="categoryName" class="form-label">Category Name</label>
                        <input type="text" class="form-control" id="categoryName" name="name" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="admin-btn admin-btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="admin-btn admin-btn-primary">Add Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Category Modal -->
<div class="modal fade" id="editCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="category_id" id="editCategoryId">
                    <div class="mb-3">
                        <label for="editCategoryName" class="form-label">Category Name</label>
                        <input type="text" class="form-control" id="editCategoryName" name="name" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="admin-btn admin-btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="admin-btn admin-btn-primary">Update Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Category Form (Hidden) -->
<form id="deleteCategoryForm" method="POST" style="display: none;">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="category_id" id="deleteCategoryId">
</form>

<script>
function showAddCategoryModal() {
    const modal = new bootstrap.Modal(document.getElementById('addCategoryModal'));
    modal.show();
}

function showEditCategoryModal(categoryId, categoryName) {
    document.getElementById('editCategoryId').value = categoryId;
    document.getElementById('editCategoryName').value = categoryName;
    const modal = new bootstrap.Modal(document.getElementById('editCategoryModal'));
    modal.show();
}

function deleteCategory(categoryId, categoryName) {
    if (confirm(`Are you sure you want to delete the category "${categoryName}"? This action cannot be undone.`)) {
        document.getElementById('deleteCategoryId').value = categoryId;
        document.getElementById('deleteCategoryForm').submit();
    }
}
</script>

<?php require_once 'footer.php'; ?> 