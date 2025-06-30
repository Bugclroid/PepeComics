        </div><!-- /.container -->
    </main>

    <footer class="footer">
        <div class="container">
            <div class="footer__grid">
                <div class="footer__column">
                    <h3 class="footer__title">About PepeComics</h3>
                    <p class="footer__text">
                        Your one-stop shop for the best comic books featuring everyone's favorite meme character, Pepe!
                        Join our community of comic enthusiasts and collectors.
                    </p>
                    <img src="/pepecomics/images/animations/pepe-wave.gif" alt="Pepe waving goodbye" class="footer__pepe">
                </div>

                <div class="footer__column">
                    <h3 class="footer__title">Quick Links</h3>
                    <ul class="footer__list">
                        <li><a href="/pepecomics/index.php" class="footer__link">Home</a></li>
                        <li><a href="/pepecomics/php/views/catalog.php" class="footer__link">Catalog</a></li>
                        <li><a href="/pepecomics/php/views/cart.php" class="footer__link">Cart</a></li>
                        <?php if (isLoggedIn()): ?>
                            <li><a href="/pepecomics/php/views/profile.php" class="footer__link">Profile</a></li>
                            <li><a href="/pepecomics/php/views/orders.php" class="footer__link">Orders</a></li>
                        <?php else: ?>
                            <li><a href="/pepecomics/php/views/login.php" class="footer__link">Login/Register</a></li>
                        <?php endif; ?>
                    </ul>
                </div>

                <div class="footer__column">
                    <h3 class="footer__title">Categories</h3>
                    <ul class="footer__list">
                        <li><a href="/pepecomics/php/views/catalog.php?category=action" class="footer__link">Action</a></li>
                        <li><a href="/pepecomics/php/views/catalog.php?category=adventure" class="footer__link">Adventure</a></li>
                        <li><a href="/pepecomics/php/views/catalog.php?category=comedy" class="footer__link">Comedy</a></li>
                        <li><a href="/pepecomics/php/views/catalog.php?category=drama" class="footer__link">Drama</a></li>
                    </ul>
                </div>

                <div class="footer__column">
                    <h3 class="footer__title">Connect With Us</h3>
                    <div class="footer__social">
                        <a href="#" class="footer__social-link" target="_blank" rel="noopener noreferrer">
                            <img src="/pepecomics/images/icons/facebook.png" alt="Facebook" class="footer__social-icon">
                        </a>
                        <a href="#" class="footer__social-link" target="_blank" rel="noopener noreferrer">
                            <img src="/pepecomics/images/icons/twitter.png" alt="Twitter" class="footer__social-icon">
                        </a>
                        <a href="#" class="footer__social-link" target="_blank" rel="noopener noreferrer">
                            <img src="/pepecomics/images/icons/instagram.png" alt="Instagram" class="footer__social-icon">
                        </a>
                        <a href="#" class="footer__social-link" target="_blank" rel="noopener noreferrer">
                            <img src="/pepecomics/images/icons/discord.png" alt="Discord" class="footer__social-icon">
                        </a>
                    </div>
                    <div class="footer__newsletter">
                        <h4 class="footer__subtitle">Subscribe to Our Newsletter</h4>
                        <form action="/pepecomics/php/controllers/newsletter.php" method="POST" class="footer__form">
                            <input type="email" name="email" class="footer__input" placeholder="Enter your email" required>
                            <button type="submit" class="footer__button">
                                Subscribe
                                <img src="/pepecomics/images/animations/pepe-mail.gif" alt="Subscribe" class="footer__button-icon">
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="footer__bottom">
                <p class="footer__copyright">
                    &copy; <?= date('Y') ?> PepeComics. All rights reserved.
                    <img src="/pepecomics/images/animations/pepe-love.gif" alt="Pepe Love" class="footer__copyright-icon">
                </p>
                <div class="footer__links">
                    <a href="/pepecomics/php/views/privacy.php" class="footer__bottom-link">Privacy Policy</a>
                    <a href="/pepecomics/php/views/terms.php" class="footer__bottom-link">Terms of Service</a>
                    <a href="/pepecomics/php/views/contact.php" class="footer__bottom-link">Contact Us</a>
                </div>
            </div>
        </div>

        <!-- Back to top button with Pepe animation -->
        <button id="back-to-top" class="back-to-top" aria-label="Back to top">
            <img src="/pepecomics/images/animations/pepe-jump.gif" alt="Back to top" class="back-to-top__icon">
        </button>
    </footer>

    <script>
        // Back to top button functionality
        const backToTopButton = document.getElementById('back-to-top');
        if (backToTopButton) {
            window.addEventListener('scroll', () => {
                if (window.scrollY > 300) {
                    backToTopButton.classList.add('show');
                } else {
                    backToTopButton.classList.remove('show');
                }
            });

            backToTopButton.addEventListener('click', () => {
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            });
        }
    </script>
</body>
</html> 