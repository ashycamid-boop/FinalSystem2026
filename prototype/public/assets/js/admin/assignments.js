// Assignments JavaScript
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const roleFilter = document.getElementById('roleFilter');
    const officeUnitFilter = document.getElementById('officeUnitFilter');
    const applyBtn = document.getElementById('applyBtn');
    const clearBtn = document.getElementById('clearBtn');
    const assignmentsTable = document.getElementById('assignmentsTable');
    const tableRows = assignmentsTable.querySelectorAll('tbody tr');

    // Search functionality
    function performSearch() {
        const searchTerm = searchInput.value.toLowerCase();
        const selectedRole = roleFilter.value.toLowerCase();
        const selectedOfficeUnit = officeUnitFilter.value.toLowerCase();
        
        tableRows.forEach(row => {
            const cells = row.querySelectorAll('td');
            const fullName = cells[1].textContent.toLowerCase();
            const email = cells[2].textContent.toLowerCase();
            const role = cells[3].textContent.toLowerCase();
            const officeUnit = cells[4].textContent.toLowerCase();
            
            const matchesSearch = searchTerm === '' || 
                                fullName.includes(searchTerm) || 
                                email.includes(searchTerm);
            
            const matchesRole = selectedRole === '' || role.includes(selectedRole);
            const matchesOfficeUnit = selectedOfficeUnit === '' || officeUnit.includes(selectedOfficeUnit);
            
            if (matchesSearch && matchesRole && matchesOfficeUnit) {
                row.style.display = '';
                // Add fade-in animation
                row.style.opacity = '0';
                setTimeout(() => {
                    row.style.opacity = '1';
                }, 50);
            } else {
                row.style.display = 'none';
            }
        });
    }

    // Search input event listener
    searchInput.addEventListener('input', performSearch);

    // Apply button event listener
    applyBtn.addEventListener('click', performSearch);

    // Clear button event listener
    clearBtn.addEventListener('click', function() {
        searchInput.value = '';
        roleFilter.value = '';
        officeUnitFilter.value = '';
        
        // Show all rows
        tableRows.forEach(row => {
            row.style.display = '';
            row.style.opacity = '1';
        });
    });

    // Filter change event listeners
    roleFilter.addEventListener('change', function() {
        if (this.value !== '') {
            performSearch();
        }
    });

    officeUnitFilter.addEventListener('change', function() {
        if (this.value !== '') {
            performSearch();
        }
    });

    // Details button handlers
    const detailsButtons = document.querySelectorAll('.btn-link');
    detailsButtons.forEach(button => {
        button.addEventListener('click', function() {
            const row = this.closest('tr');
            const userId = row.querySelector('td:first-child').textContent;
            const fullName = row.querySelector('td:nth-child(2)').textContent;
            
            // Here you would typically open a modal or navigate to a details page
            alert(`View details for ${fullName} (ID: ${userId})`);
        });
    });

    // Print button handlers
    const printButtons = document.querySelectorAll('.btn-outline-dark:not(:contains("Print All"))');
    printButtons.forEach(button => {
        button.addEventListener('click', function() {
            const row = this.closest('tr');
            const fullName = row.querySelector('td:nth-child(2)').textContent;
            
            // Here you would typically generate and print QR code
            alert(`Print QR code for ${fullName}`);
        });
    });

    // Print All QR Codes button
    const printAllBtn = document.querySelector('.btn-outline-dark');
    if (printAllBtn && printAllBtn.textContent.includes('Print All QR Codes')) {
        printAllBtn.addEventListener('click', function() {
            // Get visible rows count
            const visibleRows = Array.from(tableRows).filter(row => 
                row.style.display !== 'none'
            );
            
            if (visibleRows.length === 0) {
                alert('No users to print QR codes for');
                return;
            }
            
            alert(`Printing QR codes for ${visibleRows.length} users`);
        });
    }

    // Checkbox handlers
    const checkboxes = document.querySelectorAll('.form-check-input');
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const row = this.closest('tr');
            if (this.checked) {
                row.style.backgroundColor = '#e3f2fd';
            } else {
                row.style.backgroundColor = '';
            }
        });
    });

    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // Focus search on Ctrl+F
        if (e.ctrlKey && e.key === 'f') {
            e.preventDefault();
            searchInput.focus();
        }
        
        // Clear filters on Escape
        if (e.key === 'Escape') {
            clearBtn.click();
            searchInput.blur();
        }
        
        // Apply filters on Ctrl+Enter
        if (e.ctrlKey && e.key === 'Enter') {
            e.preventDefault();
            applyBtn.click();
        }
    });

    // Auto-resize table on window resize
    window.addEventListener('resize', function() {
        const table = document.querySelector('.table-responsive');
        // Force reflow to handle responsive table layout
        table.style.display = 'none';
        table.offsetHeight; // Trigger reflow
        table.style.display = '';
    });

    console.log('Assignments page loaded successfully');
    console.log('Keyboard shortcuts: Ctrl+F (search), Ctrl+Enter (apply), Escape (clear)');
});

// Print All QR Codes Function
function printAllQRCodes() {
    console.log('Printing all QR codes...');
    
    // Add printing class to body
    document.body.classList.add('printing-qr');
    
    // Show the QR code grid
    const qrGrid = document.getElementById('qrCodeGrid');
    if (qrGrid) {
        qrGrid.style.display = 'block';
    }
    
    // Print the page
    window.print();
    
    // Restore normal view after printing
    setTimeout(() => {
        document.body.classList.remove('printing-qr');
        if (qrGrid) {
            qrGrid.style.display = 'none';
        }
    }, 1000);
}