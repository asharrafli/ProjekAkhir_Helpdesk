-- Fix for tickets table structure
-- Run this SQL script directly in your MySQL database

-- Add missing columns to tickets table
ALTER TABLE tickets ADD COLUMN IF NOT EXISTS title VARCHAR(255) AFTER category_id;
ALTER TABLE tickets ADD COLUMN IF NOT EXISTS subcategory_id BIGINT UNSIGNED NULL AFTER category_id;
ALTER TABLE tickets ADD COLUMN IF NOT EXISTS assigned_to BIGINT UNSIGNED NULL AFTER user_id;
ALTER TABLE tickets ADD COLUMN IF NOT EXISTS resolved_at TIMESTAMP NULL AFTER priority;
ALTER TABLE tickets ADD COLUMN IF NOT EXISTS due_date TIMESTAMP NULL AFTER resolved_at;
ALTER TABLE tickets ADD COLUMN IF NOT EXISTS attachments JSON NULL AFTER due_date;
ALTER TABLE tickets ADD COLUMN IF NOT EXISTS first_response_at TIMESTAMP NULL AFTER attachments;
ALTER TABLE tickets ADD COLUMN IF NOT EXISTS last_activity_at TIMESTAMP NULL AFTER first_response_at;
ALTER TABLE tickets ADD COLUMN IF NOT EXISTS response_time_minutes INT NULL AFTER last_activity_at;
ALTER TABLE tickets ADD COLUMN IF NOT EXISTS resolution_notes TEXT NULL AFTER response_time_minutes;
ALTER TABLE tickets ADD COLUMN IF NOT EXISTS is_escalated BOOLEAN DEFAULT FALSE AFTER resolution_notes;
ALTER TABLE tickets ADD COLUMN IF NOT EXISTS escalated_at TIMESTAMP NULL AFTER is_escalated;
ALTER TABLE tickets ADD COLUMN IF NOT EXISTS sla_data JSON NULL AFTER escalated_at;
ALTER TABLE tickets ADD COLUMN IF NOT EXISTS deleted_at TIMESTAMP NULL;

-- Update status enum to include all values
ALTER TABLE tickets MODIFY COLUMN status ENUM('open', 'in_progress', 'assigned', 'pending', 'escalated', 'closed', 'resolved') DEFAULT 'open';

-- Update priority enum to include critical
ALTER TABLE tickets MODIFY COLUMN priority ENUM('low', 'medium', 'high', 'urgent', 'critical') DEFAULT 'medium';

-- Add foreign key constraints
ALTER TABLE tickets ADD CONSTRAINT fk_tickets_assigned_to FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL;
ALTER TABLE tickets ADD CONSTRAINT fk_tickets_subcategory FOREIGN KEY (subcategory_id) REFERENCES ticket_subcategories(id) ON DELETE SET NULL;

-- Verify the structure
DESCRIBE tickets;