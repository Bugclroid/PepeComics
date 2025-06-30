<?php
$pageTitle = 'Comic Details';
require_once '../db.php';
require_once '../helpers.php';
require_once '../models/Comic.php';

// Get comic ID from URL
$comicId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Initialize Comic model
$comicModel = new Comic($pdo);

// Get comic details
$comic = $comicModel->getComicById($comicId);

// Redirect if comic not found
if (!$comic) {
    setFlashMessage('Comic not found.');
    redirect('/php/views/catalog.php');
}

// Get comic categories
$categories = $comicModel->getComicCategories($comicId);

// Get comic reviews
$reviews = $comicModel->getComicReviews($comicId);

// Calculate average rating
$averageRating = 0;
if (!empty($reviews)) {
    $totalRating = array_sum(array_column($reviews, 'rating'));
    $averageRating = round($totalRating / count($reviews), 1);
}

require_once 'header.php';
?>

<main class="product-page">
    <div class="container">
        <nav class="breadcrumb" aria-label="Breadcrumb">
            <ol>
                <li><a href="/pepecomics/index.php">Home</a></li>
                <li><a href="/pepecomics/php/views/catalog.php">Catalog</a></li>
                <li><?= htmlspecialchars($comic['title']) ?></li>
            </ol>
        </nav>

        <div class="product-details">
            <div class="product-image">
                <img src="/pepecomics/php/controllers/image.php?id=<?= $comic['comic_id'] ?>" 
                     alt="<?= htmlspecialchars($comic['title']) ?>" 
                     class="main-image">
                <div class="image-overlay">
                    <button class="zoom-btn" data-image-id="<?= $comic['comic_id'] ?>">
                        <i class="fas fa-search-plus"></i>
                    </button>
                </div>
            </div>

            <div class="product-info">
                <h1 class="product-title"><?= htmlspecialchars($comic['title']) ?></h1>
                
                <div class="product-meta">
                    <p class="product-author">By <?= htmlspecialchars($comic['author']) ?></p>
                    <div class="product-rating">
                        <div class="rating-stars">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="fas fa-star <?= $i <= $averageRating ? 'filled' : '' ?>"></i>
                            <?php endfor; ?>
                        </div>
                        <span class="rating-count">(<?= count($reviews) ?> reviews)</span>
                    </div>
                </div>

                <?php if (!empty($categories)): ?>
                    <div class="product-categories">
                        <?php foreach ($categories as $category): ?>
                            <a href="/pepecomics/php/views/catalog.php?category=<?= urlencode($category['name']) ?>" 
                               class="category-tag">
                                <?= htmlspecialchars($category['name']) ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <div class="product-price">
                    <span class="current-price">$<?= number_format($comic['price'], 2) ?></span>
                    <span class="stock <?= $comic['stock'] > 0 ? 'in-stock' : 'out-of-stock' ?>">
                        <?= $comic['stock'] > 0 ? 'In Stock' : 'Out of Stock' ?>
                    </span>
                </div>

                <?php if ($comic['stock'] > 0): ?>
                    <form class="add-to-cart-form">
                        <div class="quantity-input">
                            <button type="button" class="quantity-btn minus" aria-label="Decrease quantity">-</button>
                            <input type="number" name="quantity" value="1" min="1" max="<?= $comic['stock'] ?>" required>
                            <button type="button" class="quantity-btn plus" aria-label="Increase quantity">+</button>
                        </div>
                        <button type="submit" class="btn btn--primary add-to-cart" data-comic-id="<?= $comic['comic_id'] ?>">
                            Add to Cart
                            <img src="/pepecomics/images/animations/pepe-cart.gif" alt="Add to Cart" class="btn__icon">
                        </button>
                    </form>
                    <img src="/pepecomics/images/animations/pepe-punch.gif" id="pepe-punch-<?= $comic['comic_id'] ?>" 
                         alt="Pepe Punch Animation" class="pepe-punch" style="display:none;">
                <?php endif; ?>

                <div class="product-description">
                    <h2>Description</h2>
                    <p><?= nl2br(htmlspecialchars($comic['description'] ?? 'No description available.')) ?></p>
                </div>
            </div>
        </div>

        <!-- Reviews section here -->
    </div>
</main>

<script type="module">
import { Cart } from '/pepecomics/js/cart.js';

document.addEventListener('DOMContentLoaded', () => {
    const addToCartForm = document.querySelector('.add-to-cart-form');
    if (addToCartForm) {
        // Quantity buttons functionality
        const quantityInput = addToCartForm.querySelector('input[name="quantity"]');
        const minusBtn = addToCartForm.querySelector('.minus');
        const plusBtn = addToCartForm.querySelector('.plus');

        minusBtn.addEventListener('click', () => {
            const currentValue = parseInt(quantityInput.value);
            if (currentValue > 1) {
                quantityInput.value = currentValue - 1;
            }
        });

        plusBtn.addEventListener('click', () => {
            const currentValue = parseInt(quantityInput.value);
            const maxValue = parseInt(quantityInput.max);
            if (currentValue < maxValue) {
                quantityInput.value = currentValue + 1;
            }
        });
    }
});
</script>

<?php require_once 'footer.php'; ?> 