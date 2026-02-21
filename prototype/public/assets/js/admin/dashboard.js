/**
 * Admin Dashboard JavaScript
 * Handles profile dropdown and other interactive elements
 */

document.addEventListener('DOMContentLoaded', function() {
    initializeProfileDropdown();
    initializeSidebarInteractions();
    
    // Force service desk dropdown initialization for dashboard
    setTimeout(function() {
        ensureServiceDeskDropdownWorks();
    }, 100);
    
    // Initialize dashboard specific features
    if (window.location.pathname.includes('dashboard.php')) {
        initializeDashboardCharts();
        initializeCardInteractions();
    }
});

/**
 * Initialize profile dropdown functionality
 */
function initializeProfileDropdown() {
    const profileCard = document.getElementById('profileCard');
    const profileDropdown = document.getElementById('profileDropdown');
    
    if (!profileCard || !profileDropdown) return;
    
    let dropdownOpen = false;

    function toggleDropdown() {
        dropdownOpen = !dropdownOpen;
        profileDropdown.style.display = dropdownOpen ? 'flex' : 'none';
    }

    // Toggle dropdown on profile card click
    profileCard.addEventListener('click', function(e) {
        toggleDropdown();
        e.stopPropagation();
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!profileCard.contains(e.target)) {
            dropdownOpen = false;
            profileDropdown.style.display = 'none';
        }
    });

    // Close dropdown on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && dropdownOpen) {
            dropdownOpen = false;
            profileDropdown.style.display = 'none';
        }
    });
}

/**
 * Ensure Service Desk dropdown works specifically for dashboard
 */
function ensureServiceDeskDropdownWorks() {
    const serviceDeskToggle = document.getElementById('serviceDeskToggle');
    const serviceDeskMenu = document.getElementById('serviceDeskMenu');
    
    if (!serviceDeskToggle || !serviceDeskMenu) {
        console.log('Service Desk elements not found');
        return;
    }
    
    console.log('Initializing Service Desk dropdown for dashboard...');
    
    // Remove any existing listeners by cloning elements
    const newToggle = serviceDeskToggle.cloneNode(true);
    serviceDeskToggle.parentNode.replaceChild(newToggle, serviceDeskToggle);
    
    // Add click event listener
    newToggle.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        console.log('Service Desk clicked!');
        
        const isCurrentlyOpen = serviceDeskMenu.classList.contains('show');
        
        // Close all other dropdowns first
        document.querySelectorAll('.sidebar-nav .dropdown-menu.show').forEach(function(openMenu) {
            if (openMenu !== serviceDeskMenu) {
                openMenu.classList.remove('show');
                const otherToggle = document.querySelector('[data-target="' + openMenu.id + '"]');
                if (otherToggle) {
                    otherToggle.classList.remove('active');
                    const otherArrow = otherToggle.querySelector('.dropdown-arrow');
                    if (otherArrow) otherArrow.classList.remove('rotated');
                }
            }
        });
        
        // Toggle current dropdown
        if (isCurrentlyOpen) {
            serviceDeskMenu.classList.remove('show');
            newToggle.classList.remove('active');
            console.log('Closing dropdown');
        } else {
            serviceDeskMenu.classList.add('show');
            newToggle.classList.add('active');
            console.log('Opening dropdown');
        }
        
        // Handle arrow rotation
        const arrow = newToggle.querySelector('.dropdown-arrow');
        if (arrow) {
            if (serviceDeskMenu.classList.contains('show')) {
                arrow.classList.add('rotated');
            } else {
                arrow.classList.remove('rotated');
            }
        }
    });
    
    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!newToggle.contains(e.target) && !serviceDeskMenu.contains(e.target)) {
            serviceDeskMenu.classList.remove('show');
            newToggle.classList.remove('active');
            const arrow = newToggle.querySelector('.dropdown-arrow');
            if (arrow) arrow.classList.remove('rotated');
        }
    });
    
    console.log('Service Desk dropdown initialized successfully!');
}

/**
 * Initialize sidebar interactions
 */
function initializeSidebarInteractions() {
    const sidebarLinks = document.querySelectorAll('.sidebar-nav a');
    
    sidebarLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            // Remove active class from all items
            document.querySelectorAll('.sidebar-nav li').forEach(li => {
                li.classList.remove('active');
            });
            
            // Add active class to clicked item's parent li
            this.closest('li').classList.add('active');
        });
    });
}



