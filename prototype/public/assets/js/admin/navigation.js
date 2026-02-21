/**
 * Admin Navigation JavaScript
 * Handles sidebar navigation and dropdown functionality across all admin pages
 */

document.addEventListener('DOMContentLoaded', function() {
    console.log('=== NAVIGATION.JS LOADING ===');
    console.log('Current page:', window.location.pathname);
    initializeServiceDeskDropdown();
    fetchServiceDeskCounts();
    initializeProfileDropdown();
});

/**
 * Initialize Service Desk dropdown functionality
 */
function initializeServiceDeskDropdown() {
    console.log('=== INITIALIZING SERVICE DESK DROPDOWN ===');
    // Initialize all accordion dropdowns
    const accordionToggles = document.querySelectorAll('.sidebar-nav .dropdown-toggle');
    console.log('Found dropdown toggles:', accordionToggles.length);
    
    accordionToggles.forEach(toggle => {
        // Remove any existing event listeners by cloning the element
        const newToggle = toggle.cloneNode(true);
        toggle.parentNode.replaceChild(newToggle, toggle);
        
        const targetId = newToggle.getAttribute('data-target') || 
                        (newToggle.id === 'serviceDeskToggle' ? 'serviceDeskMenu' : null);
        
        if (!targetId) return;
        
        const menu = document.getElementById(targetId);
        if (!menu) return;
        
        // Check if dropdown should be open initially (if it has show class or active parent)
        let isOpen = menu.classList.contains('show') || newToggle.parentElement.classList.contains('active');
        
        // Set initial state based on existing classes
        if (isOpen) {
            const arrow = newToggle.querySelector('.dropdown-arrow');
            if (arrow) {
                arrow.classList.add('rotated');
            }
            newToggle.classList.add('active');
        }
        
        // Add click event to toggle accordion
        newToggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            // Close other open accordions
            accordionToggles.forEach(otherToggle => {
                if (otherToggle !== newToggle) {
                    const otherId = otherToggle.getAttribute('data-target') || 
                                   (otherToggle.id === 'serviceDeskToggle' ? 'serviceDeskMenu' : null);
                    if (otherId) {
                        const otherMenu = document.getElementById(otherId);
                        if (otherMenu && otherMenu.classList.contains('show')) {
                            otherMenu.classList.remove('show');
                            otherToggle.classList.remove('active');
                            const otherArrow = otherToggle.querySelector('.dropdown-arrow');
                            if (otherArrow) {
                                otherArrow.classList.remove('rotated');
                            }
                        }
                    }
                }
            });
            
            // Toggle current accordion state
            isOpen = !isOpen;
            
            if (isOpen) {
                // Opening accordion
                menu.classList.add('show');
                this.classList.add('active');
            } else {
                // Closing accordion
                menu.classList.remove('show');
                this.classList.remove('active');
            }
            
            // Toggle arrow rotation with smooth animation
            const arrow = this.querySelector('.dropdown-arrow');
            if (arrow) {
                if (isOpen) {
                    arrow.classList.add('rotated');
                } else {
                    arrow.classList.remove('rotated');
                }
            }
        });
        
        // Handle submenu item clicks
        const menuItems = menu.querySelectorAll('li a');
        menuItems.forEach(item => {
            item.addEventListener('click', function(e) {
                // Remove active state from all submenu items
                menuItems.forEach(menuItem => {
                    menuItem.parentElement.classList.remove('active');
                });
                
                // Add active state to clicked item
                this.parentElement.classList.add('active');
                
                // Keep the accordion open when clicking submenu items
                // Don't close the accordion here
            });
        });
        
        // Handle keyboard navigation
        newToggle.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                newToggle.click();
            }
            if (e.key === 'Escape') {
                isOpen = false;
                menu.classList.remove('show');
                newToggle.classList.remove('active');
                const arrow = newToggle.querySelector('.dropdown-arrow');
                if (arrow) {
                    arrow.classList.remove('rotated');
                }
            }
        });
    });
}

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
        if (dropdownOpen) {
            profileDropdown.classList.add('show');
        } else {
            profileDropdown.classList.remove('show');
        }
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
            profileDropdown.classList.remove('show');
        }
    });

    // Close dropdown on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && dropdownOpen) {
            dropdownOpen = false;
            profileDropdown.classList.remove('show');
        }
    });
}

/**
 * Highlight active navigation item
 */
function setActiveNavigation(currentPage) {
    const navItems = document.querySelectorAll('.sidebar-nav li');
    
    navItems.forEach(item => {
        item.classList.remove('active');
        
        const link = item.querySelector('a');
        if (link && link.getAttribute('href') === currentPage) {
            item.classList.add('active');
        }
    });
}

/**
 * Fetch counts for service desk badges and update sidebar
 */
function fetchServiceDeskCounts() {
    try {
        fetch('/prototype/app/api/service_counts.php')
            .then(resp => resp.json())
            .then(data => {
                if (!data) return;
                // Update New Requests badge
                const newBadge = document.querySelector('a[href="new_requests.php"] .badge');
                if (newBadge && typeof data.new_requests !== 'undefined') {
                    newBadge.textContent = data.new_requests;
                }

                // Update Ongoing / Scheduled badge (may have badge-blue class)
                const ongoingBadge = document.querySelector('a[href="ongoing_scheduled.php"] .badge');
                if (ongoingBadge && typeof data.ongoing_scheduled !== 'undefined') {
                    ongoingBadge.textContent = data.ongoing_scheduled;
                }

                // Update Completed badge if present
                const completedBadge = document.querySelector('a[href="completed.php"] .badge');
                if (completedBadge && typeof data.completed !== 'undefined') {
                    completedBadge.textContent = data.completed;
                }
            })
            .catch(err => console.warn('service counts fetch failed', err));
    } catch (e) {
        console.warn('fetchServiceDeskCounts error', e);
    }
}