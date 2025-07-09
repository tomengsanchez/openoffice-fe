-- Insert initial roles
INSERT INTO `roles` (`role_name`, `role_description`) VALUES
('admin', 'Administrator'),
('manager', 'Manager'),
('employee', 'Employee')
ON DUPLICATE KEY UPDATE role_name=role_name; -- Do nothing if it already exists

-- Insert initial permissions
INSERT INTO `permissions` (`permission_name`, `permission_description`) VALUES
('all', 'All Permissions'),
('read', 'Read Permissions'),
('write', 'Write Permissions'),
('delete', 'Delete Permissions')
ON DUPLICATE KEY UPDATE permission_name=permission_name; -- Do nothing if it already exists

-- Assign all permissions to the admin role
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT r.id, p.id FROM roles r, permissions p WHERE r.role_name = 'admin'
ON DUPLICATE KEY UPDATE role_id=role_id; -- Do nothing if it already exists

-- Insert default admin user
-- The password is 'changeme', hashed with bcrypt.
INSERT INTO `users` (`username`, `password`, `email`, `role_id`) VALUES
('admin', '$2y$10$o03M1IfNa5YvBeORxpkm3.vegq9JBzg5fzL20qjYRe/qTH8dbuMly', 'admin@mailinator.com', (SELECT id FROM roles WHERE role_name = 'admin'))
ON DUPLICATE KEY UPDATE username=username; -- Do nothing if it already exists
