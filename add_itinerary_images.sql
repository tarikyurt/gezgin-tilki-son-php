ALTER TABLE tour_itineraries ADD COLUMN image_url VARCHAR(255) DEFAULT NULL AFTER description;
ALTER TABLE tour_itineraries ADD COLUMN image_alt VARCHAR(255) DEFAULT NULL AFTER image_url;
