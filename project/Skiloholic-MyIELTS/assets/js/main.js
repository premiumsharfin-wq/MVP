/**
 * MyIELTS - Main JavaScript
 * Client-side functionality and enhancements
 */

document.addEventListener('DOMContentLoaded', function() {
    // Form validation enhancement
    enhanceFormValidation();

    // Auto-save functionality (for test pages)
    setupAutoSave();

    // Image preview on upload
    setupImagePreview();

    // Smooth animations
    addFadeInAnimations();
});

/**
 * Enhance form validation with real-time feedback
 */
function enhanceFormValidation() {
    const forms = document.querySelectorAll('form');

    forms.forEach(form => {
        // Password strength indicator
        const passwordInput = form.querySelector('input[type="password"]');
        if (passwordInput && passwordInput.name === 'password') {
            passwordInput.addEventListener('input', function() {
                checkPasswordStrength(this);
            });
        }

        // Confirm password matching
        const confirmPassword = form.querySelector('input[name="confirm_password"]');
        if (confirmPassword) {
            confirmPassword.addEventListener('input', function() {
                checkPasswordMatch(this);
            });
        }

        // Email validation
        const emailInput = form.querySelector('input[type="email"]');
        if (emailInput) {
            emailInput.addEventListener('blur', function() {
                validateEmail(this);
            });
        }
    });
}

/**
 * Check password strength
 */
function checkPasswordStrength(input) {
    const password = input.value;
    let strength = 0;

    if (password.length >= 8) strength++;
    if (password.match(/[a-z]/)) strength++;
    if (password.match(/[A-Z]/)) strength++;
    if (password.match(/[0-9]/)) strength++;
    if (password.match(/[^a-zA-Z0-9]/)) strength++;

    // Remove existing strength indicator
    let indicator = input.parentElement.querySelector('.password-strength');
    if (indicator) {
        indicator.remove();
    }

    if (password.length > 0) {
        indicator = document.createElement('small');
        indicator.className = 'password-strength';

        if (strength < 3) {
            indicator.textContent = 'Weak password';
            indicator.style.color = '#ef4444';
        } else if (strength < 4) {
            indicator.textContent = 'Medium password';
            indicator.style.color = '#f59e0b';
        } else {
            indicator.textContent = 'Strong password';
            indicator.style.color = '#10b981';
        }

        input.parentElement.appendChild(indicator);
    }
}

/**
 * Check if passwords match
 */
function checkPasswordMatch(confirmInput) {
    const form = confirmInput.closest('form');
    const passwordInput = form.querySelector('input[name="password"]');

    if (!passwordInput) return;

    // Remove existing match indicator
    let indicator = confirmInput.parentElement.querySelector('.password-match');
    if (indicator) {
        indicator.remove();
    }

    if (confirmInput.value.length > 0) {
        indicator = document.createElement('small');
        indicator.className = 'password-match';

        if (confirmInput.value === passwordInput.value) {
            indicator.textContent = 'Passwords match';
            indicator.style.color = '#10b981';
        } else {
            indicator.textContent = 'Passwords do not match';
            indicator.style.color = '#ef4444';
        }

        confirmInput.parentElement.appendChild(indicator);
    }
}

/**
 * Validate email format
 */
function validateEmail(input) {
    const email = input.value;
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    if (email.length > 0 && !emailRegex.test(email)) {
        input.style.borderColor = '#ef4444';
    } else {
        input.style.borderColor = '';
    }
}

/**
 * Setup auto-save for text areas (test answers)
 */
function setupAutoSave() {
    const textareas = document.querySelectorAll('textarea[data-autosave]');

    textareas.forEach(textarea => {
        const key = textarea.dataset.autosave;

        // Load saved content
        const saved = localStorage.getItem(key);
        if (saved && textarea.value === '') {
            textarea.value = saved;
        }

        // Save on input (debounced)
        let timeout;
        textarea.addEventListener('input', function() {
            clearTimeout(timeout);
            timeout = setTimeout(() => {
                localStorage.setItem(key, this.value);
                showAutoSaveIndicator(this);
            }, 1000);
        });
    });
}

