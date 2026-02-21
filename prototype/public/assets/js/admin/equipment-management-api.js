const API_BASE_URL = '../../../../app/api/equipment/equipment_api.php';

// Helper: read response text and parse JSON safely. Detect HTML responses.
async function _safeParseResponse(response) {
    const statusInfo = { status: response.status, statusText: response.statusText };
    let text;
    try {
        text = await response.text();
    } catch (err) {
        return { error: 'Failed to read response', ...statusInfo };
    }

    if (typeof text === 'string' && text.trim().startsWith('<')) {
        return { error: 'Server returned HTML instead of JSON (possible session timeout or server error)', sessionExpired: true, htmlSnippet: text.slice(0,200), ...statusInfo };
    }

    try {
        return JSON.parse(text);
    } catch (err) {
        return { error: 'Invalid JSON response', parseError: err.message, textSnippet: text.slice(0,200), ...statusInfo };
    }
}

// Load all equipment
function loadEquipment() {
    fetch(`${API_BASE_URL}?action=getAll`, { credentials: 'same-origin' })
        .then(response => _safeParseResponse(response))
        .then(data => {
            const tbody = document.querySelector('#equipmentTable tbody');
            tbody.innerHTML = '';
            
            // Accept either data.records or array directly
            const records = Array.isArray(data) ? data : (Array.isArray(data.records) ? data.records : []);
            if (records.length > 0) {
                records.forEach(equipment => {
                    const row = createEquipmentRow(equipment);
                    tbody.appendChild(row);
                });
                
                // Attach event listeners to view buttons
                attachViewDetailsListeners();
                attachEditListeners();
                attachDeleteListeners();
            } else {
                tbody.innerHTML = '<tr><td colspan="10" class="text-center">No equipment found</td></tr>';
            }
        })
        .catch(error => {
            console.error('Error loading equipment:', error);
            alert('Error loading equipment data');
        });
}

// Create equipment row
function createEquipmentRow(equipment) {
    const tr = document.createElement('tr');
    
    const statusClass = getStatusBadgeClass(equipment.status);
    // Determine QR code source: prefer stored qr_code_path, else fallback to generator
    let qrSrc = '';
    if (equipment.qr_code_path) {
        if (/^https?:\/\//i.test(equipment.qr_code_path)) {
            qrSrc = equipment.qr_code_path;
        } else if (equipment.qr_code_path.startsWith('/')) {
            qrSrc = equipment.qr_code_path;
        } else {
            qrSrc = '../../../../public/' + equipment.qr_code_path.replace(/^(\.\/|\/)/, '');
        }
    } else {
        qrSrc = `https://api.qrserver.com/v1/create-qr-code/?size=50x50&data=${encodeURIComponent(equipment.property_number || '')}`;
    }

    // Build QR cell: prefer stored QR image; if external generator used, show view button instead
    let qrCellHtml = '';
    try {
        if (qrSrc && qrSrc.toLowerCase().includes('api.qrserver.com')) {
            qrCellHtml = `<button class="btn btn-sm btn-outline-secondary view-details" data-id="${equipment.id}" title="View Details"><i class="fa fa-eye"></i></button>`;
        } else {
            qrCellHtml = `<button class="qr-img-btn view-details" data-id="${equipment.id}" title="View Details" style="border:none;background:transparent;padding:0;"><img src="${qrSrc}" alt="QR Code" class="qr-code-img" style="width: 40px; height: 40px;"></button>`;
        }
    } catch (e) {
        qrCellHtml = `<button class="btn btn-sm btn-outline-secondary view-details" data-id="${equipment.id}" title="View Details"><i class="fa fa-eye"></i></button>`;
    }

    tr.innerHTML = `
        <td>${equipment.id}</td>
        <td>${equipment.property_number}</td>
        <td>${equipment.equipment_type}</td>
        <td>${equipment.brand || '-'}</td>
        <td>${equipment.year_acquired || '-'}</td>
        <td>${equipment.actual_user || '-'}</td>
        <td>${equipment.accountable_person || '-'}</td>
        <td><span class="badge ${statusClass}">${equipment.status}</span></td>
        <td>
            <div class="qr-code-container text-center">
                ${qrCellHtml}
            </div>
        </td>
        <td>
            <div class="action-buttons">
                <button class="btn btn-sm btn-outline-primary view-details" data-id="${equipment.id}" title="View Details">
                    <i class="fa fa-eye"></i>
                </button>
                <button class="btn btn-sm btn-outline-success edit-equipment" data-id="${equipment.id}" title="Edit">
                    <i class="fa fa-edit"></i>
                </button>
                <button class="btn btn-sm btn-outline-danger delete-equipment" data-id="${equipment.id}" title="Delete">
                    <i class="fa fa-trash"></i>
                </button>
            </div>
        </td>
    `;
    
    return tr;
}

