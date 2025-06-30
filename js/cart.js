import { showNotification, updateCartCount } from './main.js';
import { triggerPepePunch, animateCartItem } from './animations.js';

export class Cart {
    constructor() {
        this.items = new Map();
        this.initialize();
    }

    initialize() {
        // Add event listeners to cart buttons
        document.addEventListener('click', (e) => {
            const addToCartBtn = e.target.closest('.add-to-cart');
            const removeFromCartBtn = e.target.closest('.cart-item__remove');
            const quantityInput = e.target.closest('.quantity-input');

            if (addToCartBtn) {
                e.preventDefault();
                this.handleAddToCart(addToCartBtn);
            } else if (removeFromCartBtn) {
                e.preventDefault();
                this.handleRemoveFromCart(removeFromCartBtn);
            } else if (quantityInput) {
                this.handleQuantityChange(quantityInput);
            }
        });
    }

    async handleAddToCart(buttonOrId, quantity = 1) {
        let button = null;
        try {
            let comicId;

            // Check if first parameter is a button element or comic ID
            if (buttonOrId instanceof Element) {
                button = buttonOrId;
                comicId = button.dataset.comicId;
                const quantityInput = button.closest('form')?.querySelector('input[name="quantity"]');
                quantity = parseInt(quantityInput?.value || '1');
                
                // Show loading state
                button.classList.add('loading');
                button.disabled = true;
            } else {
                comicId = buttonOrId;
            }

            if (!comicId) {
                throw new Error('Invalid comic ID');
            }

            console.log('Adding to cart:', { comicId, quantity });

            const response = await fetch('/pepecomics/php/controllers/cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: 'add',
                    comic_id: comicId,
                    quantity: quantity
                })
            });

            console.log('Response status:', response.status);
            
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }

            const result = await response.json();
            console.log('Response data:', result);
            
            if (result.success) {
                // Update UI
                updateCartCount(result.cartCount);
                
                // Show success notification
                showNotification('Item added to cart!', 'success');
                
                // Trigger Pepe punch animation
                await triggerPepePunch(comicId);
                
                // Update quantity if on product page and using a form
                if (button) {
                    const quantityInput = button.closest('form')?.querySelector('input[name="quantity"]');
                    if (quantityInput) {
                        quantityInput.value = '1';
                    }
                }
            } else {
                throw new Error(result.message || 'Failed to add item to cart');
            }
        } catch (error) {
            console.error('Error adding to cart:', error);
            showNotification(error.message || 'Failed to add item to cart', 'error');
        } finally {
            // Reset button state if a button was used
            if (button) {
                button.classList.remove('loading');
                button.disabled = false;
            }
        }
    }

    async handleRemoveFromCart(button) {
        try {
            button.disabled = true;
            
            const comicId = button.dataset.comicId;
            const cartItem = button.closest('.cart-item');

            if (!comicId || !cartItem) {
                throw new Error('Invalid cart item');
            }

            console.log('Removing from cart:', comicId);

            const response = await fetch('/pepecomics/php/controllers/cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: 'remove',
                    comic_id: comicId
                })
            });

            console.log('Response status:', response.status);

            if (!response.ok) {
                throw new Error('Network response was not ok');
            }

            const result = await response.json();
            console.log('Response data:', result);
            
            if (result.success) {
                // Animate removal
                await animateCartItem(cartItem, 'remove');
                cartItem.remove();

                // Update cart count and total
                updateCartCount(result.cartCount);
                this.updateCartTotal(result.total);

                // Show empty cart if no items left
                if (result.cartCount === 0) {
                    location.reload();
                }

                showNotification('Item removed from cart', 'success');
            } else {
                throw new Error(result.message || 'Failed to remove item');
            }
        } catch (error) {
            console.error('Error removing from cart:', error);
            showNotification(error.message || 'Failed to remove item from cart', 'error');
        } finally {
            button.disabled = false;
        }
    }

    async handleQuantityChange(input) {
        try {
            const comicId = input.dataset.comicId;
            const quantity = parseInt(input.value);
            const cartItem = input.closest('.cart-item');

            if (!comicId || !cartItem || isNaN(quantity)) {
                throw new Error('Invalid input');
            }

            // Store previous value
            const previousQuantity = input.dataset.previousQuantity || input.value;
            input.dataset.previousQuantity = input.value;

            // Validate quantity
            const min = parseInt(input.min) || 1;
            const max = parseInt(input.max) || 99;
            
            if (quantity < min) {
                input.value = min;
                return;
            }
            if (quantity > max) {
                input.value = max;
                return;
            }

            console.log('Updating quantity:', { comicId, quantity });

            const response = await fetch('/pepecomics/php/controllers/cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: 'update',
                    comic_id: comicId,
                    quantity: quantity
                })
            });

            console.log('Response status:', response.status);

            if (!response.ok) {
                throw new Error('Network response was not ok');
            }

            const result = await response.json();
            console.log('Response data:', result);
            
            if (result.success) {
                // Update item total
                const totalElement = cartItem.querySelector('.cart-item__total');
                if (totalElement) {
                    totalElement.textContent = `$${result.itemTotal.toFixed(2)}`;
                    totalElement.classList.add('price-change');
                    setTimeout(() => totalElement.classList.remove('price-change'), 300);
                }

                // Update cart total
                this.updateCartTotal(result.total);

                // Update quantity buttons
                const decreaseBtn = cartItem.querySelector('.decrease');
                const increaseBtn = cartItem.querySelector('.increase');
                
                if (decreaseBtn) {
                    decreaseBtn.disabled = quantity <= min;
                }
                if (increaseBtn) {
                    increaseBtn.disabled = quantity >= max;
                }

                // Animate the update
                await animateCartItem(cartItem, 'update');

                showNotification('Cart updated', 'success');
            } else {
                throw new Error(result.message || 'Failed to update quantity');
            }
        } catch (error) {
            console.error('Error updating quantity:', error);
            showNotification(error.message || 'Failed to update quantity', 'error');
            
            // Revert to previous quantity
            const previousQuantity = input.dataset.previousQuantity;
            if (previousQuantity) {
                input.value = previousQuantity;
            }
        }
    }

    updateCartTotal(total) {
        const totalElement = document.querySelector('.cart-total__amount');
        if (totalElement) {
            totalElement.textContent = `$${parseFloat(total).toFixed(2)}`;
            totalElement.classList.add('price-change');
            setTimeout(() => totalElement.classList.remove('price-change'), 300);
        }
    }
}

// Initialize cart when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.cart = new Cart();
}); 