// Review Form Handling
document.addEventListener('DOMContentLoaded', () => {
    const reviewForm = document.getElementById('review-form');
    const reviewModal = document.getElementById('review-modal');
    const openReviewModalBtn = document.getElementById('write-review-btn');
    const closeReviewModalBtn = document.getElementById('close-review-modal');
    const ratingInputs = document.querySelectorAll('.rating-input input');
    const reviewContent = document.getElementById('review-content');
    const reviewsList = document.querySelector('.reviews-grid');

    // Open review modal
    if (openReviewModalBtn) {
        openReviewModalBtn.addEventListener('click', (e) => {
            e.preventDefault();
            reviewModal.classList.add('active');
        });
    }

    // Close review modal
    if (closeReviewModalBtn) {
        closeReviewModalBtn.addEventListener('click', () => {
            reviewModal.classList.remove('active');
            resetForm();
        });
    }

    // Close modal on outside click
    reviewModal?.addEventListener('click', (e) => {
        if (e.target === reviewModal) {
            reviewModal.classList.remove('active');
            resetForm();
        }
    });

    // Handle rating selection
    ratingInputs.forEach(input => {
        input.addEventListener('change', () => {
            const rating = input.value;
            updateRatingDisplay(rating);
        });
    });

    // Handle form submission
    if (reviewForm) {
        reviewForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            const formData = new FormData(reviewForm);
            formData.append('action', 'create');

            try {
                const response = await fetch('/php/controllers/reviews.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    // Show success message
                    showAlert('success', result.message);
                    
                    // Close modal and reset form
                    reviewModal.classList.remove('active');
                    resetForm();
                    
                    // Refresh reviews list
                    await refreshReviews();
                } else {
                    showAlert('error', result.message);
                }
            } catch (error) {
                console.error('Error submitting review:', error);
                showAlert('error', 'An error occurred while submitting your review.');
            }
        });
    }

    // Helper Functions
    function resetForm() {
        if (reviewForm) {
            reviewForm.reset();
            updateRatingDisplay(0);
        }
    }

    function updateRatingDisplay(rating) {
        const labels = document.querySelectorAll('.rating-input label');
        labels.forEach((label, index) => {
            if (5 - index <= rating) {
                label.style.color = '#FFC107';
            } else {
                label.style.color = '#ddd';
            }
        });
    }

    async function refreshReviews() {
        try {
            const comicId = reviewForm.querySelector('[name="comic_id"]').value;
            const response = await fetch(`/php/controllers/reviews.php?action=get&comic_id=${comicId}`);
            const result = await response.json();

            if (result.success && reviewsList) {
                reviewsList.innerHTML = '';
                result.data.forEach(review => {
                    reviewsList.insertAdjacentHTML('beforeend', createReviewCard(review));
                });

                if (result.data.length === 0) {
                    reviewsList.innerHTML = `
                        <div class="no-reviews">
                            <p>No reviews yet. Be the first to review this comic!</p>
                        </div>
                    `;
                }
            }
        } catch (error) {
            console.error('Error refreshing reviews:', error);
        }
    }

    function createReviewCard(review) {
        const stars = '★'.repeat(review.rating) + '☆'.repeat(5 - review.rating);
        return `
            <div class="review-card">
                <div class="review-header">
                    <div class="reviewer-info">
                        <span class="reviewer-name">${review.user_name}</span>
                        <span class="review-date">${formatDate(review.created_at)}</span>
                    </div>
                    <div class="review-rating">${stars}</div>
                </div>
                <div class="review-content">
                    <p>${review.content}</p>
                </div>
            </div>
        `;
    }

    function formatDate(dateString) {
        const options = { year: 'numeric', month: 'long', day: 'numeric' };
        return new Date(dateString).toLocaleDateString(undefined, options);
    }

    function showAlert(type, message) {
        const alertContainer = document.createElement('div');
        alertContainer.className = `alert alert-${type}`;
        alertContainer.textContent = message;

        document.body.appendChild(alertContainer);

        // Remove alert after 3 seconds
        setTimeout(() => {
            alertContainer.remove();
        }, 3000);
    }
}); 