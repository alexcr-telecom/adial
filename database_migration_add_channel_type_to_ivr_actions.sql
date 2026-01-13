-- =====================================================
-- Database Migration: Add channel_type to ivr_actions
-- =====================================================
-- Version: 1.0
-- Date: 2026-01-13
-- Description: Add channel_type column to ivr_actions table for SIP/PJSIP selection
-- Backward Compatibility: YES - defaults to 'sip' for existing records
-- =====================================================

-- Add channel_type column (default 'sip')
ALTER TABLE `ivr_actions`
ADD COLUMN `channel_type` ENUM('sip','pjsip') NOT NULL DEFAULT 'sip'
COMMENT 'Channel type for exten actions (SIP or PJSIP)' AFTER `action_value`;

-- =====================================================
-- Verification Queries (run separately to confirm):
-- =====================================================
-- DESCRIBE ivr_actions;
-- SELECT * FROM ivr_actions;
-- =====================================================
