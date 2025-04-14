-- First make sure the name column exists (for safety)
ALTER TABLE users 
MODIFY COLUMN name VARCHAR(100) NOT NULL;

-- Add last_name column
ALTER TABLE users
ADD COLUMN last_name VARCHAR(50) DEFAULT NULL;

-- Update existing records to split name into first_name and last_name
UPDATE users 
SET last_name = SUBSTRING_INDEX(name, ' ', -1),
    name = SUBSTRING_INDEX(name, ' ', 1);

-- Rename name to first_name
ALTER TABLE users
CHANGE COLUMN name first_name VARCHAR(50) NOT NULL;

-- Make last_name NOT NULL after data migration
ALTER TABLE users
MODIFY COLUMN last_name VARCHAR(50) NOT NULL;
