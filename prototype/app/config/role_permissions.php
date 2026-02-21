<?php
/**
 * Role-Based Access Control (RBAC) Configuration
 * Defines permissions and access levels for each role
 */

class RolePermissions {
    
    // Define role constants
    const ADMIN = 'admin';
    const ENFORCEMENT_OFFICER = 'enforcement_officer';
    const ENFORCER = 'enforcer';
    const PROPERTY_CUSTODIAN = 'property_custodian';
    const OFFICE_STAFF = 'office_staff';
    
    // Role hierarchy (higher number = more permissions)
    private static $roleHierarchy = [
        self::ADMIN => 5,
        self::ENFORCEMENT_OFFICER => 4,
        self::ENFORCER => 3,
        self::PROPERTY_CUSTODIAN => 2,
        self::OFFICE_STAFF => 1
    ];
    
    // Define permissions for each role
    private static $rolePermissions = [
        
        // ADMIN - Full system access
        self::ADMIN => [
            'users' => ['create', 'read', 'update', 'delete'],
            'applications' => ['create', 'read', 'update', 'delete', 'approve', 'reject'],
            'reports' => ['create', 'read', 'update', 'delete', 'generate'],
            'settings' => ['create', 'read', 'update', 'delete'],
            'audit_logs' => ['read'],
            'system_management' => ['full_access'],
            'dashboard' => ['admin_dashboard']
        ],
        
        // ENFORCEMENT OFFICER - Manage enforcement and applications
        self::ENFORCEMENT_OFFICER => [
            'applications' => ['create', 'read', 'update', 'approve', 'reject'],
            'enforcement' => ['create', 'read', 'update', 'manage_cases'],
            'reports' => ['create', 'read', 'generate'],
            'users' => ['read', 'update_profile'],
            'dashboard' => ['officer_dashboard']
        ],
        
        // ENFORCER - Field enforcement activities
        self::ENFORCER => [
            'enforcement' => ['create', 'read', 'update', 'field_reports'],
            'applications' => ['read', 'update_status'],
            'reports' => ['create', 'read'],
            'users' => ['read', 'update_profile'],
            'dashboard' => ['enforcer_dashboard']
        ],
        
        // PROPERTY CUSTODIAN - Property and asset management
        self::PROPERTY_CUSTODIAN => [
            'properties' => ['create', 'read', 'update', 'manage'],
            'applications' => ['read', 'update_property_status'],
            'reports' => ['create', 'read', 'property_reports'],
            'users' => ['read', 'update_profile'],
            'dashboard' => ['custodian_dashboard']
        ],
        
        // OFFICE STAFF - Basic data entry and processing
        self::OFFICE_STAFF => [
            'applications' => ['create', 'read', 'update'],
            'reports' => ['read', 'basic_reports'],
            'users' => ['read', 'update_profile'],
            'dashboard' => ['staff_dashboard']
        ]
    ];
    
    /**
     * Check if user has permission for specific action
     */
    public static function hasPermission($userRole, $module, $action) {
        if (!isset(self::$rolePermissions[$userRole])) {
            return false;
        }
        
        if (!isset(self::$rolePermissions[$userRole][$module])) {
            return false;
        }
        
        return in_array($action, self::$rolePermissions[$userRole][$module]);
    }
    
    /**
     * Get all permissions for a role
     */
    public static function getRolePermissions($role) {
        return self::$rolePermissions[$role] ?? [];
    }
    
    /**
     * Check if role has higher or equal access level
     */
    public static function hasAccessLevel($userRole, $requiredRole) {
        $userLevel = self::$roleHierarchy[$userRole] ?? 0;
        $requiredLevel = self::$roleHierarchy[$requiredRole] ?? 0;
        
        return $userLevel >= $requiredLevel;
    }
    
    /**
     * Get role display name
     */
    public static function getRoleDisplayName($role) {
        $displayNames = [
            self::ADMIN => 'Administrator',
            self::ENFORCEMENT_OFFICER => 'Enforcement Officer',
            self::ENFORCER => 'Enforcer',
            self::PROPERTY_CUSTODIAN => 'Property Custodian',
            self::OFFICE_STAFF => 'Office Staff'
        ];
        
        return $displayNames[$role] ?? 'Unknown Role';
    }
    
    /**
     * Get dashboard URL for role
     */
    public static function getDashboardUrl($role) {
        $dashboards = [
            self::ADMIN => 'app/modules/admin/views/dashboard.php',
            self::ENFORCEMENT_OFFICER => 'app/modules/enforcement_officer/views/dashboard.php',
            self::ENFORCER => 'app/modules/enforcer/views/dashboard.php',
            self::PROPERTY_CUSTODIAN => 'app/modules/property_custodian/views/dashboard.php',
            self::OFFICE_STAFF => 'app/modules/office_staff/views/dashboard.php'
        ];
        
        return $dashboards[$role] ?? 'dashboard.php';
    }
}
?>