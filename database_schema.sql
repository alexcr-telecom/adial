-- Asterisk ARI Dialer Database Schema
-- Create database
CREATE DATABASE IF NOT EXISTS `adialer` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `adialer`;

-- Campaigns table
CREATE TABLE IF NOT EXISTS `campaigns` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text,
  `trunk_type` enum('custom','pjsip','sip') NOT NULL DEFAULT 'custom',
  `trunk_value` varchar(255) NOT NULL COMMENT 'Trunk name or custom dial string',
  `callerid` varchar(100) DEFAULT NULL,
  `agent_dest_type` enum('custom','exten','ivr') NOT NULL DEFAULT 'custom',
  `agent_dest_value` varchar(255) DEFAULT NULL COMMENT 'Destination value based on type',
  `record_calls` tinyint(1) NOT NULL DEFAULT '0',
  `status` enum('stopped','running','paused') NOT NULL DEFAULT 'stopped',
  `concurrent_calls` int(11) NOT NULL DEFAULT '1',
  `retry_times` int(11) NOT NULL DEFAULT '0',
  `retry_delay` int(11) NOT NULL DEFAULT '300' COMMENT 'Retry delay in seconds',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Campaign numbers table
CREATE TABLE IF NOT EXISTS `campaign_numbers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `campaign_id` int(11) NOT NULL,
  `phone_number` varchar(50) NOT NULL,
  `status` enum('pending','calling','answered','failed','completed','no_answer','busy') NOT NULL DEFAULT 'pending',
  `attempts` int(11) NOT NULL DEFAULT '0',
  `last_attempt` timestamp NULL DEFAULT NULL,
  `data` text COMMENT 'Additional JSON data for the number',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `campaign_id` (`campaign_id`),
  KEY `status` (`status`),
  KEY `phone_number` (`phone_number`),
  CONSTRAINT `campaign_numbers_ibfk_1` FOREIGN KEY (`campaign_id`) REFERENCES `campaigns` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- IVR menus table
CREATE TABLE IF NOT EXISTS `ivr_menus` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `campaign_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `audio_file` varchar(255) NOT NULL COMMENT 'Path to audio file',
  `timeout` int(11) NOT NULL DEFAULT '10',
  `max_digits` int(11) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `campaign_id` (`campaign_id`),
  CONSTRAINT `ivr_menus_ibfk_1` FOREIGN KEY (`campaign_id`) REFERENCES `campaigns` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- IVR actions table
CREATE TABLE IF NOT EXISTS `ivr_actions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ivr_menu_id` int(11) NOT NULL,
  `dtmf_digit` varchar(10) NOT NULL,
  `action_type` enum('exten','queue','hangup','playback','goto_ivr') NOT NULL,
  `action_value` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `ivr_menu_id` (`ivr_menu_id`),
  CONSTRAINT `ivr_actions_ibfk_1` FOREIGN KEY (`ivr_menu_id`) REFERENCES `ivr_menus` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- CDR (Call Detail Records) table
CREATE TABLE IF NOT EXISTS `cdr` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `campaign_id` int(11) DEFAULT NULL,
  `campaign_number_id` int(11) DEFAULT NULL,
  `channel_id` varchar(255) DEFAULT NULL,
  `uniqueid` varchar(255) DEFAULT NULL,
  `callerid` varchar(100) DEFAULT NULL,
  `destination` varchar(100) DEFAULT NULL,
  `agent` varchar(100) DEFAULT NULL,
  `start_time` datetime DEFAULT NULL,
  `answer_time` datetime DEFAULT NULL,
  `end_time` datetime DEFAULT NULL,
  `duration` int(11) DEFAULT '0' COMMENT 'Duration in seconds',
  `billsec` int(11) DEFAULT '0' COMMENT 'Billable seconds',
  `disposition` enum('answered','no_answer','busy','failed','cancelled') DEFAULT NULL,
  `recording_file` varchar(255) DEFAULT NULL,
  `recording_leg1` varchar(255) DEFAULT NULL COMMENT 'Recording file for leg 1',
  `recording_leg2` varchar(255) DEFAULT NULL COMMENT 'Recording file for leg 2',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `campaign_id` (`campaign_id`),
  KEY `campaign_number_id` (`campaign_number_id`),
  KEY `uniqueid` (`uniqueid`),
  KEY `start_time` (`start_time`),
  CONSTRAINT `cdr_ibfk_1` FOREIGN KEY (`campaign_id`) REFERENCES `campaigns` (`id`) ON DELETE SET NULL,
  CONSTRAINT `cdr_ibfk_2` FOREIGN KEY (`campaign_number_id`) REFERENCES `campaign_numbers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Active channels table (for real-time monitoring)
CREATE TABLE IF NOT EXISTS `active_channels` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `campaign_id` int(11) DEFAULT NULL,
  `channel_id` varchar(255) NOT NULL,
  `state` varchar(50) DEFAULT NULL,
  `caller` varchar(100) DEFAULT NULL,
  `connected` varchar(100) DEFAULT NULL,
  `accountcode` varchar(100) DEFAULT NULL,
  `dialplan_app` varchar(100) DEFAULT NULL,
  `dialplan_appdata` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `channel_id` (`channel_id`),
  KEY `campaign_id` (`campaign_id`),
  CONSTRAINT `active_channels_ibfk_1` FOREIGN KEY (`campaign_id`) REFERENCES `campaigns` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- System settings table
CREATE TABLE IF NOT EXISTS `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text,
  `description` varchar(255) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Users table (for authentication)
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `role` enum('admin','user') DEFAULT 'user',
  `is_active` tinyint(1) DEFAULT '1',
  `last_login` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Insert default settings
INSERT INTO `settings` (`setting_key`, `setting_value`, `description`) VALUES
('ari_enabled', '1', 'Enable/Disable ARI functionality'),
('debug_mode', '1', 'Enable/Disable debug logging'),
('max_concurrent_campaigns', '5', 'Maximum number of concurrent campaigns'),
('call_timeout', '60', 'Call timeout in seconds');

-- Insert default admin user
-- Username: admin
-- Password: admin (CHANGE THIS IMMEDIATELY AFTER INSTALLATION!)
INSERT INTO `users` (`username`, `password`, `email`, `full_name`, `role`, `is_active`, `created_at`) VALUES
('admin', '$2y$10$nG4K5S6hSflCLUCsgn62ze7rohekGbOgEMgvFpqhPHPHMzzoFdCA.', 'admin@localhost', 'Administrator', 'admin', 1, NOW());
