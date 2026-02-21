// Equipment Management JavaScript
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const equipmentTable = document.getElementById('equipmentTable');
    if (!searchInput || !equipmentTable) {
        console.warn('Required elements missing: searchInput or equipmentTable. Aborting equipment-management.js bindings.');
        return;
    }

    // Search functionality
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const tableRows = equipmentTable.querySelectorAll('tbody tr');
        tableRows.forEach(row => {
            const text = row.textContent.toLowerCase();
            if (searchTerm === '' || text.includes(searchTerm)) {
                row.style.display = '';
                row.style.opacity = '0';
                setTimeout(() => { row.style.opacity = '1'; }, 50);
            } else {
                row.style.display = 'none';
            }
        });
    });

    // QR Code functionality (guard)
    const qrCodeIcons = document.querySelectorAll('.qr-code-icon');
    qrCodeIcons.forEach(icon => {
        icon.addEventListener('click', function() {
            const row = this.closest('tr');
            const assetId = row.querySelector('td:first-child').textContent;
            const propertyNo = row.querySelector('td:nth-child(2)').textContent;
            
            // Show QR code modal or generate QR code
            showQRCode(assetId, propertyNo);
        });
    });

    // Modal elements
    const modal = document.getElementById('equipmentDetailsModal');
    const closeModalBtn = document.getElementById('closeModal');

    const modalOverlay = document.createElement('div');
    modalOverlay.className = 'modal-overlay';
    document.body.appendChild(modalOverlay);

    // Action button handlers (use specific classes)
    const viewButtons = document.querySelectorAll('.view-details') || [];
    const editButtons = document.querySelectorAll('.edit-equipment') || [];
    const deleteButtons = document.querySelectorAll('.delete-equipment') || [];

    viewButtons.forEach(button => {
        button.addEventListener('click', function() {
            const row = this.closest('tr');
            showEquipmentDetails(row);
        });
    });

    editButtons.forEach(button => {
        button.addEventListener('click', function() {
            const row = this.closest('tr');
            const assetId = row && row.querySelector('td:first-child') ? row.querySelector('td:first-child').textContent.trim() : '';
            editEquipment(assetId);
        });
    });

    deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
            const row = this.closest('tr');
            const assetId = row && row.querySelector('td:first-child') ? row.querySelector('td:first-child').textContent.trim() : '';
            const propertyNo = row && row.querySelector('td:nth-child(2)') ? row.querySelector('td:nth-child(2)').textContent.trim() : '';
            deleteEquipment(assetId, propertyNo);
        });
    });

    // Print All QR Codes functionality (guard)
    const printQRButton = document.querySelector('.btn-outline-dark');
    if (printQRButton) printQRButton.addEventListener('click', function() { printAllQRCodes(); });

    // Add Device functionality (guard)
    const addDeviceButton = document.querySelector('.btn-primary');
    if (addDeviceButton) addDeviceButton.addEventListener('click', function() { showAddDeviceModal(); });

    // Modal close handlers
    if (closeModalBtn) closeModalBtn.addEventListener('click', hideEquipmentDetails);

    modalOverlay.addEventListener('click', hideEquipmentDetails);

    // Functions
    function showEquipmentDetails(row) {
        const cells = row.querySelectorAll('td');
        
        // Populate modal with data from table row -> map to the detail* IDs used in PHP template
        const setIfExists = (id, text) => { const el = document.getElementById(id); if (el) el.textContent = text; };
        setIfExists('detailAssetId', cells[0] ? cells[0].textContent.trim() : '');
        setIfExists('detailPropertyNumber', cells[1] ? cells[1].textContent.trim() : '');
        setIfExists('detailEquipmentType', cells[2] ? cells[2].textContent.trim() : '');
        setIfExists('detailBrand', cells[3] ? cells[3].textContent.trim() : '');
        setIfExists('detailYearAcquired', cells[4] ? cells[4].textContent.trim() : '');
        setIfExists('detailActualUser', cells[5] ? cells[5].textContent.trim() : '');
        setIfExists('detailAccountablePerson', cells[6] ? cells[6].textContent.trim() : '');
        setIfExists('detailRemarks', '');
         
        // Show modal
        if (modal) modal.style.display = 'flex';
        modalOverlay.classList.add('show');
        document.body.style.overflow = 'hidden';
    }

    function hideEquipmentDetails() {
        if (modal) modal.style.display = 'none';
        modalOverlay.classList.remove('show');
        document.body.style.overflow = '';
    }

    function showQRCode(assetId, propertyNo) {
        // Generate and show QR code for specific equipment
        alert(`Showing QR Code for Asset ID: ${assetId} (${propertyNo})`);
        // You can implement actual QR code generation here
    }

    function editEquipment(assetId) {
        // Redirect to equipment edit page
        console.log('Editing equipment:', assetId);
        // window.location.href = `edit_equipment.php?id=${assetId}`;
    }

    function deleteEquipment(assetId, propertyNo) {
        if (confirm(`Are you sure you want to delete equipment ${propertyNo}?`)) {
            // Perform delete operation
            console.log('Deleting equipment:', assetId);
            // You can implement actual delete functionality here
        }
    }

    function printAllQRCodes() {
        // Generate and print all QR codes
        console.log('Printing all QR codes...');
        alert('Generating QR codes for all equipment...');
        // You can implement actual QR code generation and printing here
    }

    function showAddDeviceModal() {
        // Show add device modal or redirect to add page
        const addDeviceModalEl = document.getElementById('addDeviceModal');
        if (addDeviceModalEl) addDeviceModalEl.style.display = 'flex';
        modalOverlay.classList.add('show');
    }

    // Table row hover effects
    // attach to current rows (may be dynamic)
    const currentRows = equipmentTable.querySelectorAll('tbody tr');
    currentRows.forEach(row => {
         row.addEventListener('mouseenter', function() {
             this.style.transform = 'translateX(3px)';
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
        
        // Clear search on Escape or close modal
        if (e.key === 'Escape') {
            if (modal.classList.contains('show')) {
                hideEquipmentDetails();
            } else {
                searchInput.value = '';
                searchInput.dispatchEvent(new Event('input'));
                searchInput.blur();
            }
        }
        
        // Print QR codes on Ctrl+P
        if (e.ctrlKey && e.key === 'p') {
            e.preventDefault();
            printAllQRCodes();
        }
        
        // Add new device on Ctrl+N
        if (e.ctrlKey && e.key === 'n') {
            e.preventDefault();
            showAddDeviceModal();
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

    // Export functionality (bonus feature)
    function exportEquipmentData() {
        const rows = Array.from(equipmentTable.querySelectorAll('tbody tr')).filter(row => row.style.display !== 'none');

        if (rows.length === 0) {
            alert('No data to export');
            return;
        }

        let csvContent = "Asset ID,Property No,Category,Brand,Model,Serial Number,Date Acquired,Assigned To,Original Owner,Department,Status\n";

        rows.forEach(row => {
            const cells = row.querySelectorAll('td');
            const rowData = [];

            // Skip QR Code and Actions columns (assume last two columns are QR and Actions)
            for (let i = 0; i < cells.length - 2; i++) {
                rowData.push(cells[i].textContent.trim().replace(/,/g, ';'));
            }
            csvContent += rowData.join(',') + '\n';
        });

        // Download CSV
        const blob = new Blob([csvContent], { type: 'text/csv' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'equipment_inventory_' + new Date().toISOString().split('T')[0] + '.csv';
        a.click();
        window.URL.revokeObjectURL(url);
    }

    // Add Device Modal functionality
    const addDeviceModal = document.getElementById('addDeviceModal');
    const addDeviceForm = document.getElementById('addDeviceForm');
    const addDeviceBtn = document.getElementById('addDeviceBtn');
    const closeAddDeviceModalBtn = document.getElementById('closeAddDeviceModal');
    const addNewDeviceBtn = document.getElementById('addNewDeviceBtn');
     
     // Show Add Device Modal function
     function showAddDeviceModal() {
        const addDeviceModalEl = document.getElementById('addDeviceModal');
        if (addDeviceModalEl) addDeviceModalEl.style.display = 'flex';
        modalOverlay.classList.add('show');
     }
     
     // Hide Add Device Modal function
     function hideAddDeviceModal() {
        const addDeviceModalEl = document.getElementById('addDeviceModal');
        if (addDeviceModalEl) addDeviceModalEl.style.display = 'none';
        modalOverlay.classList.remove('show');
        if (addDeviceForm) addDeviceForm.reset();
     }
     
     // Add New Device button click handler
     if (addNewDeviceBtn) {
         addNewDeviceBtn.addEventListener('click', function() {
             showAddDeviceModal();
         });
     }
     
     // Close modal button handler
     if (closeAddDeviceModalBtn) {
         closeAddDeviceModalBtn.addEventListener('click', hideAddDeviceModal);
     }
     
     // Handle Add Device form submission
     if (addDeviceBtn) {
         addDeviceBtn.addEventListener('click', function() {
             const formData = new FormData(addDeviceForm || document.createElement('form'));
             
             // Get form values
             const propertyNo = formData.get('propertyNumber') || formData.get('propertyNo');
             const category = formData.get('equipmentType') || formData.get('category');
             const brand = formData.get('brand');
             const model = formData.get('model');
             const serialNo = formData.get('serialNumber') || formData.get('serialNo');
             const dateAcquired = formData.get('yearAcquired') || formData.get('dateAcquired');
             const assignedTo = formData.get('actualUser') || formData.get('assignedTo');
             const department = formData.get('officeDevision') || formData.get('department');
             const originalOwner = formData.get('originalOwner');
             
             // Basic validation
             if (!propertyNo || !category || !brand || !model) {
                 alert('Please fill in all required fields (Property No., Category, Brand, Model)');
                 return;
             }
             
             // Here you would typically send the data to your backend
             // For now, we'll just show a success message
             alert('Device added successfully!');
             
             // Reset form and close modal
             hideAddDeviceModal();
             
             // Optionally refresh the equipment table
             // window.location.reload();
         });
     }
     
     // Close modal when clicking overlay
    modalOverlay.addEventListener('click', function() {
        hideEquipmentDetails();
        hideAddDeviceModal();
    });

    // Add export function to window for external access
    window.exportEquipmentData = exportEquipmentData;
    window.showAddDeviceModal = showAddDeviceModal;

    console.log('Equipment Management page loaded successfully');
    console.log('Keyboard shortcuts: Ctrl+F (search), Ctrl+P (print QR), Ctrl+N (add device), Escape (clear)');
});