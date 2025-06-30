<?php
$pageTitle = 'Login/Register';
require_once 'header.php';
?>

<div class="auth-container">
    <div class="auth-tabs">
        <button class="auth-tab active" data-tab="login">
            Login
            <img src="/pepecomics/images/animations/pepe-login.gif" alt="Login" class="auth-tab__icon">
        </button>
        <button class="auth-tab" data-tab="register">
            Register
            <img src="/pepecomics/images/animations/pepe-register.gif" alt="Register" class="auth-tab__icon">
        </button>
    </div>

    <div class="auth-content">
        <!-- Login Form -->
        <form id="login-form" action="/pepecomics/php/controllers/auth.php?action=login" method="POST" class="auth-form active" data-validate>
            <div class="form-group">
                <label for="login-email" class="form-label">Email</label>
                <input type="email" id="login-email" name="email" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="login-password" class="form-label">Password</label>
                <div class="password-input">
                    <input type="password" id="login-password" name="password" class="form-control" required>
                    <button type="button" class="toggle-password" aria-label="Toggle password visibility">
                        <img src="/pepecomics/images/animations/pepe-eye.gif" alt="Toggle password" class="toggle-password__icon">
                    </button>
                </div>
            </div>

            <div class="form-group">
                <label class="form-checkbox">
                    <input type="checkbox" name="remember" value="1">
                    <span class="checkbox-label">Remember me</span>
                </label>
            </div>

            <button type="submit" class="btn btn--primary auth-submit">
                Login
                <img src="/pepecomics/images/animations/pepe-login.gif" alt="Login" class="btn__icon">
            </button>

            <div class="auth-links">
                <a href="/pepecomics/php/views/forgot-password.php" class="auth-link">Forgot Password?</a>
            </div>
        </form>

        <!-- Register Form -->
        <form id="register-form" action="/pepecomics/php/controllers/auth.php?action=register" method="POST" class="auth-form" data-validate>
            <div class="form-group">
                <label for="register-name" class="form-label">Full Name</label>
                <input type="text" id="register-name" name="name" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="register-email" class="form-label">Email</label>
                <input type="email" id="register-email" name="email" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="register-password" class="form-label">Password</label>
                <div class="password-input">
                    <input type="password" id="register-password" name="password" class="form-control" 
                           pattern="^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$" 
                           title="Password must be at least 8 characters long and include both letters and numbers"
                           required>
                    <button type="button" class="toggle-password" aria-label="Toggle password visibility">
                        <img src="/pepecomics/images/animations/pepe-eye.gif" alt="Toggle password" class="toggle-password__icon">
                    </button>
                </div>
                <div class="password-strength">
                    <div class="strength-meter"></div>
                    <span class="strength-text"></span>
                </div>
            </div>

            <div class="form-group">
                <label for="register-confirm-password" class="form-label">Confirm Password</label>
                <div class="password-input">
                    <input type="password" id="register-confirm-password" name="confirm_password" class="form-control" required>
                    <button type="button" class="toggle-password" aria-label="Toggle password visibility">
                        <img src="/pepecomics/images/animations/pepe-eye.gif" alt="Toggle password" class="toggle-password__icon">
                    </button>
                </div>
            </div>

            <div class="form-group">
                <label class="form-checkbox">
                    <input type="checkbox" name="terms" required>
                    <span class="checkbox-label">
                        I agree to the <a href="/pepecomics/php/views/terms.php" target="_blank">Terms of Service</a>
                        and <a href="/pepecomics/php/views/privacy.php" target="_blank">Privacy Policy</a>
                    </span>
                </label>
            </div>

            <button type="submit" class="btn btn--primary auth-submit">
                Register
                <img src="/pepecomics/images/animations/pepe-register.gif" alt="Register" class="btn__icon">
            </button>
        </form>
    </div>

    <!-- Pepe animations container -->
    <div class="auth-animations">
        <img src="/pepecomics/images/animations/pepe-welcome.gif" alt="Welcome" class="auth-animation welcome-animation">
        <img src="/pepecomics/images/animations/pepe-smash.gif" alt="Success" class="auth-animation success-animation" style="display: none;">
    </div>
