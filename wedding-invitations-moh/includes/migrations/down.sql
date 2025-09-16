
-- Remove new fields from events table
ALTER TABLE events DROP COLUMN invitation_image_url;
ALTER TABLE events DROP COLUMN invitation_text;
ALTER TABLE events DROP COLUMN location_details;
ALTER TABLE events DROP COLUMN custom_fields;

-- Drop invitation_links table
DROP TABLE invitation_links;

-- Drop comments table
DROP TABLE comments;

-- Remove new fields from rsvps table
ALTER TABLE rsvps DROP COLUMN invitation_token;
ALTER TABLE rsvps DROP COLUMN is_confirmed;
