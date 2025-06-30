// Admin Panel JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Initialize mobile menu
    initializeMobileMenu();

    // Initialize flash messages
    initializeFlashMessages();

    // Initialize tooltips
    initializeTooltips();

    // Initialize form validation
    initializeFormValidation();

    // Initialize bulk actions
    initializeBulkActions();
});

// Mobile Menu
function initializeMobileMenu() {
    const menuButton = document.querySelector('.mobile-menu-button');
    const mobileMenu = document.querySelector('.mobile-menu');

    if (menuButton && mobileMenu) {
        menuButton.addEventListener('click', function() {
            this.classList.toggle('active');
            mobileMenu.classList.toggle('show');
        });
    }
}

// Flash Messages
function initializeFlashMessages() {
    const flashMessages = document.querySelectorAll('.flash-message');
    
    flashMessages.forEach(message => {
        // Auto-hide flash messages after 5 seconds
        setTimeout(() => {
            message.style.opacity = '0';
            setTimeout(() => {
                message.remove();
            }, 300);
        }, 5000);

        // Add close button functionality
        const closeButton = message.querySelector('.close-flash');
        if (closeButton) {
            closeButton.addEventListener('click', () => {
                message.style.opacity = '0';
                setTimeout(() => {
                    message.remove();
                }, 300);
            });
        }
    });
}

// Tooltips
function initializeTooltips() {
    const tooltips = document.querySelectorAll('[data-tooltip]');
    
    tooltips.forEach(element => {
        element.addEventListener('mouseenter', e => {
            const tooltip = document.createElement('div');
            tooltip.className = 'tooltip';
            tooltip.textContent = element.getAttribute('data-tooltip');
            document.body.appendChild(tooltip);

            const rect = element.getBoundingClientRect();
            tooltip.style.top = rect.top - tooltip.offsetHeight - 10 + 'px';
            tooltip.style.left = rect.left + (rect.width - tooltip.offsetWidth) / 2 + 'px';
            tooltip.style.opacity = '1';
        });

        element.addEventListener('mouseleave', () => {
            const tooltip = document.querySelector('.tooltip');
            if (tooltip) {
                tooltip.remove();
            }
        });
    });
}

// Form Validation
function initializeFormValidation() {
    const forms = document.querySelectorAll('form[data-validate]');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            let isValid = true;
            const requiredFields = form.querySelectorAll('[required]');
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    showFieldError(field, field.getAttribute('data-error') || 'This field is required');
                } else {
                    clearFieldError(field);
                }
            });

            if (!isValid) {
                e.preventDefault();
            }
        });

        // Real-time validation
        const fields = form.querySelectorAll('input, select, textarea');
        fields.forEach(field => {
            field.addEventListener('blur', function() {
                if (field.hasAttribute('required') && !field.value.trim()) {
                    showFieldError(field, field.getAttribute('data-error') || 'This field is required');
                } else {
                    clearFieldError(field);
                }
            });
        });
    });
}

function showFieldError(field, message) {
    clearFieldError(field);
    field.classList.add('is-invalid');
    const errorDiv = document.createElement('div');
    errorDiv.className = 'invalid-feedback';
    errorDiv.textContent = message;
    field.parentNode.appendChild(errorDiv);
}

function clearFieldError(field) {
    field.classList.remove('is-invalid');
    const errorDiv = field.parentNode.querySelector('.invalid-feedback');
    if (errorDiv) {
        errorDiv.remove();
    }
}

// Bulk Actions
function initializeBulkActions() {
    const bulkActionForms = document.querySelectorAll('.bulk-action-form');
    
    bulkActionForms.forEach(form => {
        const selectAll = form.querySelector('.select-all');
        const checkboxes = form.querySelectorAll('.bulk-checkbox');
        const bulkActionSelect = form.querySelector('.bulk-action-select');
        const applyButton = form.querySelector('.apply-bulk-action');
        
        if (selectAll && checkboxes.length) {
            // Select all functionality
            selectAll.addEventListener('change', function() {
                checkboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
                updateBulkActionButton();
            });

            // Individual checkbox functionality
            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const allChecked = Array.from(checkboxes).every(c => c.checked);
                    const someChecked = Array.from(checkboxes).some(c => c.checked);
                    selectAll.checked = allChecked;
                    selectAll.indeterminate = someChecked && !allChecked;
                    updateBulkActionButton();
                });
            });

            // Update apply button state
            function updateBulkActionButton() {
                const checkedCount = Array.from(checkboxes).filter(c => c.checked).length;
                if (applyButton) {
                    applyButton.disabled = checkedCount === 0 || !bulkActionSelect.value;
                    applyButton.textContent = `Apply to ${checkedCount} item${checkedCount !== 1 ? 's' : ''}`;
                }
            }

            // Bulk action select functionality
            if (bulkActionSelect) {
                bulkActionSelect.addEventListener('change', updateBulkActionButton);
            }

            // Form submission
            form.addEventListener('submit', function(e) {
                const checkedCount = Array.from(checkboxes).filter(c => c.checked).length;
                if (checkedCount === 0) {
                    e.preventDefault();
                    alert('Please select at least one item.');
                    return;
                }

                const action = bulkActionSelect.value;
                if (!action) {
                    e.preventDefault();
                    alert('Please select an action.');
                    return;
                }

                if (!confirm(`Are you sure you want to ${action} the selected items?`)) {
                    e.preventDefault();
                }
            });
        }
    });
}

// Utility Functions
function formatCurrency(amount) {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(amount);
}

function formatDate(date) {
    return new Intl.DateTimeFormat('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    }).format(new Date(date));
}

function debounce(func, wait) {
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

// Ajax Functions
function ajaxRequest(url, method = 'GET', data = null) {
    const options = {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    };

    if (data && method !== 'GET') {
        options.body = JSON.stringify(data);
    }

    return fetch(url, options)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .catch(error => {
            console.error('Error:', error);
            throw error;
        });
}

// Chart Functions
function initializeCharts() {
    // Sales Chart
    const salesChart = document.getElementById('salesChart');
    if (salesChart) {
        new Chart(salesChart, {
            type: 'line',
            data: {
                labels: salesChart.dataset.labels.split(','),
                datasets: [{
                    label: 'Sales',
                    data: salesChart.dataset.values.split(','),
                    borderColor: '#007bff',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
    }

    // Orders Chart
    const ordersChart = document.getElementById('ordersChart');
    if (ordersChart) {
        new Chart(ordersChart, {
            type: 'bar',
            data: {
                labels: ordersChart.dataset.labels.split(','),
                datasets: [{
                    label: 'Orders',
                    data: ordersChart.dataset.values.split(','),
                    backgroundColor: '#28a745'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
    }
}

// Export functions
window.adminFunctions = {
    formatCurrency,
    formatDate,
    ajaxRequest,
    showFieldError,
    clearFieldError
}; 