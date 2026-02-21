// ===== CASE MANAGEMENT JAVASCRIPT =====

document.addEventListener('DOMContentLoaded', function() {
    // Initialize filter functionality
    initializeFilters();
    
    // Initialize search functionality
    initializeSearch();
    
    // Initialize table interactions
    initializeTable();
});

// Filter Functionality
function initializeFilters() {
    const applyFilterBtn = document.getElementById('applyFilter');
    const clearFilterBtn = document.getElementById('clearFilter');
    const searchInput = document.getElementById('searchInput');
    const dateFrom = document.getElementById('dateFrom');
    const dateTo = document.getElementById('dateTo');
    const statusFilter = document.getElementById('statusFilter');
    
    if (applyFilterBtn) {
        applyFilterBtn.addEventListener('click', function() {
            applyFilters();
        });
    }
    
    if (clearFilterBtn) {
        clearFilterBtn.addEventListener('click', function() {
            clearAllFilters();
        });
    }
    
    // Real-time search
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            filterTable();
        });
    }
    
    // Filter on status change
    if (statusFilter) {
        statusFilter.addEventListener('change', function() {
            filterTable();
        });
    }
}

// Search Functionality
function initializeSearch() {
    const searchInput = document.getElementById('searchInput');
    
    if (searchInput) {
        searchInput.addEventListener('keyup', function(e) {
            if (e.key === 'Enter') {
                applyFilters();
            }
        });
    }
}

// Table Interactions
function initializeTable() {
    // Add click handlers for action buttons
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('btn-outline-primary')) {
            // If the element is an anchor with an href, allow normal navigation
            if (e.target.tagName === 'A' && e.target.getAttribute('href')) {
                return;
            }
            const row = e.target.closest('tr');
            const refNo = row ? (row.querySelector('td:first-child') ? row.querySelector('td:first-child').textContent : '') : '';
            // If we have a reference and the element is not a regular anchor, navigate programmatically
            if (refNo) {
                // navigate to details page (safer than alert)
                window.location.href = `case_details.php?ref=${encodeURIComponent(refNo.trim())}`;
            }
        }
    });
}

// Apply Filters
function applyFilters() {
    const searchValue = document.getElementById('searchInput').value.toLowerCase();
    const dateFromValue = document.getElementById('dateFrom').value;
    const dateToValue = document.getElementById('dateTo').value;
    const statusValue = document.getElementById('statusFilter').value;
    
    filterTable(searchValue, dateFromValue, dateToValue, statusValue);
    
    // Show loading state
    showLoadingState();
    
    // Simulate API call
    setTimeout(() => {
        hideLoadingState();
        updateSummaryCards();
    }, 500);
}

// Clear All Filters
function clearAllFilters() {
    document.getElementById('searchInput').value = '';
    document.getElementById('dateFrom').value = '';
    document.getElementById('dateTo').value = '';
    document.getElementById('statusFilter').value = '';
    
    // Reset table
    filterTable();
    updateSummaryCards();
}

// Filter Table
function filterTable(search = '', dateFrom = '', dateTo = '', status = '') {
    const tbody = document.querySelector('.cases-table tbody');
    const rows = tbody.querySelectorAll('tr');
    
    rows.forEach(row => {
        let showRow = true;
        
        // Search filter
        if (search) {
            const rowText = row.textContent.toLowerCase();
            if (!rowText.includes(search)) {
                showRow = false;
            }
        }
        
        // Status filter
        if (status) {
            const statusBadge = row.querySelector('.badge-info');
            if (statusBadge && !statusBadge.textContent.toLowerCase().includes(status.replace('-', ' '))) {
                showRow = false;
            }
        }
        
        // Date filters (simplified - would need proper date parsing in real implementation)
        // This is a basic example
        
        row.style.display = showRow ? '' : 'none';
    });
}

// View Case Details
function viewCaseDetails(refNo) {
    // Navigate to the details page instead of showing an alert
    console.log('Viewing details for case:', refNo);
    if (refNo) {
        window.location.href = `case_details.php?ref=${encodeURIComponent(refNo.trim())}`;
    }
}

// Update Summary Cards
function updateSummaryCards() {
    const tbody = document.querySelector('.cases-table tbody');
    const visibleRows = tbody.querySelectorAll('tr:not([style*="display: none"])');
    
    // Count statuses from visible rows
    const statusCounts = {
        'under-investigation': 0,
        'for-filing': 0,
        'ongoing': 0,
        'dismissed': 0,
        'resolved': 0
    };
    
    visibleRows.forEach(row => {
        const statusBadge = row.querySelector('.badge-info');
        if (statusBadge) {
            const status = statusBadge.textContent.toLowerCase().replace(' ', '-');
            if (statusCounts.hasOwnProperty(status)) {
                statusCounts[status]++;
            }
        }
    });
    
    // Update summary cards
    const summaryCards = document.querySelectorAll('.summary-card');
    summaryCards.forEach((card, index) => {
        const countElement = card.querySelector('.summary-count');
        const titles = ['under-investigation', 'for-filing', 'ongoing', 'dismissed', 'resolved'];
        if (countElement && titles[index]) {
            countElement.textContent = statusCounts[titles[index]];
        }
    });
}

// Loading State
function showLoadingState() {
    const applyBtn = document.getElementById('applyFilter');
    if (applyBtn) {
        applyBtn.disabled = true;
        applyBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Loading...';
    }
}

function hideLoadingState() {
    const applyBtn = document.getElementById('applyFilter');
    if (applyBtn) {
        applyBtn.disabled = false;
        applyBtn.innerHTML = '<i class="fa fa-filter"></i> Apply';
    }
}

// Export functions for external use
window.CaseManagement = {
    applyFilters,
    clearAllFilters,
    viewCaseDetails,
    updateSummaryCards
};