-- Migration: add position column to users
ALTER TABLE users
  ADD COLUMN position VARCHAR(100) DEFAULT NULL;
