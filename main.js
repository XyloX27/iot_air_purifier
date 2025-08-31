// Main JavaScript for IoT Air Purifier System

document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    initTooltips();
    
    // Initialize real-time updates for dashboard
    if (document.querySelector('.dashboard-content')) {
        initRealTimeUpdates();
    }
    
    // Initialize form validation
    initFormValidation();
});

// Tooltip initialization
function initTooltips() {
    const tooltipElements = document.querySelectorAll('[data-tooltip]');
    
    tooltipElements.forEach(element => {
        element.addEventListener('mouseenter', showTooltip);
        element.addEventListener('mouseleave', hideTooltip);
    });
}

function showTooltip(e) {
    const tooltipText = this.getAttribute('data-tooltip');
    const tooltip = document.createElement('div');
    tooltip.className = 'tooltip';
    tooltip.textContent = tooltipText;
    document.body.appendChild(tooltip);
    
    const rect = this.getBoundingClientRect();
    tooltip.style.left = rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2) + 'px';
    tooltip.style.top = (rect.top - tooltip.offsetHeight - 5) + 'px';
}

function hideTooltip() {
    const tooltip = document.querySelector('.tooltip');
    if (tooltip) {
        tooltip.remove();
    }
}

// Real-time updates for dashboard
function initRealTimeUpdates() {
    // Update data every 30 seconds
    setInterval(updateDashboardData, 30000);
}

function updateDashboardData() {
    fetch('api/get_latest_data.php')
        .then(response => response.json())
        .then(data => {
            // Update stats cards
            document.querySelector('.stat-number:nth-child(1)').textContent = data.totalDevices;
            document.querySelector('.stat-number:nth-child(2)').textContent = data.onlineDevices;
            document.querySelector('.stat-number:nth-child(3)').textContent = data.offlineDevices;
            document.querySelector('.stat-number:nth-child(4)').textContent = data.currentPm25 + ' μg/m³';
            
            // Update recent readings table
            updateReadingsTable(data.latestReadings);
        })
        .catch(error => console.error('Error fetching data:', error));
}

function updateReadingsTable(readings) {
    const tableBody = document.querySelector('.recent-readings tbody');
    tableBody.innerHTML = '';
    
    readings.forEach(reading => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${reading.device_name}</td>
            <td>${reading.pm25} μg/m³</td>
            <td>${reading.pm10} μg/m³</td>
            <td>${reading.temperature}°C</td>
            <td>${reading.humidity}%</td>
            <td>${new Date(reading.timestamp).toLocaleString()}</td>
        `;
        tableBody.appendChild(row);
    });
}

// Form validation
function initFormValidation() {
    const forms = document.querySelectorAll('form');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            let isValid = true;
            const inputs = this.querySelectorAll('input[required], select[required], textarea[required]');
            
            inputs.forEach(input => {
                if (!input.value.trim()) {
                    isValid = false;
                    highlightError(input, 'This field is required');
                } else {
                    removeErrorHighlight(input);
                    
                    // Email validation
                    if (input.type === 'email' && !isValidEmail(input.value)) {
                        isValid = false;
                        highlightError(input, 'Please enter a valid email address');
                    }
                    
                    // Password confirmation validation
                    if (input.id === 'confirm_password') {
                        const password = document.getElementById('new_password');
                        if (password && input.value !== password.value) {
                            isValid = false;
                            highlightError(input, 'Passwords do not match');
                        }
                    }
                }
            });
            
            if (!isValid) {
                e.preventDefault();
            }
        });
    });
}

function highlightError(input, message) {
    removeErrorHighlight(input);
    
    input.classList.add('error');
    
    const errorDiv = document.createElement('div');
    errorDiv.className = 'error-message';
    errorDiv.textContent = message;
    input.parentNode.appendChild(errorDiv);
}

function removeErrorHighlight(input) {
    input.classList.remove('error');
    
    const existingError = input.parentNode.querySelector('.error-message');
    if (existingError) {
        existingError.remove();
    }
}

function isValidEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

// Utility function for making API calls
function apiCall(endpoint, method = 'GET', data = null) {
    const options = {
        method: method,
        headers: {
            'Content-Type': 'application/json',
        },
    };
    
    if (data && (method === 'POST' || method === 'PUT')) {
        options.body = JSON.stringify(data);
    }
    
    return fetch(endpoint, options)
        .then(response => {
            if (!response.ok) {
                throw new Error('API request failed');
            }
            return response.json();
        });
}

// Export functions for use in other modules
window.airPurifierApp = {
    apiCall,
    isValidEmail,
    updateDashboardData
};