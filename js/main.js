// Add notification styles
const style = document.createElement('style');
style.textContent = `
    .notification {
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 25px;
        border-radius: 5px;
        color: white;
        font-weight: bold;
        z-index: 10000;
        animation: slideIn 0.3s ease-out;
    }

    .notification--success {
        background-color: #4CAF50;
    }

    .notification--error {
        background-color: #f44336;
    }

    .notification--info {
        background-color: #2196F3;
    }

    .notification--warning {
        background-color: #ff9800;
    }

    .notification.fade-out {
        animation: fadeOut 0.3s ease-out forwards;
    }

    .cart-count {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 20px;
        height: 20px;
        padding: 0 5px;
        border-radius: 10px;
        background-color: #f44336;
        color: white;
        font-size: 12px;
        font-weight: bold;
    }

    .cart-count.bounce {
        animation: bounce 0.3s ease-out;
    }

    .cart-total.highlight {
        animation: highlight 0.3s ease-out;
    }

    .pepe-cursor {
        position: fixed;
        width: 50px;
        height: 50px;
        pointer-events: none;
        z-index: 9999;
        opacity: 0;
        transition: opacity 0.3s ease-out;
    }

    .pepe-cursor.active {
        opacity: 1;
    }

    .pepe-click {
        position: fixed;
        width: 40px;
        height: 40px;
        pointer-events: none;
        z-index: 9998;
        animation: clickAnimation 0.5s ease-out forwards;
    }

    @keyframes clickAnimation {
        0% {
            transform: scale(0.5);
            opacity: 1;
        }
        50% {
            transform: scale(1.2);
            opacity: 0.8;
        }
        100% {
            transform: scale(1);
            opacity: 0;
        }
    }

    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    @keyframes fadeOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }

    @keyframes bounce {
        0%, 100% {
            transform: scale(1);
        }
        50% {
            transform: scale(1.2);
        }
    }

    @keyframes highlight {
        0%, 100% {
            color: inherit;
        }
        50% {
            color: #f44336;
        }
    }
`;

document.head.appendChild(style);

// Main JavaScript functionality
export function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification notification--${type}`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    // Remove notification after 3 seconds
    setTimeout(() => {
        notification.classList.add('fade-out');
        notification.addEventListener('animationend', () => {
            notification.remove();
        });
    }, 3000);
}

// Function to update cart count in the header
export function updateCartCount(count) {
    const cartCountElement = document.querySelector('.cart-count');
    if (cartCountElement) {
        cartCountElement.textContent = count;
        // Show animation if count increased
        if (count > parseInt(cartCountElement.dataset.previousCount || '0')) {
            cartCountElement.classList.add('bounce');
            setTimeout(() => cartCountElement.classList.remove('bounce'), 300);
        }
        cartCountElement.dataset.previousCount = count;
    }
}

// Function to update cart total price
export function updateCartTotal(total) {
    const cartTotalElement = document.querySelector('.cart-total');
    if (cartTotalElement) {
        cartTotalElement.textContent = `$${parseFloat(total).toFixed(2)}`;
        cartTotalElement.classList.add('highlight');
        setTimeout(() => cartTotalElement.classList.remove('highlight'), 300);
    }
}

// Function to format price
export function formatPrice(price) {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(price);
}

// Function to debounce function calls
export function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Function to validate form inputs
export function validateInput(input, type) {
    switch (type) {
        case 'email':
            return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(input);
        case 'phone':
            return /^\+?[\d\s-]{10,}$/.test(input);
        case 'number':
            return !isNaN(input) && input > 0;
        default:
            return input.trim().length > 0;
    }
}

// Initialize Pepe cursor
function initPepeCursor() {
    const cursor = document.createElement('div');
    cursor.className = 'pepe-cursor';
    cursor.innerHTML = '<img src="/pepecomics/images/animations/pepe-cursor.gif" alt="Cursor">';
    document.body.appendChild(cursor);

    let cursorTimeout;

    document.addEventListener('mousemove', (e) => {
        cursor.style.left = e.clientX + 'px';
        cursor.style.top = e.clientY + 'px';
        
        cursor.classList.add('active');
        clearTimeout(cursorTimeout);
        
        cursorTimeout = setTimeout(() => {
            cursor.classList.remove('active');
        }, 2000);
    });

    // Add click animation
    document.addEventListener('click', (e) => {
        const clickAnim = document.createElement('div');
        clickAnim.className = 'pepe-click';
        clickAnim.innerHTML = '<img src="/pepecomics/images/animations/pepe-click.gif" alt="Click">';
        clickAnim.style.left = (e.clientX - 20) + 'px'; // Center the 40px wide animation
        clickAnim.style.top = (e.clientY - 20) + 'px';  // Center the 40px high animation
        document.body.appendChild(clickAnim);

        // Remove the element after animation completes
        clickAnim.addEventListener('animationend', () => {
            clickAnim.remove();
        });
    });
}

// Initialize everything when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Pepe cursor
    initPepeCursor();

    // Mobile menu toggle
    const mobileMenuButton = document.getElementById('mobile-menu-button');
    const mobileMenu = document.getElementById('mobile-menu');
    
    if (mobileMenuButton && mobileMenu) {
        mobileMenuButton.addEventListener('click', function() {
            const isVisible = mobileMenu.style.display === 'block';
            mobileMenu.style.display = isVisible ? 'none' : 'block';
            mobileMenuButton.setAttribute('aria-expanded', !isVisible);
        });
    }

    // Close mobile menu on outside click
    document.addEventListener('click', function(event) {
        if (mobileMenu && 
            mobileMenu.style.display === 'block' && 
            !mobileMenu.contains(event.target) && 
            !mobileMenuButton.contains(event.target)) {
            mobileMenu.style.display = 'none';
            mobileMenuButton.setAttribute('aria-expanded', 'false');
        }
    });

    // Initialize tooltips
    const tooltips = document.querySelectorAll('[data-tooltip]');
    tooltips.forEach(element => {
        element.addEventListener('mouseenter', e => {
            const tooltip = document.createElement('div');
            tooltip.className = 'tooltip';
            tooltip.textContent = element.dataset.tooltip;
            document.body.appendChild(tooltip);

            const rect = element.getBoundingClientRect();
            tooltip.style.top = rect.top - tooltip.offsetHeight - 10 + 'px';
            tooltip.style.left = rect.left + (rect.width - tooltip.offsetWidth) / 2 + 'px';
        });

        element.addEventListener('mouseleave', () => {
            const tooltip = document.querySelector('.tooltip');
            if (tooltip) {
                tooltip.remove();
            }
        });
    });
});
