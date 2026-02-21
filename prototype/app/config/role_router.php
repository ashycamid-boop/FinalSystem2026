<?php
/**
 * Role-Based Router
 * Routes users to appropriate modules based on their role
 */

session_start();
require_once 'role_permissions.php';

class RoleRouter {
    
    /**
     * Route user to appropriate dashboard based on their role
     */
    public static function routeToDashboard() {
        if (!isset($_SESSION['user_role'])) {
            header('Location: ../../index.php?error=not_logged_in');
            exit;
        }
        
        $role = $_SESSION['user_role'];
        $dashboardUrl = RolePermissions::getDashboardUrl($role);
        
        header("Location: $dashboardUrl");
        exit;
    }
    
    /**
     * Check if user can access a specific module
     */
    public static function checkModuleAccess($module, $action = 'read') {
        if (!isset($_SESSION['user_role'])) {
            return false;
        }
        
        return RolePermissions::hasPermission($_SESSION['user_role'], $module, $action);
    }
    
    /**
     * Middleware to protect routes
     */
    public static function requirePermission($module, $action = 'read') {
        if (!self::checkModuleAccess($module, $action)) {
            header('HTTP/1.1 403 Forbidden');
            include '../shared/views/access_denied.php';
            exit;
        }
    }
    
    /**
     * Get navigation menu items based on user role
     */
    public static function getNavigationMenu($userRole) {
        $menus = [
            RolePermissions::ADMIN => [
                ['name' => 'Dashboard', 'url' => 'app/modules/admin/views/dashboard.php', 'icon' => 'bi-speedometer2'],
                ['name' => 'User Management', 'url' => 'app/modules/admin/views/manage_users.php', 'icon' => 'bi-people'],
                ['name' => 'System Settings', 'url' => 'app/modules/admin/views/system_settings.php', 'icon' => 'bi-gear'],
                ['name' => 'Audit Logs', 'url' => 'app/modules/admin/views/audit_logs.php', 'icon' => 'bi-journal-text'],
                ['name' => 'Reports', 'url' => 'app/modules/admin/views/reports.php', 'icon' => 'bi-bar-chart']
            ],
            
            RolePermissions::ENFORCEMENT_OFFICER => [
                ['name' => 'Dashboard', 'url' => 'app/modules/enforcement_officer/views/dashboard.php', 'icon' => 'bi-speedometer2'],
                ['name' => 'Review Applications', 'url' => 'app/modules/enforcement_officer/views/review_applications.php', 'icon' => 'bi-file-earmark-check'],
                ['name' => 'Enforcement Cases', 'url' => 'app/modules/enforcement_officer/views/manage_enforcement.php', 'icon' => 'bi-shield-exclamation'],
                ['name' => 'Reports', 'url' => 'app/modules/enforcement_officer/views/reports.php', 'icon' => 'bi-bar-chart']
            ],
            
            RolePermissions::ENFORCER => [
                ['name' => 'Dashboard', 'url' => 'app/modules/enforcer/views/dashboard.php', 'icon' => 'bi-speedometer2'],
                ['name' => 'Field Reports', 'url' => 'app/modules/enforcer/views/field_reports.php', 'icon' => 'bi-journal-plus'],
                ['name' => 'Assigned Cases', 'url' => 'app/modules/enforcer/views/assigned_cases.php', 'icon' => 'bi-clipboard-check'],
                ['name' => 'Submit Report', 'url' => 'app/modules/enforcer/views/submit_report.php', 'icon' => 'bi-upload']
            ],
            
            RolePermissions::PROPERTY_CUSTODIAN => [
                ['name' => 'Dashboard', 'url' => 'app/modules/property_custodian/views/dashboard.php', 'icon' => 'bi-speedometer2'],
                ['name' => 'Property Management', 'url' => 'app/modules/property_custodian/views/manage_properties.php', 'icon' => 'bi-building'],
                ['name' => 'Asset Tracking', 'url' => 'app/modules/property_custodian/views/asset_tracking.php', 'icon' => 'bi-box-seam'],
                ['name' => 'Property Reports', 'url' => 'app/modules/property_custodian/views/property_reports.php', 'icon' => 'bi-bar-chart']
            ],
            
            RolePermissions::OFFICE_STAFF => [
                ['name' => 'Dashboard', 'url' => 'app/modules/office_staff/views/dashboard.php', 'icon' => 'bi-speedometer2'],
                ['name' => 'Data Entry', 'url' => 'app/modules/office_staff/views/data_entry.php', 'icon' => 'bi-keyboard'],
                ['name' => 'View Applications', 'url' => 'app/modules/office_staff/views/view_applications.php', 'icon' => 'bi-file-earmark-text'],
                ['name' => 'Basic Reports', 'url' => 'app/modules/office_staff/views/basic_reports.php', 'icon' => 'bi-bar-chart']
            ]
        ];
        
        return $menus[$userRole] ?? [];
    }
}
?>