-- =====================================================
-- Database Migration: Add Endpoints Cache Table
-- =====================================================
-- Version: 1.0
-- Date: 2026-01-14
-- Description: Cache table for SIP/PJSIP endpoints to improve performance
-- =====================================================

CREATE TABLE IF NOT EXISTS `endpoints_cache` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `technology` enum('SIP','PJSIP') NOT NULL,
  `resource` varchar(100) NOT NULL,
  `state` varchar(50) DEFAULT NULL,
  `last_seen` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_endpoint` (`technology`,`resource`),
  KEY `technology` (`technology`),
  KEY `last_seen` (`last_seen`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- =====================================================
-- Verification Queries:
-- =====================================================
-- DESCRIBE endpoints_cache;
-- SELECT * FROM endpoints_cache;
-- =====================================================