// Get status badge class
function getStatusBadgeClass(status) {
    const statusMap = {
        'Available': 'status-available',
        'In Use': 'status-in-use',
        'Under Maintenance': 'status-maintenance',
        'Damaged': 'status-damaged',
        'Out of Service': 'status-out-of-service'
    };
    return statusMap[status] || 'bg-secondary';
}

// Create equipment
function createEquipment(formData) {
    fetch(`${API_BASE_URL}?action=create`, {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(formData)
    })
    .then(response => _safeParseResponse(response))
    .then(data => {
        if (!data || data.error) {
            alert('Error creating equipment: ' + (data && data.error ? data.error : 'Unknown error'));
            return;
        }
        const message = data.message || data.msg || (data.success ? 'Equipment added successfully!' : '');
        if (message.toLowerCase().includes('success') || data.success) {
            alert('Equipment added successfully!');
            const modal = document.getElementById('addDeviceModal');
            if (modal) modal.style.display = 'none';
            const form = document.getElementById('addDeviceForm');
            if (form) form.reset();
            loadEquipment();
        } else {
            alert('Error: ' + (data.message || JSON.stringify(data)));
        }
    })
    .catch(error => {
        console.error('Error creating equipment:', error);
        alert('Error creating equipment');
    });
}

// View equipment details
function viewEquipmentDetails(id) {
    fetch(`${API_BASE_URL}?action=read_one&id=${encodeURIComponent(id)}`, { credentials: 'same-origin' })
        .then(response => _safeParseResponse(response))
        .then(equipment => {
            if (!equipment || equipment.error) {
                alert('Unable to load equipment details: ' + (equipment && equipment.error ? equipment.error : 'Unknown'));
                return;
            }
             // Populate modal with equipment data
            document.getElementById('detailAssetId').textContent = equipment.id;
            document.getElementById('detailPropertyNumber').textContent = equipment.property_number;
            document.getElementById('detailOfficeDevision').textContent = equipment.office_division || '-';
            document.getElementById('detailEquipmentType').textContent = equipment.equipment_type;
            document.getElementById('detailYearAcquired').textContent = equipment.year_acquired || '-';
            document.getElementById('detailShelfLife').textContent = equipment.shelf_life || '-';
            document.getElementById('detailBrand').textContent = equipment.brand || '-';
            document.getElementById('detailModel').textContent = equipment.model || '-';
            document.getElementById('detailProcessor').textContent = equipment.processor || '-';
            document.getElementById('detailRamSize').textContent = equipment.ram_size || '-';
            document.getElementById('detailGpu').textContent = equipment.gpu || '-';
            document.getElementById('detailRangeCategory').textContent = equipment.range_category || '-';
            document.getElementById('detailOsVersion').textContent = equipment.os_version || '-';
            document.getElementById('detailOfficeProductivity').textContent = equipment.office_productivity || '-';
            document.getElementById('detailEndpointProtection').textContent = equipment.endpoint_protection || '-';
            document.getElementById('detailComputerName').textContent = equipment.computer_name || '-';
            document.getElementById('detailSerialNumber').textContent = equipment.serial_number || '-';
            document.getElementById('detailAccountablePerson').textContent = equipment.accountable_person || '-';
            document.getElementById('detailAccountableSex').textContent = equipment.accountable_sex || '-';
            document.getElementById('detailAccountableEmployment').textContent = equipment.accountable_employment || '-';
            document.getElementById('detailActualUser').textContent = equipment.actual_user || '-';
            document.getElementById('detailActualUserSex').textContent = equipment.actual_user_sex || '-';
            document.getElementById('detailActualUserEmployment').textContent = equipment.actual_user_employment || '-';
            document.getElementById('detailNatureOfWork').textContent = equipment.nature_of_work || '-';
            document.getElementById('detailRemarks').textContent = equipment.remarks || '-';

            // Show the modal
            document.getElementById('equipmentDetailsModal').style.display = 'flex';
        })
        .catch(error => {
            console.error('Error loading equipment details:', error);
            alert('Error loading equipment details');
        });
}

