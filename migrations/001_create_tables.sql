-- 001_create_tables.sql
-- Create core tables for MindEase (MySQL 8+)
-- Run as a single migration (be sure your DB name exists)

SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS actions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  slug VARCHAR(100) NOT NULL UNIQUE,
  title VARCHAR(255) NOT NULL,
  type ENUM('breathing','grounding','journal','other') NOT NULL DEFAULT 'other',
  duration_seconds INT NULL,
  content_json TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS users (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  email_hash VARCHAR(128) NULL,            -- store hashed email only if accounts allowed
  settings_json JSON NULL,
  consent_flags JSON NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS entries (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  user_hash VARCHAR(128) NULL,              -- anonymized id (HMAC or similar)
  method ENUM('quick','text') NOT NULL,
  input_text TEXT NULL,                     -- ENCRYPT before inserting if storing real text
  slider_value TINYINT NULL,
  score TINYINT NOT NULL COMMENT '0-10 stress score',
  tone_tags JSON NULL,                      -- JSON array of tags
  suggestion_id INT NULL,
  feedback ENUM('helpful','not_helpful') NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_user_hash (user_hash),
  INDEX idx_created_at (created_at),
  FOREIGN KEY (suggestion_id) REFERENCES actions(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS feedback_log (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  entry_id BIGINT NULL,
  user_hash VARCHAR(128) NULL,
  note TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (entry_id) REFERENCES entries(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS audit_log (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  event_type VARCHAR(100) NOT NULL,
  meta_json JSON NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_event_type (event_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
