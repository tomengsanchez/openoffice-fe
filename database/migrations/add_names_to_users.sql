-- Add firstname and lastname columns to users table
ALTER TABLE `users` 
ADD COLUMN `firstname` VARCHAR(100) NULL AFTER `email`,
ADD COLUMN `lastname` VARCHAR(100) NULL AFTER `firstname`;

-- Update existing users with default names
UPDATE `users` SET 
    `firstname` = 'System',
    `lastname` = 'Administrator'
WHERE `username` = 'admin';

UPDATE `users` SET 
    `firstname` = 'Department',
    `lastname` = 'Manager'
WHERE `username` = 'manager';

UPDATE `users` SET 
    `firstname` = 'Regular',
    `lastname` = 'Employee'
WHERE `username` = 'employee';
