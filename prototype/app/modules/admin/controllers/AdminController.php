<?php
/**
 * Admin Dashboard Controller
 * Handles all administrative functions
 */

require_once '../../config/role_permissions.php';

class AdminController {
    
    public function dashboard() {
        // Check if user has admin access
        if (!$this->hasPermission('dashboard', 'admin_dashboard')) {
            header('Location: ../../../index.php?error=access_denied');
            exit;
        }
        
        $data = [
            'total_users' => $this->getUserCount(),
            'total_applications' => $this->getApplicationCount(),
            'pending_approvals' => $this->getPendingCount(),
            'system_alerts' => $this->getSystemAlerts()
        ];
        
        include '../views/dashboard.php';
    }
    
    public function manageUsers() {
        if (!$this->hasPermission('users', 'read')) {
            $this->accessDenied();
        }
        
        $users = $this->getAllUsers();
        include '../views/manage_users.php';
    }
    
    public function systemSettings() {
        if (!$this->hasPermission('settings', 'read')) {
            $this->accessDenied();
        }
        
        include '../views/system_settings.php';
    }
    
    public function auditLogs() {
        if (!$this->hasPermission('audit_logs', 'read')) {
            $this->accessDenied();
        }
        
        $logs = $this->getAuditLogs();
        include '../views/audit_logs.php';
    }
    
    private function hasPermission($module, $action) {
        // Get user role from session
        $userRole = $_SESSION['user_role'] ?? null;
        return RolePermissions::hasPermission($userRole, $module, $action);
    }
    
    private function accessDenied() {
        header('HTTP/1.1 403 Forbidden');
        include '../../shared/views/access_denied.php';
        exit;
    }
    
    // Mock data methods (replace with actual database queries)
    private function getUserCount() { return 45; }
    private function getApplicationCount() { return 156; }
    private function getPendingCount() { return 23; }
    private function getSystemAlerts() { return []; }
    private function getAllUsers() { return []; }
    private function getAuditLogs() { return []; }
}
?>