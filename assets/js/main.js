// Main JavaScript for Borewell System

document.addEventListener('DOMContentLoaded', function() {
    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        }, 5000);
    });
    
    // Form validation
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const requiredFields = form.querySelectorAll('[required]');
            let valid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    valid = false;
                    field.style.borderColor = 'var(--accent-color)';
                    
                    // Add error message
                    let errorMsg = field.parentElement.querySelector('.error-message');
                    if (!errorMsg) {
                        errorMsg = document.createElement('div');
                        errorMsg.className = 'error-message';
                        errorMsg.style.color = 'var(--accent-color)';
                        errorMsg.style.fontSize = '0.9rem';
                        errorMsg.style.marginTop = '5px';
                        field.parentElement.appendChild(errorMsg);
                    }
                    errorMsg.textContent = 'This field is required';
                } else {
                    field.style.borderColor = '#ddd';
                    const errorMsg = field.parentElement.querySelector('.error-message');
                    if (errorMsg) errorMsg.remove();
                }
            });
            
            if (!valid) {
                e.preventDefault();
                return false;
            }
            return true;
        });
    });
    
    // Format currency
    window.formatCurrency = function(amount) {
        return 'Rp ' + amount.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    };
    
    // Format date
    window.formatDate = function(dateString) {
        const options = { year: 'numeric', month: 'long', day: 'numeric' };
        return new Date(dateString).toLocaleDateString('id-ID', options);
    };
    
    // Check if user is logged in
    window.isLoggedIn = function() {
        const userData = JSON.parse(localStorage.getItem('currentUser') || '{}');
        return userData.loggedIn === true;
    };
    
    // Get current user
    window.getCurrentUser = function() {
        return JSON.parse(localStorage.getItem('currentUser') || '{}');
    };
    
    // Logout function
    window.logout = function() {
        localStorage.removeItem('currentUser');
        window.location.href = 'login.html';
    };
    
    // Mobile menu toggle (if needed)
    const mobileMenuBtn = document.getElementById('mobileMenuBtn');
    if (mobileMenuBtn) {
        mobileMenuBtn.addEventListener('click', function() {
            const nav = document.querySelector('.main-nav ul');
            nav.classList.toggle('show');
        });
    }
    
    // Initialize demo data if not exists
    if (!localStorage.getItem('orders')) {
        const demoOrders = [
            {
                id: 'BW2024-001',
                customer: 'Budi Santoso',
                service: 'Borewell Drilling',
                date: '2024-01-15',
                status: 'completed',
                amount: 5500000,
                location: 'Jl. Merdeka No. 123, Jakarta'
            },
            {
                id: 'BW2024-002',
                customer: 'Siti Nurbaya',
                service: 'Pump Installation',
                date: '2024-01-16',
                status: 'in_progress',
                amount: 3300000,
                location: 'Jl. Sudirman No. 456, Bandung'
            }
        ];
        localStorage.setItem('orders', JSON.stringify(demoOrders));
    }
    
    if (!localStorage.getItem('customers')) {
        const demoCustomers = [
            {
                id: 1,
                name: 'PT. Sumber Air Makmur',
                email: 'contact@airmakmur.com',
                phone: '08123456789',
                address: 'Jl. Industri No. 123, Jakarta',
                join_date: '2023-05-15',
                total_orders: 12,
                total_spent: 45000000
            },
            {
                id: 2,
                name: 'CV. Tirta Jaya',
                email: 'tirta@jayawater.com',
                phone: '08234567890',
                address: 'Jl. Perdagangan No. 456, Bandung',
                join_date: '2023-07-20',
                total_orders: 8,
                total_spent: 28000000
            }
        ];
        localStorage.setItem('customers', JSON.stringify(demoCustomers));
    }
});