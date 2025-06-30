<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../models/Comic.php';
require_once __DIR__ . '/../helpers.php';

// Initialize Comic model
$comicModel = new Comic($pdo);

// Get all categories
$categories = $comicModel->getCategories();

// Get featured and latest comics
$featuredComics = $comicModel->getFeaturedComics();
$latestComics = $comicModel->getLatestComics();
?>

<main class="home">
    <!-- Hero Section -->
    <section class="hero">
        <div class="hero__content">
            <h1 class="hero__title">Welcome to PepeComics</h1>
            <p class="hero__subtitle">Your one-stop shop for the best comic books!</p>
            <div class="hero__cta">
                <a href="/pepecomics/php/views/catalog.php" class="btn btn--primary">
                    Browse Comics
                    <img src="/pepecomics/images/animations/pepe-browse.gif" alt="Browse" class="btn__icon">
                </a>
                <?php if (!isLoggedIn()): ?>
                    <a href="/pepecomics/php/views/login.php" class="btn btn--secondary">
                        Join Now
                        <img src="/pepecomics/images/animations/pepe-join.gif" alt="Join" class="btn__icon">
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <div class="hero__animation">
            <img src="/pepecomics/images/animations/pepe-welcome.gif" alt="Welcome" class="hero__pepe">
        </div>
    </section>

    <!-- Featured Comics -->
    <section class="featured">
        <div class="section-header">
            <h2 class="section-title">Featured Comics</h2>
            <a href="/pepecomics/php/views/catalog.php?sort=featured" class="section-link">View All</a>
        </div>
        <div class="comics-grid">
            <?php foreach ($featuredComics as $comic): ?>
                <div class="comic-card">
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
    </section>

    <!-- Categories -->
    <section class="categories">
        <div class="section-header">
            <h2 class="section-title">Browse by Category</h2>
        </div>
        <div class="categories-grid">
            <?php foreach ($categories as $category): ?>
                <a href="/pepecomics/php/views/catalog.php?category=<?= urlencode($category['name']) ?>" 
                   class="category-card">
                    <div class="category-card__icon">
                        <img src="/pepecomics/images/categories/<?= strtolower($category['name']) ?>.png" 
                             alt="<?= htmlspecialchars($category['name']) ?>"
                             loading="lazy">
                    </div>
                    <h3 class="category-card__title">
                        <?= htmlspecialchars($category['name']) ?>
                    </h3>
                </a>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Latest Arrivals -->
    <section class="latest">
        <div class="section-header">
            <h2 class="section-title">Latest Arrivals</h2>
            <a href="/pepecomics/php/views/catalog.php?sort=newest" class="section-link">View All</a>
        </div>
        <div class="comics-grid">
            <?php foreach ($latestComics as $comic): ?>
                <div class="comic-card">
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
    </section>

    <!-- Newsletter Section -->
    <section class="newsletter">
        <div class="newsletter__content">
            <h2 class="newsletter__title">Stay Updated!</h2>
            <p class="newsletter__text">
                Subscribe to our newsletter for the latest comics and exclusive offers.
            </p>
            <form id="newsletter-form" class="newsletter__form" method="POST" action="/pepecomics/php/controllers/newsletter.php">
                <div class="newsletter__input-group">
                    <input type="email" name="email" placeholder="Enter your email" required>
                    <button type="submit" class="btn btn--primary">
                        Subscribe
                        <img src="/pepecomics/images/animations/pepe-mail.gif" alt="Subscribe" class="btn__icon">
                    </button>
                </div>
            </form>
        </div>
        <div class="newsletter__animation">
            <img src="/pepecomics/images/animations/pepe-newsletter.gif" alt="Newsletter" class="newsletter__pepe">
        </div>
    </section>
</main>

<script type="module">
import { showNotification } from '/pepecomics/js/main.js';

document.addEventListener('DOMContentLoaded', () => {
    // Handle newsletter form submission
    const newsletterForm = document.getElementById('newsletter-form');
    newsletterForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        try {
            const formData = new FormData(newsletterForm);
            const response = await fetch(newsletterForm.action, {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                showNotification('Successfully subscribed to newsletter!', 'success');
                newsletterForm.reset();
            } else {
                throw new Error(result.message || 'Failed to subscribe');
            }
        } catch (error) {
            showNotification(error.message, 'error');
        }
    });
});
</script> 