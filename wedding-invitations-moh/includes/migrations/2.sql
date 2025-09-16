
-- Add new fields to events table for invitation management
ALTER TABLE events ADD COLUMN invitation_image_url TEXT;
ALTER TABLE events ADD COLUMN invitation_text TEXT;
ALTER TABLE events ADD COLUMN location_details TEXT;
ALTER TABLE events ADD COLUMN custom_fields TEXT; -- JSON string for custom fields

-- Create invitation_links table for unique guest links
CREATE TABLE invitation_links (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  guest_id INTEGER NOT NULL,
  event_id INTEGER NOT NULL,
  unique_token TEXT NOT NULL UNIQUE,
  qr_code_url TEXT,
  is_used BOOLEAN DEFAULT FALSE,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Create comments table for congratulations
CREATE TABLE comments (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  event_id INTEGER NOT NULL,
  author_name TEXT NOT NULL,
  comment_text TEXT NOT NULL,
  is_visible BOOLEAN DEFAULT TRUE,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Update rsvps table to track unique responses
ALTER TABLE rsvps ADD COLUMN invitation_token TEXT;
ALTER TABLE rsvps ADD COLUMN is_confirmed BOOLEAN DEFAULT FALSE;
