-- Fix for ticket_categories table structure
-- Run this SQL script directly in your database

-- Check if columns exist and add them if missing
PRAGMA table_info(ticket_categories);

-- Add missing columns if they don't exist
ALTER TABLE ticket_categories ADD COLUMN description TEXT DEFAULT NULL;
ALTER TABLE ticket_categories ADD COLUMN is_active BOOLEAN DEFAULT 1;
ALTER TABLE ticket_categories ADD COLUMN sort_order INTEGER DEFAULT 0;

-- Verify the structure
PRAGMA table_info(ticket_categories);