-- This script completely rebuilds the database for the OpenOffice API.
-- WARNING: This will delete all existing data.

-- Step 1: Drop existing tables in the correct order to avoid foreign key issues.
DROP TABLE IF EXISTS `role_permissions`;
DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `permissions`;
DROP TABLE IF EXISTS `roles`;

-- Step 2: Recreate tables from the schema.

-- Table for roles
CREATE TABLE IF NOT EXISTS `roles` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `role_name` VARCHAR(255) NOT NULL UNIQUE,
    `role_description` TEXT
);

-- Table for permissions
CREATE TABLE IF NOT EXISTS `permissions` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `permission_name` VARCHAR(255) NOT NULL UNIQUE,
    `permission_description` TEXT
);

-- Main table for users
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(255) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `email` VARCHAR(255) NOT NULL UNIQUE,
    `firstname` VARCHAR(100) NULL,
    `lastname` VARCHAR(100) NULL,
    `role_id` INT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`)
);

-- Pivot table for many-to-many relationship between roles and permissions
CREATE TABLE IF NOT EXISTS `role_permissions` (
    `role_id` INT,
    `permission_id` INT,
    PRIMARY KEY (`role_id`, `permission_id`),
    FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`permission_id`) REFERENCES `permissions`(`id`) ON DELETE CASCADE
);

-- Step 3: Insert data.

-- Insert roles
INSERT INTO `roles` (`role_name`, `role_description`) VALUES
('admin', 'Administrator with all permissions'),
('manager', 'Manager role with specific permissions'),
('employee', 'Employee role with basic permissions');

-- Insert permissions generated from routes.json
INSERT IGNORE INTO `permissions` (`permission_name`, `permission_description`) VALUES
-- Authentication
('index', 'Access to the index route'),
('auth:login', 'Ability to log in'),
('auth:register', 'Ability to register a new account'),
('auth:logout', 'Ability to log out'),
('auth:forgotPassword', 'Ability to use the forgot password feature'),
('auth:resetPassword', 'Ability to reset a password'),
('auth:changePassword', 'Ability to change a password'),

-- User profile
('user:index', 'Ability to view user profile'),
('user:update', 'Ability to update user profile'),
('user:destroy', 'Ability to delete a user account'),

-- Dashboard
('dashboard:index', 'Access to the dashboard interface'),

-- Settings
('settings:index', 'Ability to view settings'),

-- Permissions
('permissions:index', 'Ability to view permissions'),
('permissions:show', 'Ability to view a single permission'),
('permissions:create', 'Ability to create new permissions'),
('permissions:store', 'Ability to store new permissions'),
('permissions:edit', 'Ability to modify existing permissions'),
('permissions:update', 'Ability to update permissions'),
('permissions:delete', 'Ability to remove permissions'),
('permissions:destroy', 'Ability to delete permissions'),

-- Roles
('roles:index', 'Ability to view roles list'),
('roles:show', 'Ability to view a single role with its permissions'),
('roles:create', 'Ability to create new roles'),
('roles:store', 'Ability to store new roles'),
('roles:edit', 'Ability to modify existing roles'),
('roles:update', 'Ability to update roles'),
('roles:delete', 'Ability to remove roles'),
('roles:destroy', 'Ability to delete roles'),

-- Users
('users:index', 'Ability to view users list'),
('users:show', 'Ability to view a single user'),
('users:create', 'Ability to create new users'),
('users:store', 'Ability to store new users'),
('users:edit', 'Ability to modify existing users'),
('users:update', 'Ability to update users'),
('users:delete', 'Ability to remove users'),
('users:destroy', 'Ability to delete users'),

-- IT Service Requests
('ItServiceRequest:index', 'Can list IT Service Request List'),
('ItServiceRequest:store', 'Can Create IT Service Request List'),
('ItServiceRequest:update', 'Can Edit IT Service Request List'),
('ItServiceRequest:destroy', 'Can Delete IT Service Request List'),
('ItServiceRequest:change_status', 'Can Change IT Service Request List Status'),

-- System
('routes:index', 'Ability to view available routes');

-- Insert default users
-- Password for all users is 'changeme'
INSERT INTO `users` (`username`, `password`, `email`, `firstname`, `lastname`, `role_id`) VALUES
('admin', '$2y$10$o03M1IfNa5YvBeORxpkm3.vegq9JBzg5fzL20qjYRe/qTH8dbuMly', 'admin@example.com', 'System', 'Administrator', (SELECT id FROM roles WHERE role_name = 'admin')),
('manager', '$2y$10$o03M1IfNa5YvBeORxpkm3.vegq9JBzg5fzL20qjYRe/qTH8dbuMly', 'manager@example.com', 'Department', 'Manager', (SELECT id FROM roles WHERE role_name = 'manager')),
('employee', '$2y$10$o03M1IfNa5YvBeORxpkm3.vegq9JBzg5fzL20qjYRe/qTH8dbuMly', 'employee@example.com', 'Regular', 'Employee', (SELECT id FROM roles WHERE role_name = 'employee'));

-- Assign all permissions to the admin role
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT
    (SELECT id FROM roles WHERE role_name = 'admin'),
    p.id
FROM
    permissions p;

-- Assign basic permissions to the employee role
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT
    (SELECT id FROM roles WHERE role_name = 'employee'),
    p.id
FROM
    permissions p
WHERE
    p.permission_name IN ('user:index', 'dashboard:index', 'auth:logout', 'auth:changePassword', 'routes:index');

-- Assign manager permissions
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT
    (SELECT id FROM roles WHERE role_name = 'manager'),
    p.id
FROM
    permissions p
WHERE
    p.permission_name IN ('user:index', 'user:update', 'dashboard:index', 'auth:logout', 'auth:changePassword', 'routes:index');

-- Database rebuild is complete.
