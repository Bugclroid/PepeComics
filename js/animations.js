import { showNotification } from './main.js';

// Pepe Smash Animation
export function triggerPepeSmash() {
    const pepe = document.getElementById('pepe-smash');
    if (!pepe) {
        const newPepe = document.createElement('img');
        newPepe.id = 'pepe-smash';
        newPepe.src = '/pepecomics/images/animations/pepe-smash.gif';
        newPepe.alt = 'Pepe Smash Animation';
        newPepe.className = 'pepe-smash';
        document.body.appendChild(newPepe);
        return triggerPepeSmash();
    }

    return new Promise((resolve) => {
        pepe.style.display = 'block';
        pepe.classList.add('animate');
        
        const handleAnimationEnd = () => {
            pepe.classList.remove('animate');
            pepe.style.display = 'none';
            pepe.removeEventListener('animationend', handleAnimationEnd);
            resolve();
        };

        pepe.addEventListener('animationend', handleAnimationEnd);
    });
}

// Pepe Dance Animation
export function initializePepeDance() {
    const pepe = document.createElement('img');
    pepe.src = '/pepecomics/images/animations/pepe-dance.gif';
    pepe.alt = 'Dancing Pepe';
    pepe.className = 'pepe-dance';
    document.body.appendChild(pepe);

    // Add scroll-based animation
    window.addEventListener('scroll', () => {
        const scrollPercent = (window.scrollY / (document.documentElement.scrollHeight - window.innerHeight)) * 100;
        pepe.style.bottom = `${20 + (scrollPercent / 5)}px`;
    });
}

// Pepe Punch Animation
export function triggerPepePunch(comicId) {
    const pepe = document.getElementById(`pepe-punch-${comicId}`);
    if (!pepe) return;

    return new Promise((resolve) => {
        pepe.style.display = 'block';
        pepe.classList.add('animate');
        
        const handleAnimationEnd = () => {
            pepe.classList.remove('animate');
            pepe.style.display = 'none';
            pepe.removeEventListener('animationend', handleAnimationEnd);
            resolve();
        };

        pepe.addEventListener('animationend', handleAnimationEnd);
    });
}

// Cart Item Animations
export function animateCartItem(element, type) {
    if (!element) return Promise.resolve();

    return new Promise((resolve) => {
        let animation = '';
        
        switch (type) {
            case 'add':
                animation = 'slideIn 0.3s ease-out forwards';
                break;
            case 'remove':
                animation = 'slideOut 0.3s ease-out forwards';
                break;
            case 'update':
                animation = 'pulse 0.3s ease-out';
                break;
            default:
                return resolve();
        }

        element.style.animation = animation;
        
        const handleAnimationEnd = () => {
            element.style.animation = '';
            element.removeEventListener('animationend', handleAnimationEnd);
            resolve();
        };

        element.addEventListener('animationend', handleAnimationEnd);
    });
}

// Add the necessary CSS animations
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateX(-100%);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    @keyframes slideOut {
        from {
            opacity: 1;
            transform: translateX(0);
        }
        to {
            opacity: 0;
            transform: translateX(100%);
        }
    }

    @keyframes pulse {
        0% {
            transform: scale(1);
        }
        50% {
            transform: scale(1.05);
        }
        100% {
            transform: scale(1);
        }
    }

    .pepe-smash {
        position: fixed;
        bottom: 20px;
        right: 20px;
        width: 100px;
        height: 100px;
        z-index: 9999;
        display: none;
    }

    .pepe-smash.animate {
        display: block;
        animation: pepeSmash 1s ease-in-out;
    }

    .pepe-dance {
        position: fixed;
        bottom: 20px;
        right: 20px;
        width: 50px;
        height: 50px;
        z-index: 9998;
        transition: bottom 0.3s ease-out;
    }

    .pepe-punch {
        display: none;
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 100px;
        height: 100px;
        z-index: 9997;
    }

    .pepe-punch.animate {
        display: block;
        animation: pepePunch 0.5s ease-in-out;
    }

    @keyframes pepeSmash {
        0% {
            transform: scale(1) rotate(0deg);
        }
        50% {
            transform: scale(1.2) rotate(10deg);
        }
        100% {
            transform: scale(1) rotate(0deg);
        }
    }

    @keyframes pepePunch {
        0% {
            transform: translate(-50%, -50%) scale(1);
        }
        50% {
            transform: translate(-50%, -50%) scale(1.2);
        }
        100% {
            transform: translate(-50%, -50%) scale(1);
        }
    }
`;

document.head.appendChild(style);

// Add to Cart Animation
export function triggerAddToCartAnimation(button) {
    return new Promise((resolve) => {
        button.classList.add('success');
        
        const handleAnimationEnd = () => {
            button.classList.remove('success');
            button.removeEventListener('animationend', handleAnimationEnd);
            resolve();
        };

        button.addEventListener('animationend', handleAnimationEnd);
    });
}

// Pepe Loading Animation
export function showPepeLoading() {
    const loading = document.createElement('div');
    loading.className = 'loading';
    loading.innerHTML = '<img src="/images/animations/pepe-loading.gif" alt="Loading..." class="pepe-loading">';
    document.body.appendChild(loading);
    return loading;
}

export function hidePepeLoading(loading) {
    if (loading && loading.parentNode) {
        loading.remove();
    }
}

// Page Transition Animation
export function triggerPageTransition(callback) {
    const content = document.querySelector('.page-content');
    if (!content) return callback();

    content.style.opacity = '0';
    content.style.transform = 'translateY(20px)';

    setTimeout(() => {
        callback();
        content.style.opacity = '1';
        content.style.transform = 'translateY(0)';
    }, 300);
}

// Pepe Error Animation
export function triggerPepeError(element) {
    return new Promise((resolve) => {
        element.classList.add('pepe-error');
        
        const handleAnimationEnd = () => {
            element.classList.remove('pepe-error');
            element.removeEventListener('animationend', handleAnimationEnd);
            resolve();
        };

        element.addEventListener('animationend', handleAnimationEnd);
    });
}

// Mobile Menu Animation
export function toggleMobileMenu(show = true) {
    const menu = document.getElementById('mobile-menu');
    if (!menu) return;

    menu.classList.toggle('active', show);
}

// Notification Animation with Pepe
export function showPepeNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification notification--${type}`;
    
    const pepeIcon = document.createElement('img');
    pepeIcon.src = `/images/animations/pepe-${type}.gif`;
    pepeIcon.alt = type;
    pepeIcon.className = 'notification__icon';
    
    const messageText = document.createElement('span');
    messageText.textContent = message;
    
    notification.appendChild(pepeIcon);
    notification.appendChild(messageText);
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.classList.add('notification--hide');
        notification.addEventListener('animationend', () => {
            notification.remove();
        });
    }, 3000);
}

// Initialize all animations
export function initializeAnimations() {
    // Initialize dancing Pepe
    initializePepeDance();
    
    // Add hover animations to cards
    const cards = document.querySelectorAll('.comic-card');
    cards.forEach(card => {
        card.addEventListener('mouseenter', () => {
            card.style.transform = 'translateY(-5px)';
        });
        
        card.addEventListener('mouseleave', () => {
            card.style.transform = 'translateY(0)';
        });
    });
    
    // Add button hover effects
    const buttons = document.querySelectorAll('.btn');
    buttons.forEach(button => {
        button.addEventListener('mouseenter', () => {
            button.style.transform = 'translateY(-2px)';
        });
        
        button.addEventListener('mouseleave', () => {
            button.style.transform = 'translateY(0)';
        });
    });
} 