/**
 * Show auto-save indicator
 */
function showAutoSaveIndicator(element) {
    let indicator = element.parentElement.querySelector('.autosave-indicator');

    if (!indicator) {
        indicator = document.createElement('small');
        indicator.className = 'autosave-indicator';
        indicator.style.color = '#10b981';
        element.parentElement.appendChild(indicator);
    }

    indicator.textContent = '✓ Saved';

    setTimeout(() => {
        indicator.textContent = '';
    }, 2000);
}

/**
 * Setup image preview on upload
 */
function setupImagePreview() {
    const imageInputs = document.querySelectorAll('input[type="file"][accept*="image"]');

    imageInputs.forEach(input => {
        input.addEventListener('change', function() {
            previewImages(this);
        });
    });
}

/**
 * Preview uploaded images
 */
function previewImages(input) {
    const files = input.files;

    // Remove existing preview
    let preview = input.parentElement.querySelector('.image-preview');
    if (preview) {
        preview.remove();
    }

    if (files.length > 0) {
        preview = document.createElement('div');
        preview.className = 'image-preview';
        preview.style.display = 'flex';
        preview.style.gap = '10px';
        preview.style.flexWrap = 'wrap';
        preview.style.marginTop = '10px';

        Array.from(files).forEach(file => {
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.style.width = '100px';
                    img.style.height = '100px';
                    img.style.objectFit = 'cover';
                    img.style.borderRadius = '8px';
                    img.style.border = '2px solid #e5e7eb';
                    preview.appendChild(img);
                };
                reader.readAsDataURL(file);
            }
        });

        input.parentElement.appendChild(preview);
    }
}

/**
 * Add fade-in animations to elements
 */
function addFadeInAnimations() {
    const elements = document.querySelectorAll('.card, .auth-box');

    elements.forEach((element, index) => {
        setTimeout(() => {
            element.classList.add('fade-in');
        }, index * 100);
    });
}

/**
 * Word counter for text areas
 */
function setupWordCounter(textareaId, counterId) {
    const textarea = document.getElementById(textareaId);
    const counter = document.getElementById(counterId);

    if (!textarea || !counter) return;

    function updateCount() {
        const text = textarea.value.trim();
        const words = text.length > 0 ? text.split(/\s+/).length : 0;
        counter.textContent = `${words} words`;
    }

    textarea.addEventListener('input', updateCount);
    updateCount();
}

/**
 * Confirmation dialog for destructive actions
 */
function confirmAction(message) {
    return confirm(message || 'Are you sure you want to proceed?');
}

/**
 * Show notification toast
 */
function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `alert alert-${type}`;
    toast.style.position = 'fixed';
    toast.style.top = '20px';
    toast.style.right = '20px';
    toast.style.minWidth = '250px';
    toast.style.zIndex = '9999';
    toast.style.animation = 'fadeIn 0.3s ease-out';

    const p = document.createElement('p');
    p.textContent = message;
    toast.appendChild(p);

    document.body.appendChild(toast);

    setTimeout(() => {
        toast.style.animation = 'fadeOut 0.3s ease-out';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

/**
 * Format time remaining
 */
function formatTimeRemaining(seconds) {
    const hours = Math.floor(seconds / 3600);
    const minutes = Math.floor((seconds % 3600) / 60);
    const secs = seconds % 60;

    if (hours > 0) {
        return `${hours}h ${minutes}m ${secs}s`;
    } else if (minutes > 0) {
        return `${minutes}m ${secs}s`;
    } else {
        return `${secs}s`;
    }
}

/**
 * Clear local storage auto-save for specific key
 */
function clearAutoSave(key) {
    localStorage.removeItem(key);
}

// Export functions for use in other scripts
window.MyIELTS = {
    setupWordCounter,
    confirmAction,
    showToast,
    formatTimeRemaining,
    clearAutoSave
};