</div>

<script type="module">
import { showNotification } from '/pepecomics/js/main.js';

document.addEventListener('DOMContentLoaded', () => {
    // Tab switching
    const tabs = document.querySelectorAll('.auth-tab');
    const authForms = document.querySelectorAll('.auth-form');
    
    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            const targetForm = tab.dataset.tab;
            
            // Update active states
            tabs.forEach(t => t.classList.remove('active'));
            tab.classList.add('active');

            // Handle form switching with animation
            authForms.forEach(form => {
                if (form.id === `${targetForm}-form`) {
                    // First hide the current active form
                    const currentActive = document.querySelector('.auth-form.active');
                    if (currentActive) {
                        currentActive.style.opacity = '0';
                        currentActive.style.transform = 'translateX(-20px)';
                        setTimeout(() => {
                            currentActive.classList.remove('active');
                            // Then show the target form
                            form.classList.add('active');
                            setTimeout(() => {
                                form.style.opacity = '1';
                                form.style.transform = 'translateX(0)';
                            }, 50);
                        }, 300);
                    } else {
                        form.classList.add('active');
                        form.style.opacity = '1';
                        form.style.transform = 'translateX(0)';
                    }
                }
            });
            
            // Update welcome animation
            const welcomeAnimation = document.querySelector('.welcome-animation');
            welcomeAnimation.src = `/pepecomics/images/animations/pepe-${targetForm}.gif`;
        });
    });

    // Password visibility toggle
    const toggleButtons = document.querySelectorAll('.toggle-password');
    toggleButtons.forEach(button => {
        button.addEventListener('click', () => {
            const input = button.previousElementSibling;
            const type = input.type === 'password' ? 'text' : 'password';
            input.type = type;
            
            // Update icon
            const icon = button.querySelector('img');
            icon.src = `/pepecomics/images/animations/pepe-eye-${type === 'password' ? 'closed' : 'open'}.gif`;
        });
    });

    // Password strength meter
    const passwordInput = document.getElementById('register-password');
    const strengthMeter = document.querySelector('.strength-meter');
    const strengthText = document.querySelector('.strength-text');

    if (passwordInput && strengthMeter && strengthText) {
        passwordInput.addEventListener('input', () => {
            const password = passwordInput.value;
            const strength = calculatePasswordStrength(password);
            
            // Update strength meter
            strengthMeter.style.width = `${strength}%`;
            strengthMeter.className = 'strength-meter ' + getStrengthClass(strength);
            
            // Update strength text
            strengthText.textContent = getStrengthText(strength);
        });
    }

    // Form validation
    document.querySelectorAll('form[data-validate]').forEach(form => {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            if (!validateForm(form)) {
                return;
            }

            try {
                const formData = new FormData(form);
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();
                
                if (result.success) {
                    showNotification(result.message, 'success');
                    // Show success animation
                    const successAnimation = document.querySelector('.success-animation');
                    if (successAnimation) {
                        successAnimation.style.display = 'block';
                        setTimeout(() => {
                            successAnimation.style.display = 'none';
                            window.location.href = '/pepecomics/index.php';
                        }, 1500);
                    } else {
                        window.location.href = '/pepecomics/index.php';
                    }
                } else {
                    throw new Error(result.message || 'An error occurred');
                }
            } catch (error) {
                showNotification(error.message, 'error');
            }
        });
    });

    // Form submission
    const loginForm = document.getElementById('login-form');
    if (loginForm) {
        loginForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            try {
                const formData = new FormData(loginForm);
                const response = await fetch('/pepecomics/php/controllers/auth.php?action=login', {
                    method: 'POST',
                    body: formData
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const result = await response.json();
                
                if (result.success) {
                    // Show success notification
                    showNotification(result.message, 'success');
                    
                    // Show success animation
                    const successAnimation = document.querySelector('.success-animation');
                    if (successAnimation) {
                        successAnimation.style.display = 'block';
                        setTimeout(() => {
                            // Redirect after animation
                            window.location.href = result.data.redirect;
                        }, 1500);
                    } else {
                        // Redirect immediately if no animation
                        window.location.href = result.data.redirect;
                    }
                } else {
                    showNotification(result.message, 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showNotification('An error occurred. Please try again.', 'error');
            }
        });
    }

    // Form validation and submission
    const registerForm = document.getElementById('register-form');
    if (registerForm) {
        registerForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            if (!validateForm(registerForm)) {
                return;
            }

            try {
                const formData = new FormData(registerForm);
                const response = await fetch('/pepecomics/php/controllers/auth.php?action=register', {
                    method: 'POST',
                    body: formData
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const result = await response.json();
                
                if (result.success) {
                    // Show success notification
                    showNotification(result.message, 'success');
                    
                    // Show success animation
                    const successAnimation = document.querySelector('.success-animation');
                    if (successAnimation) {
                        successAnimation.style.display = 'block';
                        setTimeout(() => {
                            // Redirect after animation
                            window.location.href = result.data.redirect;
                        }, 1500);
                    } else {
                        // Redirect immediately if no animation
                        window.location.href = result.data.redirect;
                    }
                } else {
                    showNotification(result.message, 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showNotification('An error occurred. Please try again.', 'error');
            }
        });
    }

    // Helper functions
    function calculatePasswordStrength(password) {
        let strength = 0;
        
        // Length check
        if (password.length >= 8) strength += 25;
        
        // Character type checks
        if (password.match(/[a-z]/)) strength += 15;
        if (password.match(/[A-Z]/)) strength += 15;
        if (password.match(/[0-9]/)) strength += 15;
        if (password.match(/[^a-zA-Z0-9]/)) strength += 15;
        
        // Additional length bonus
        if (password.length >= 12) strength += 15;
        
        return Math.min(strength, 100);
    }

    function getStrengthClass(strength) {
        if (strength >= 80) return 'very-strong';
        if (strength >= 60) return 'strong';
        if (strength >= 40) return 'medium';
        if (strength >= 20) return 'weak';
        return 'very-weak';
    }

    function getStrengthText(strength) {
        if (strength >= 80) return 'Very Strong';
        if (strength >= 60) return 'Strong';
        if (strength >= 40) return 'Medium';
        if (strength >= 20) return 'Weak';
        return 'Very Weak';
    }

    function validateForm(form) {
        let isValid = true;
        
        // Clear previous errors
        form.querySelectorAll('.error-message').forEach(error => error.remove());
        form.querySelectorAll('.error').forEach(field => field.classList.remove('error'));
        
        // Required fields
        form.querySelectorAll('[required]').forEach(field => {
            if (!field.value.trim()) {
                isValid = false;
                showError(field, 'This field is required');
            }
        });
        
        // Email validation
        const emailInput = form.querySelector('input[type="email"]');
        if (emailInput && !isValidEmail(emailInput.value)) {
            isValid = false;
            showError(emailInput, 'Please enter a valid email address');
        }
        
        // Password validation for registration
        if (form.id === 'register-form') {
            const password = form.querySelector('#register-password');
            const confirmPassword = form.querySelector('#register-confirm-password');
            
            if (password && confirmPassword && password.value !== confirmPassword.value) {
                isValid = false;
                showError(confirmPassword, 'Passwords do not match');
            }
        }
        
        return isValid;
    }

    function showError(field, message) {
        field.classList.add('error');
        const errorDiv = document.createElement('div');
        errorDiv.className = 'error-message';
        errorDiv.textContent = message;
        field.parentNode.appendChild(errorDiv);
    }

    function isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }
});
</script>

<?php require_once 'footer.php'; ?> 