-- CENRO NASIPIT Database Structure
-- Run this SQL to create the database and tables

CREATE DATABASE IF NOT EXISTS cenro_nasipit;
USE cenro_nasipit;

-- Users table for authentication and role management
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(255) NOT NULL,
    contact_number VARCHAR(20),
    office_unit VARCHAR(255),
    profile_picture VARCHAR(255),
    role ENUM('Admin', 'Enforcement Officer', 'Enforcer', 'Property Custodian', 'Office Staff') NOT NULL,
    status TINYINT(1) DEFAULT 1, -- 1 = active, 0 = disabled
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL
);

-- Applications table for permit applications
CREATE TABLE IF NOT EXISTS applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    applicant_name VARCHAR(255) NOT NULL,
    applicant_email VARCHAR(255),
    application_type VARCHAR(100) NOT NULL,
    status ENUM('Pending', 'Under Review', 'Approved', 'Rejected') DEFAULT 'Pending',
    submitted_by INT,
    reviewed_by INT NULL,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    reviewed_at TIMESTAMP NULL,
    remarks TEXT,
    FOREIGN KEY (submitted_by) REFERENCES users(id),
    FOREIGN KEY (reviewed_by) REFERENCES users(id)
);

-- Enforcement cases table
CREATE TABLE IF NOT EXISTS enforcement_cases (
    id INT AUTO_INCREMENT PRIMARY KEY,
    case_number VARCHAR(50) UNIQUE NOT NULL,
    case_title VARCHAR(255) NOT NULL,
    description TEXT,
    location VARCHAR(255),
    status ENUM('Open', 'Under Investigation', 'Resolved', 'Closed') DEFAULT 'Open',
    assigned_officer INT,
    assigned_enforcer INT NULL,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (assigned_officer) REFERENCES users(id),
    FOREIGN KEY (assigned_enforcer) REFERENCES users(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- Properties/Assets table for Property Custodian
CREATE TABLE IF NOT EXISTS properties (
    id INT AUTO_INCREMENT PRIMARY KEY,
    property_name VARCHAR(255) NOT NULL,
    property_type VARCHAR(100),
    location VARCHAR(255),
    description TEXT,
    status ENUM('Available', 'In Use', 'Under Maintenance', 'Disposed') DEFAULT 'Available',
    custodian_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (custodian_id) REFERENCES users(id)
);

-- Audit logs for system tracking
CREATE TABLE IF NOT EXISTS audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(255) NOT NULL,
    table_name VARCHAR(100),
    record_id INT,
    old_values JSON,
    new_values JSON,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);
-- NOTE: sample data removed. This SQL contains only schema definitions. Import your real data separately.