/**
 * Initialize sidebar interactions
 */
function initializeSidebarInteractions() {
    // Handle regular navigation links (excluding dropdown toggles and dropdown menu items)
    const sidebarLinks = document.querySelectorAll('.sidebar-nav a:not(.dropdown-toggle)');
    
    sidebarLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            // Don't handle dropdown menu items here - they have their own handler
            if (this.closest('.dropdown-menu')) return;
            
            // Remove active class from all main nav items
            document.querySelectorAll('.sidebar-nav > ul > li').forEach(li => {
                li.classList.remove('active');
            });
            
            // Close any open dropdowns
            document.querySelectorAll('.dropdown-menu').forEach(menu => {
                menu.classList.remove('show');
            });
            
            // Add active class to clicked item's parent li
            this.closest('li').classList.add('active');
        });
    });
}


function showNotification(message, type = 'info') {
 
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;
    

    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 1rem 1.5rem;
        background: ${type === 'success' ? '#d4edda' : type === 'error' ? '#f8d7da' : '#d1ecf1'};
        color: ${type === 'success' ? '#155724' : type === 'error' ? '#721c24' : '#0c5460'};
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        z-index: 1000;
        transition: all 0.3s ease;
    `;
    
    // Add to DOM
    document.body.appendChild(notification);
    
    // Remove after 3 seconds
    setTimeout(() => {
        notification.style.opacity = '0';
        notification.style.transform = 'translateX(100%)';
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }, 3000);
}

/**
 * =====================================
 * DASHBOARD SPECIFIC FUNCTIONS
 * =====================================
 */

/**
 * Initialize dashboard charts
 */
function initializeDashboardCharts() {
    // Check if Chart.js is loaded
    if (typeof Chart === 'undefined') {
        console.error('Chart.js is not loaded!');
        return;
    }
    
    console.log('Initializing dashboard charts...');
    // Spot Reports Chart
    const spotReportsCtx = document.getElementById('spotReportsChart');
    if (spotReportsCtx) {
        console.log('Creating Spot Reports chart...');
        new Chart(spotReportsCtx, {
            type: 'bar',
            data: {
                labels: ['Approved', 'Pending', 'Rejected'],
                datasets: [{
                    label: 'Spot Reports',
                    data: [20, 8, 2],
                    backgroundColor: [
                        'rgba(40, 167, 69, 0.8)',
                        'rgba(255, 193, 7, 0.8)',
                        'rgba(220, 53, 69, 0.8)'
                    ],
                    borderColor: [
                        'rgba(40, 167, 69, 1)',
                        'rgba(255, 193, 7, 1)',
                        'rgba(220, 53, 69, 1)'
                    ],
                    borderWidth: 2,
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            display: false
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    }

    // Case Status Chart
    const caseStatusCtx = document.getElementById('caseStatusChart');
    if (caseStatusCtx) {
        new Chart(caseStatusCtx, {
            type: 'doughnut',
            data: {
                labels: ['Under Investigation', 'For Filing', 'Ongoing', 'Dismissed', 'Resolved'],
                datasets: [{
                    data: [1, 1, 1, 1, 1],
                    backgroundColor: [
                        'rgba(253, 126, 20, 0.8)',
                        'rgba(255, 193, 7, 0.8)',
                        'rgba(23, 162, 184, 0.8)',
                        'rgba(220, 53, 69, 0.8)',
                        'rgba(108, 117, 125, 0.8)'
                    ],
                    borderColor: [
                        'rgba(253, 126, 20, 1)',
                        'rgba(255, 193, 7, 1)',
                        'rgba(23, 162, 184, 1)',
                        'rgba(220, 53, 69, 1)',
                        'rgba(108, 117, 125, 1)'
                    ],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            padding: 15
                        }
                    }
                }
            }
        });
    }

    // Equipment Status Chart
    const equipmentStatusCtx = document.getElementById('equipmentChart');
    if (equipmentStatusCtx) {
        new Chart(equipmentStatusCtx, {
            type: 'bar',
            data: {
                labels: ['In Use', 'Available'],
                datasets: [{
                    label: 'Equipment',
                    data: [4, 0],
                    backgroundColor: [
                        'rgba(40, 167, 69, 0.8)',
                        'rgba(255, 193, 7, 0.8)'
                    ],
                    borderColor: [
                        'rgba(40, 167, 69, 1)',
                        'rgba(255, 193, 7, 1)'
                    ],
                    borderWidth: 2,
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            display: false
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    }

    // User Roles Chart
    const userRolesCtx = document.getElementById('userRolesChart');
    if (userRolesCtx) {
        new Chart(userRolesCtx, {
            type: 'doughnut',
            data: {
                labels: ['Enforcement', 'Enforcer', 'Property Custodian', 'Office Staff'],
                datasets: [{
                    data: [1, 1, 1, 1],
                    backgroundColor: [
                        'rgba(40, 167, 69, 0.8)',
                        'rgba(253, 126, 20, 0.8)',
                        'rgba(111, 66, 193, 0.8)',
                        'rgba(32, 201, 151, 0.8)'
                    ],
                    borderColor: [
                        'rgba(40, 167, 69, 1)',
                        'rgba(253, 126, 20, 1)',
                        'rgba(111, 66, 193, 1)',
                        'rgba(32, 201, 151, 1)'
                    ],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            padding: 15
                        }
                    }
                }
            }
        });
    }
}

/**
 * Initialize card interactions and animations
 */
function initializeCardInteractions() {
    const cards = document.querySelectorAll('.card');
    
    cards.forEach(card => {
        // Add hover effect listeners
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-8px)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
        
        // Add click interaction for cards
        card.addEventListener('click', function() {
            // Add a subtle click animation
            this.style.transform = 'translateY(-4px) scale(0.98)';
            setTimeout(() => {
                this.style.transform = 'translateY(-8px) scale(1)';
            }, 100);
        });
    });
}

/**
 * Refresh dashboard data
 */
function refreshDashboard() {
    // Add loading state
    const cards = document.querySelectorAll('.card');
    cards.forEach(card => {
        card.style.opacity = '0.7';
    });
    
    // Simulate data refresh
    setTimeout(() => {
        cards.forEach(card => {
            card.style.opacity = '1';
        });
        
        // Reinitialize charts with new data
        Chart.helpers.each(Chart.instances, function(instance) {
            instance.destroy();
        });
        initializeDashboardCharts();
    }, 1000);
}

/**
 * Update card statistics
 */
function updateCardStats(cardType, newValue) {
    const card = document.querySelector(`[data-card-type="${cardType}"]`);
    if (card) {
        const valueElement = card.querySelector('h1');
        if (valueElement) {
            // Animate the number change
            animateNumber(valueElement, parseInt(valueElement.textContent), newValue);
        }
    }
}

/**
 * Animate number changes
 */
function animateNumber(element, start, end) {
    const duration = 1000;
    const startTime = Date.now();
    
    const updateNumber = () => {
        const elapsed = Date.now() - startTime;
        const progress = Math.min(elapsed / duration, 1);
        
        const current = Math.floor(start + (end - start) * progress);
        element.textContent = current;
        
        if (progress < 1) {
            requestAnimationFrame(updateNumber);
        }
    };
    
    requestAnimationFrame(updateNumber);
}

// Make sure elements are visible on dashboard page
if (window.location.pathname.includes('dashboard.php')) {
    // Ensure visibility immediately
    setTimeout(function() {
        const cards = document.querySelectorAll('.card');
        const mainContent = document.querySelector('.main-content');
        
        if (mainContent) {
            mainContent.style.opacity = '1';
            mainContent.style.visibility = 'visible';
            mainContent.style.display = 'block';
        }
        
        cards.forEach(card => {
            card.style.opacity = '1';
            card.style.visibility = 'visible';
            card.style.display = 'block';
        });
    }, 100);
}

// Fallback: Force visibility after a short delay
setTimeout(function() {
    const cards = document.querySelectorAll('.card');
    const mainContent = document.querySelector('.main-content');
    
    if (mainContent) {
        mainContent.style.opacity = '1';
        mainContent.style.visibility = 'visible';
        mainContent.style.display = 'block';
    }
    
    cards.forEach(card => {
        card.style.opacity = '1';
        card.style.visibility = 'visible';
        card.style.display = 'block';
    });
}, 100);