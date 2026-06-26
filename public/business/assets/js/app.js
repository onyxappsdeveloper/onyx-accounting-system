/**
 * ONYX Accounting System - Main JavaScript
 */

// Initialize on document ready
document.addEventListener('DOMContentLoaded', function() {
    console.log('ONYX Accounting System initialized');
    initializeEventListeners();
});

/**
 * Initialize all event listeners
 */
function initializeEventListeners() {
    // Form submissions
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', handleFormSubmit);
    });
}

/**
 * Handle form submission
 */
function handleFormSubmit(e) {
    e.preventDefault();
    
    const form = e.target;
    const formData = new FormData(form);
    const url = form.action || form.getAttribute('data-action');
    const method = form.method || 'POST';

    // Convert FormData to JSON
    const data = {};
    formData.forEach((value, key) => {
        data[key] = value;
    });

    apiCall(method, url, data)
        .then(response => {
            if (response.success) {
                showNotification(response.message || 'Operation successful', 'success');
                if (response.redirect) {
                    setTimeout(() => {
                        window.location.href = response.redirect;
                    }, 1000);
                } else {
                    form.reset();
                }
            } else {
                if (response.errors) {
                    displayErrors(response.errors);
                } else {
                    showNotification(response.message || 'Operation failed', 'error');
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('An error occurred', 'error');
        });
}

/**
 * API call helper
 */
function apiCall(method, url, data = null) {
    const options = {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    };

    if (data) {
        options.body = JSON.stringify(data);
    }

    return fetch(url, options).then(response => response.json());
}

/**
 * Show notification
 */
function showNotification(message, type = 'info') {
    const alertClass = `alert alert-${type === 'error' ? 'error' : type}`;
    const alertDiv = document.createElement('div');
    alertDiv.className = alertClass;
    alertDiv.innerHTML = `
        <div>${message}</div>
        <button onclick="this.parentElement.style.display='none'" style="float: right; background: none; border: none; font-size: 1.2rem; cursor: pointer;">&times;</button>
    `;
    
    const container = document.querySelector('.main-content') || document.body;
    container.insertBefore(alertDiv, container.firstChild);
    
    // Auto-dismiss
    setTimeout(() => {
        alertDiv.style.display = 'none';
    }, 5000);
}

/**
 * Display form errors
 */
function displayErrors(errors) {
    Object.keys(errors).forEach(field => {
        const input = document.querySelector(`[name="${field}"]`);
        if (input) {
            input.style.borderColor = '#e74c3c';
            const errorDiv = document.createElement('small');
            errorDiv.style.color = '#e74c3c';
            errorDiv.textContent = errors[field][0];
            input.parentElement.appendChild(errorDiv);
        }
    });
}

/**
 * Delete item helper
 */
function deleteItem(id, type) {
    if (!confirm('Are you sure?')) return;
    
    apiCall('DELETE', `/business/${type}/${id}`)
        .then(response => {
            if (response.success) {
                showNotification(response.message || 'Deleted successfully', 'success');
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                showNotification(response.message || 'Deletion failed', 'error');
            }
        });
}
