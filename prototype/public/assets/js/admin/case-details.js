// Case Details JavaScript
document.addEventListener('DOMContentLoaded', function() {
    
    // File item click handlers
    const fileItems = document.querySelectorAll('.file-name');
    fileItems.forEach(item => {
        item.addEventListener('click', function() {
            // Simulate file opening/download
            const fileName = this.textContent;
            console.log('Opening file:', fileName);
            
            // You can add actual file opening logic here
            // For now, just show an alert
            alert('Opening file: ' + fileName);
        });
    });

    // Status badge hover effects
    const badges = document.querySelectorAll('.badge');
    badges.forEach(badge => {
        badge.addEventListener('mouseenter', function() {
            this.style.transform = 'scale(1.05)';
            this.style.transition = 'transform 0.2s ease';
        });
        
        badge.addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1)';
        });
    });

    // Print functionality
    function printCaseDetails() {
        window.print();
    }

    // Export functionality
    function exportCaseDetails() {
        // Implement export logic here
        alert('Export functionality to be implemented');
    }

    // Update case status functionality
    function updateCaseStatus() {
        // Implement update logic here
        alert('Update functionality to be implemented');
    }

    // Add keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // Ctrl+P for print
        if (e.ctrlKey && e.key === 'p') {
            e.preventDefault();
            printCaseDetails();
        }
        
        // Escape to go back
        if (e.key === 'Escape') {
            window.history.back();
        }
    });

    // Auto-resize tables on window resize
    window.addEventListener('resize', function() {
        const tables = document.querySelectorAll('.table-responsive');
        tables.forEach(table => {
            // Force reflow to handle responsive table layout
            table.style.display = 'none';
            table.offsetHeight; // Trigger reflow
            table.style.display = '';
        });
    });

    // Add smooth scrolling for long content
    const smoothScrollToTop = () => {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    };

    // Add scroll to top functionality
    let scrollToTopButton = document.createElement('button');
    scrollToTopButton.innerHTML = '<i class="fa fa-arrow-up"></i>';
    scrollToTopButton.className = 'btn btn-primary position-fixed';
    scrollToTopButton.style.cssText = `
        bottom: 20px;
        right: 20px;
        z-index: 1000;
        border-radius: 50%;
        width: 50px;
        height: 50px;
        display: none;
    `;
    scrollToTopButton.onclick = smoothScrollToTop;
    document.body.appendChild(scrollToTopButton);

    // Show/hide scroll to top button
    window.addEventListener('scroll', function() {
        if (window.pageYOffset > 300) {
            scrollToTopButton.style.display = 'block';
        } else {
            scrollToTopButton.style.display = 'none';
        }
    });

    console.log('Case Details page loaded successfully');
});