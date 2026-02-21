const EquipmentService = {
    apiUrl: '../../../../app/api/equipment/equipment_api.php',

    // Helper: read response as text and try to parse JSON safely.
    async _safeParseResponse(response) {
        const statusInfo = { status: response.status, statusText: response.statusText };
        let text;
        try {
            text = await response.text();
        } catch (err) {
            return { error: 'Failed to read response text', ...statusInfo };
        }

        // If response body looks like HTML (login page, error page), return an error object
        if (typeof text === 'string' && text.trim().startsWith('<')) {
            return {
                error: 'Server returned HTML instead of JSON (possible session timeout or server error).',
                sessionExpired: true,
                htmlSnippet: text.slice(0, 200),
                ...statusInfo
            };
        }

        // Try parse JSON
        try {
            const parsed = JSON.parse(text);
            return parsed;
        } catch (err) {
            return { error: 'Invalid JSON response from server', parseError: err.message, textSnippet: text.slice(0, 200), ...statusInfo };
        }
    },

    // Get all equipment
    async getAll(search = '', status = 'All') {
        try {
            const response = await fetch(`${this.apiUrl}?action=getAll&search=${encodeURIComponent(search)}&status=${encodeURIComponent(status)}`, { credentials: 'same-origin' });
            return await this._safeParseResponse(response);
        } catch (error) {
            console.error('Error fetching equipment:', error);
            return { error: error.message || 'Network error' };
        }
    },

    // Get equipment by ID
    async getById(id) {
        try {
            const response = await fetch(`${this.apiUrl}?action=getById&id=${encodeURIComponent(id)}`, { credentials: 'same-origin' });
            return await this._safeParseResponse(response);
        } catch (error) {
            console.error('Error fetching equipment:', error);
            return { error: error.message || 'Network error' };
        }
    },

    // Get all users
    async getUsers() {
        try {
            const response = await fetch(`${this.apiUrl}?action=getUsers`, { credentials: 'same-origin' });
            return await this._safeParseResponse(response);
        } catch (error) {
            console.error('Error fetching users:', error);
            return { error: error.message || 'Network error' };
        }
    },

    // Create new equipment
    async create(data) {
        try {
            const response = await fetch(`${this.apiUrl}?action=create`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            });
            return await this._safeParseResponse(response);
        } catch (error) {
            console.error('Error creating equipment:', error);
            return { error: error.message || 'Network error' };
        }
    },

    // Update equipment
    async update(id, data) {
        try {
            const response = await fetch(`${this.apiUrl}?action=update`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ ...data, id })
            });
            return await this._safeParseResponse(response);
        } catch (error) {
            console.error('Error updating equipment:', error);
            return { error: error.message || 'Network error' };
        }
    },

    // Delete equipment
    async delete(id) {
        try {
            const response = await fetch(`${this.apiUrl}?action=delete`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ id })
            });
            return await this._safeParseResponse(response);
        } catch (error) {
            console.error('Error deleting equipment:', error);
            return { error: error.message || 'Network error' };
        }
    },

    // Generate QR for equipment (on-demand)
    async generateQR(id) {
        try {
            const response = await fetch(`${this.apiUrl}?action=generateQR&id=${encodeURIComponent(id)}`, {
                method: 'GET',
                credentials: 'same-origin'
            });
            return await this._safeParseResponse(response);
        } catch (error) {
            console.error('Error generating QR:', error);
            return { error: error.message || 'Network error' };
        }
    }
};
