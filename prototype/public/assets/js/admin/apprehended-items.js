// Apprehended Items JavaScript
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const filterButtons = document.querySelectorAll('.btn-filter');
    const itemsTable = document.getElementById('itemsTable');
    const tableRows = itemsTable.querySelectorAll('tbody tr');

    // Search functionality
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        
        // Add searching class for loading animation
        if (searchTerm.length > 0) {
            document.querySelector('.search-box').classList.add('searching');
            setTimeout(() => {
                document.querySelector('.search-box').classList.remove('searching');
            }, 500);
        }
        
        filterTable(searchTerm, getActiveFilter());
    });

    // Filter functionality
    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Remove active class from all buttons
            filterButtons.forEach(btn => btn.classList.remove('active'));
            // Add active class to clicked button
            this.classList.add('active');
            
            const filter = this.getAttribute('data-filter');
            const searchTerm = searchInput.value.toLowerCase();
            
            filterTable(searchTerm, filter);
        });
    });

    // Get active filter
    function getActiveFilter() {
        const activeButton = document.querySelector('.btn-filter.active');
        return activeButton ? activeButton.getAttribute('data-filter') : 'all';
    }

    // Filter table function
    function filterTable(searchTerm, filter) {
        let visibleRows = 0;
        
        tableRows.forEach(row => {
            const text = row.textContent.toLowerCase();
            const type = row.getAttribute('data-type');
            
            // Check if row matches search and filter
            const matchesSearch = searchTerm === '' || text.includes(searchTerm);
            const matchesFilter = filter === 'all' || type === filter;
            
            if (matchesSearch && matchesFilter) {
                row.style.display = '';
                visibleRows++;
                // Add fade-in animation
                row.style.opacity = '0';
                setTimeout(() => {
                    row.style.opacity = '1';
                }, 50);
            } else {
                row.style.display = 'none';
            }
        });

        // Show empty state if no rows visible
        showEmptyState(visibleRows === 0);
    }

    // Show/hide empty state
    function showEmptyState(show) {
        let emptyState = document.querySelector('.empty-state');
        
        if (show && !emptyState) {
            emptyState = document.createElement('tr');
            emptyState.className = 'empty-state';
            emptyState.innerHTML = `
                <td colspan="8" class="empty-state">
                    <i class="fa fa-search"></i>
                    <h5>No items found</h5>
                    <p>Try adjusting your search or filter criteria</p>
                </td>
            `;
            itemsTable.querySelector('tbody').appendChild(emptyState);
        } else if (!show && emptyState) {
            emptyState.remove();
        }
    }

    // Table row hover effects
    tableRows.forEach(row => {
        row.addEventListener('mouseenter', function() {
            this.style.transform = 'translateX(5px)';
            this.style.transition = 'transform 0.2s ease';
        });
        
        row.addEventListener('mouseleave', function() {
            this.style.transform = 'translateX(0)';
        });
    });

    // Status badge hover effects
    const statusBadges = document.querySelectorAll('.badge');
    statusBadges.forEach(badge => {
        badge.addEventListener('mouseenter', function() {
            this.style.transform = 'scale(1.05)';
            this.style.transition = 'transform 0.2s ease';
        });
        
        badge.addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1)';
        });
    });

    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // Focus search on Ctrl+F
        if (e.ctrlKey && e.key === 'f') {
            e.preventDefault();
            searchInput.focus();
        }
        
        // Clear search on Escape
        if (e.key === 'Escape') {
            searchInput.value = '';
            filterTable('', getActiveFilter());
            searchInput.blur();
        }
        
        // Filter shortcuts (Alt + Number)
        if (e.altKey && e.key >= '1' && e.key <= '4') {
            e.preventDefault();
            const index = parseInt(e.key) - 1;
            if (filterButtons[index]) {
                filterButtons[index].click();
            }
        }
    });

    // Auto-refresh functionality (optional)
    function autoRefresh() {
        // Simulate data refresh
        console.log('Auto-refreshing apprehended items data...');
        
        // Add loading state
        tableRows.forEach(row => {
            row.classList.add('loading');
        });
        
        // Remove loading state after 1 second
        setTimeout(() => {
            tableRows.forEach(row => {
                row.classList.remove('loading');
            });
        }, 1000);
    }

    // Set up auto-refresh every 5 minutes (optional)
    // setInterval(autoRefresh, 300000);

    // Export functionality
    function exportData() {
        const visibleRows = Array.from(tableRows).filter(row => 
            row.style.display !== 'none'
        );
        
        if (visibleRows.length === 0) {
            alert('No data to export');
            return;
        }
        
        // Prepare CSV data
        let csvContent = "Reference No,Item Type,Description,Quantity,Volume,Evidence,Status,Last Updated\n";
        
        visibleRows.forEach(row => {
            const cells = row.querySelectorAll('td');
            const rowData = Array.from(cells).map(cell => {
                // Clean up text content (remove badge HTML)
                return cell.textContent.trim().replace(/,/g, ';');
            });
            csvContent += rowData.join(',') + '\n';
        });
        
        // Download CSV
        const blob = new Blob([csvContent], { type: 'text/csv' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'apprehended_items_' + new Date().toISOString().split('T')[0] + '.csv';
        a.click();
        window.URL.revokeObjectURL(url);
    }

    // Add export button if needed
    window.exportApprehendedItems = exportData;

    console.log('Apprehended Items page loaded successfully');
    console.log('Keyboard shortcuts: Ctrl+F (search), Escape (clear), Alt+1-4 (filters)');
});