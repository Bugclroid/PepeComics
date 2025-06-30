<?php
$pageTitle = 'Comic Catalog';
require_once '../db.php';
require_once '../helpers.php';
require_once '../models/Comic.php';

// Initialize Comic model
$comicModel = new Comic($pdo);

// Get filter parameters
$category = sanitizeInput($_GET['category'] ?? '');
$search = sanitizeInput($_GET['search'] ?? '');
$sort = sanitizeInput($_GET['sort'] ?? 'newest');
$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 12;

// Get all categories for filter
$categories = $comicModel->getCategories();

// Build query based on filters
$filters = [];
if ($category) {
    $filters['category'] = $category;
}
if ($search) {
    $filters['search'] = $search;
}

// Get total comics count for pagination
$totalComics = $comicModel->getComicsCount($filters);
$totalPages = ceil($totalComics / $perPage);

// Get comics for current page
$offset = ($page - 1) * $perPage;
$comics = $comicModel->getFilteredComics($filters, $sort, $perPage, $offset);

require_once 'header.php';
?>

<main class="catalog">
    <!-- Search and Filter Section -->
    <section class="catalog-filters">
        <form id="filter-form" class="filters-form" method="GET">
            <div class="search-group">
                <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" 
                       placeholder="Search comics..." class="search-input">
                <button type="submit" class="btn btn--primary search-btn">
                    Search
                    <img src="/pepecomics/images/animations/pepe-search.gif" alt="Search" class="btn__icon">
                </button>
            </div>

            <div class="filter-group">
                <label for="category" class="filter-label">Category:</label>
                <select name="category" id="category" class="filter-select">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= htmlspecialchars($cat['name']) ?>" 
                                <?= $category === $cat['name'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="filter-group">
                <label for="sort" class="filter-label">Sort by:</label>
                <select name="sort" id="sort" class="filter-select">
                    <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>
                        Newest First
                    </option>
                    <option value="price-low" <?= $sort === 'price-low' ? 'selected' : '' ?>>
                        Price: Low to High
                    </option>
                    <option value="price-high" <?= $sort === 'price-high' ? 'selected' : '' ?>>
                        Price: High to Low
                    </option>
                    <option value="title" <?= $sort === 'title' ? 'selected' : '' ?>>
                        Title A-Z
                    </option>
                </select>
            </div>
        </form>

        <?php if ($search || $category): ?>
            <div class="active-filters">
                <?php if ($search): ?>
                    <div class="filter-tag">
                        Search: <?= htmlspecialchars($search) ?>
                        <a href="?<?= http_build_query(array_merge($_GET, ['search' => null])) ?>" 
                           class="filter-remove" aria-label="Remove search filter">×</a>
                    </div>
                <?php endif; ?>

                <?php if ($category): ?>
                    <div class="filter-tag">
                        Category: <?= htmlspecialchars($category) ?>
                        <a href="?<?= http_build_query(array_merge($_GET, ['category' => null])) ?>" 
                           class="filter-remove" aria-label="Remove category filter">×</a>
                    </div>
                <?php endif; ?>

                <a href="/pepecomics/php/views/catalog.php" class="clear-filters">
                    Clear All Filters
                </a>
            </div>
        <?php endif; ?>
    </section>

    <!-- Results Section -->
    <section class="catalog-results">
        <?php if (empty($comics)): ?>
            <div class="no-results">
                <img src="/pepecomics/images/animations/pepe-sad.gif" alt="No Results" class="no-results__image">
                <h2>No Comics Found</h2>
                <p>Try adjusting your filters or search terms.</p>
            </div>
        <?php else: ?>
            <div class="results-header">
                <p class="results-count">
                    Showing <?= ($offset + 1) ?>-<?= min($offset + $perPage, $totalComics) ?> 
                    of <?= $totalComics ?> comics
                </p>
            </div>

            <div class="comics-grid">
                <?php foreach ($comics as $comic): ?>
                    <div class="comic-card" data-category="<?= htmlspecialchars($comic['categories'] ?? '') ?>">
                        <div class="comic-card__image">
                            <img src="/pepecomics/php/controllers/image.php?id=<?= $comic['comic_id'] ?>" 
                                 alt="<?= htmlspecialchars($comic['title']) ?>"
                                 loading="lazy">
                            <div class="comic-card__overlay">
                                <a href="/pepecomics/php/views/product.php?id=<?= $comic['comic_id'] ?>" 
                                   class="btn btn--light">View Details</a>
                            </div>
                        </div>
                        <div class="comic-card__content">
                            <h3 class="comic-card__title">
                                <?= htmlspecialchars($comic['title']) ?>
                            </h3>
                            <p class="comic-card__author">
                                By <?= htmlspecialchars($comic['author']) ?>
                            </p>
                            <?php if (!empty($comic['categories'])): ?>
                                <div class="comic-card__categories">
                                    <?php foreach (explode(',', $comic['categories']) as $cat): ?>
                                        <span class="category-tag">
                                            <?= htmlspecialchars(trim($cat)) ?>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                            <div class="comic-card__footer">
                                <span class="comic-card__price">
                                    $<?= number_format($comic['price'], 2) ?>
                                </span>
                                <?php if ($comic['stock'] > 0): ?>
                                    <button class="btn btn--primary add-to-cart" 
                                            data-comic-id="<?= $comic['comic_id'] ?>">
                                        Add to Cart
                                        <img src="/pepecomics/images/animations/pepe-cart.gif" alt="Add to Cart" class="btn__icon">
                                    </button>
                                    <img src="/pepecomics/images/animations/pepe-punch.gif" 
                                         id="pepe-punch-<?= $comic['comic_id'] ?>" 
                                         alt="Pepe Punch Animation" 
                                         class="pepe-punch" 
                                         style="display:none;">
                                <?php else: ?>
                                    <button class="btn btn--disabled" disabled>
                                        Out of Stock
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>" 
                           class="pagination__link pagination__prev">
                            Previous
                        </a>
                    <?php endif; ?>

                    <div class="pagination__pages">
                        <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>" 
                               class="pagination__link <?= $i === $page ? 'active' : '' ?>">
                                <?= $i ?>
                            </a>
                        <?php endfor; ?>
                    </div>

                    <?php if ($page < $totalPages): ?>
                        <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>" 
                           class="pagination__link pagination__next">
                            Next
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </section>
</main>

<script type="module">
import { debounce } from '/pepecomics/js/main.js';

document.addEventListener('DOMContentLoaded', () => {
    // Auto-submit form when filters change
    const filterForm = document.getElementById('filter-form');
    const filterSelects = filterForm.querySelectorAll('select');
    
    filterSelects.forEach(select => {
        select.addEventListener('change', () => {
            filterForm.submit();
        });
    });

    // Debounced search input
    const searchInput = document.querySelector('input[name="search"]');
    searchInput.addEventListener('input', debounce(() => {
        filterForm.submit();
    }, 500));
});
</script>

<?php require_once 'footer.php'; ?> 