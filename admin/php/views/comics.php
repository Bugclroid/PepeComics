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

// Get search query
$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';
$sort = $_GET['sort'] ?? 'title_asc';

// Get comics with filters
$comics = $comicModel->getComicsWithFilters($search, $category, $sort);
$categories = $categoryModel->getAllCategories();

// Handle bulk actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $selectedIds = isset($_POST['ids']) ? explode(',', $_POST['ids']) : [];

    if (!empty($selectedIds)) {
        switch ($action) {
            case 'delete':
                foreach ($selectedIds as $id) {
                    $comicModel->deleteComic($id);
                }
                $_SESSION['flash_message'] = 'Selected comics have been deleted.';
                $_SESSION['flash_type'] = 'success';
                break;

            case 'update_stock':
                $stock = $_POST['stock'] ?? 0;
                foreach ($selectedIds as $id) {
                    $comicModel->updateComicStock($id, $stock);
                }
                $_SESSION['flash_message'] = 'Stock has been updated for selected comics.';
                $_SESSION['flash_type'] = 'success';
                break;
        }
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
    }
}

require_once 'header.php';
?>

<div class="admin-main">
    <div class="container">
        <div class="admin-content">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>Manage Comics</h1>
                <a href="add-comic.php" class="admin-btn admin-btn-primary">
                    <i class="fas fa-plus"></i> Add New Comic
                </a>
            </div>

            <!-- Filters -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-4">
                            <label for="search" class="form-label">Search</label>
                            <input type="text" class="form-control" id="search" name="search" 
                                   value="<?= htmlspecialchars($search) ?>" 
                                   placeholder="Search by title or author">
                        </div>
                        
                        <div class="col-md-3">
                            <label for="category" class="form-label">Category</label>
                            <select class="form-control" id="category" name="category">
                                <option value="">All Categories</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['category_id'] ?>" 
                                            <?= $category == $cat['category_id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cat['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-3">
                            <label for="sort" class="form-label">Sort By</label>
                            <select class="form-control" id="sort" name="sort">
                                <option value="title_asc" <?= $sort === 'title_asc' ? 'selected' : '' ?>>
                                    Title (A-Z)
                                </option>
                                <option value="title_desc" <?= $sort === 'title_desc' ? 'selected' : '' ?>>
                                    Title (Z-A)
                                </option>
                                <option value="price_asc" <?= $sort === 'price_asc' ? 'selected' : '' ?>>
                                    Price (Low to High)
                                </option>
                                <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>
                                    Price (High to Low)
                                </option>
                                <option value="stock_asc" <?= $sort === 'stock_asc' ? 'selected' : '' ?>>
                                    Stock (Low to High)
                                </option>
                                <option value="stock_desc" <?= $sort === 'stock_desc' ? 'selected' : '' ?>>
                                    Stock (High to Low)
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

            <!-- Bulk Actions -->
            <div class="bulk-actions mb-3">
                <select class="form-control bulk-action-select" data-url="<?= $_SERVER['PHP_SELF'] ?>">
                    <option value="">Bulk Actions</option>
                    <option value="delete">Delete Selected</option>
                    <option value="update_stock">Update Stock</option>
                </select>
                <button class="admin-btn admin-btn-secondary bulk-action-apply">Apply</button>
            </div>

            <!-- Comics Table -->
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>
                                <input type="checkbox" class="select-all-checkbox">
                            </th>
                            <th>Image</th>
                            <th data-sort="title">Title</th>
                            <th data-sort="author">Author</th>
                            <th data-sort="price">Price</th>
                            <th data-sort="stock">Stock</th>
                            <th>Categories</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($comics as $comic): ?>
                            <tr>
                                <td>
                                    <input type="checkbox" class="bulk-action-checkbox" 
                                           value="<?= $comic['comic_id'] ?>">
                                </td>
                                <td>
                                    <img src="/pepecomics/images/products/<?= htmlspecialchars($comic['image'] ?? 'default-comic.jpg') ?>" 
                                         alt="<?= htmlspecialchars($comic['title']) ?>" 
                                         class="comic-thumbnail">
                                </td>
                                <td><?= htmlspecialchars($comic['title']) ?></td>
                                <td><?= htmlspecialchars($comic['author']) ?></td>
                                <td>$<?= number_format($comic['price'], 2) ?></td>
                                <td>
                                    <span class="stock-badge <?= $comic['stock'] < 5 ? 'stock-critical' : 
                                          ($comic['stock'] < 10 ? 'stock-low' : 'stock-normal') ?>">
                                        <?= $comic['stock'] ?>
                                    </span>
                                </td>
                                <td>
                                    <?php
                                    $comicCategories = $categoryModel->getCategoriesForComic($comic['comic_id']);
                                    foreach ($comicCategories as $index => $cat) {
                                        echo htmlspecialchars($cat['name']);
                                        if ($index < count($comicCategories) - 1) {
                                            echo ', ';
                                        }
                                    }
                                    ?>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="edit-comic.php?id=<?= $comic['comic_id'] ?>" 
                                           class="admin-btn admin-btn-secondary btn-sm">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button class="admin-btn admin-btn-danger btn-sm" 
                                                data-confirm="Are you sure you want to delete this comic?"
                                                onclick="deleteComic(<?= $comic['comic_id'] ?>)">
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

<script>
function deleteComic(comicId) {
    if (confirm('Are you sure you want to delete this comic?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?= $_SERVER['PHP_SELF'] ?>';

        const actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = 'delete';

        const idsInput = document.createElement('input');
        idsInput.type = 'hidden';
        idsInput.name = 'ids';
        idsInput.value = comicId;

        form.appendChild(actionInput);
        form.appendChild(idsInput);
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<?php require_once 'footer.php'; ?> 