// Delete equipment
function deleteEquipment(id) {
     if (confirm('Are you sure you want to delete this equipment?')) {
        fetch(`${API_BASE_URL}?action=delete`, {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id })
        })
        .then(response => _safeParseResponse(response))
        .then(data => {
            if (!data || data.error) {
                alert('Error deleting equipment: ' + (data && data.error ? data.error : 'Unknown'));
                return;
            }
            if ((data.message && data.message.toLowerCase().includes('success')) || data.success) {
                alert('Equipment deleted successfully!');
                loadEquipment();
            } else {
                alert('Error: ' + (data.message || JSON.stringify(data)));
            }
        })
        .catch(error => {
            console.error('Error deleting equipment:', error);
            alert('Error deleting equipment');
        });
    }
}

// Attach view details listeners
function attachViewDetailsListeners() {
    document.querySelectorAll('.view-details').forEach(button => {
        button.addEventListener('click', function() {
            const equipmentId = this.getAttribute('data-id');
            viewEquipmentDetails(equipmentId);
        });
    });
}

// Attach edit listeners
function attachEditListeners() {
    document.querySelectorAll('.edit-equipment').forEach(button => {
        button.addEventListener('click', function() {
            const equipmentId = this.getAttribute('data-id');
            // TODO: Implement edit functionality
            alert('Edit functionality for equipment ID: ' + equipmentId);
        });
    });
}

// Attach delete listeners
function attachDeleteListeners() {
    document.querySelectorAll('.delete-equipment').forEach(button => {
        button.addEventListener('click', function() {
            const equipmentId = this.getAttribute('data-id');
            deleteEquipment(equipmentId);
        });
    });
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Load equipment data
    loadEquipment();
    
    // Add equipment form submission
    document.getElementById('addDeviceBtn').addEventListener('click', function() {
        const form = document.getElementById('addDeviceForm');
        const formData = {
            office_division: form.officeDevision.value,
            equipment_type: form.equipmentType.value,
            year_acquired: form.yearAcquired.value,
            shelf_life: form.shelfLife.value,
            brand: form.brand.value,
            model: form.model.value,
            processor: form.processor.value,
            ram_size: form.ramSize.value,
            gpu: form.gpu.value,
            os_version: form.osVersion.value,
            office_productivity: form.officeProductivity.value,
            endpoint_protection: form.endpointProtection.value,
            computer_name: form.computerName.value,
            serial_number: form.serialNumber.value,
            property_number: form.propertyNumber.value,
            accountable_person: form.accountablePerson.value,
            accountable_sex: form.accountableSex.value,
            accountable_employment: form.accountableEmployment.value,
            actual_user: form.actualUser.value,
            actual_user_sex: form.actualUserSex.value,
            actual_user_employment: form.actualUserEmployment.value,
            nature_of_work: form.natureOfWork.value,
            status: 'Available',
            remarks: form.remarks.value
        };
        
        // Validate required fields
        if (!formData.property_number || !formData.equipment_type) {
            alert('Please fill in required fields: Property Number and Equipment Type');
            return;
        }
        
        createEquipment(formData);
    });
});
