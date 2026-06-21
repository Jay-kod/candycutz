USE candycutz_db;

ALTER TABLE gallery
CHANGE COLUMN image_url image_path VARCHAR(255) NOT NULL,
ADD COLUMN title VARCHAR(255) NULL AFTER id,
ADD COLUMN category ENUM('haircut', 'beard', 'combo', 'before_after', 'shop') NULL AFTER image_path,
ADD COLUMN display_order INT UNSIGNED DEFAULT 0;

UPDATE gallery SET title = 'Gallery Image', category = 'shop';

-- Re-insert gallery data
TRUNCATE TABLE gallery;
INSERT INTO gallery (barber_id, title, category, image_path, description, is_featured, created_at, updated_at) VALUES
(1, 'Classic Fade', 'haircut', '/images/gallery/fade-1.png', 'Classic skin fade with razor-sharp lines.', TRUE, NOW(), NOW()),
(1, 'High Fade', 'haircut', '/images/gallery/fade-1.png', 'High fade precision cut.', FALSE, NOW(), NOW()),
(2, 'Beard Trim', 'beard', '/images/gallery/beard-1.png', 'Luxury full beard shaping and trimming.', TRUE, NOW(), NOW()),
(2, 'Beard Styling', 'beard', '/images/gallery/beard-1.png', 'Detailed beard styling with sharp contours.', FALSE, NOW(), NOW()),
(3, 'Textured Crop', 'haircut', '/images/gallery/fade-1.png', 'Modern textured crop with a low taper.', TRUE, NOW(), NOW()),
(3, 'Texture Cut', 'haircut', '/images/gallery/fade-1.png', 'Contemporary textured cut and style.', FALSE, NOW(), NOW()),
(4, 'Hot Towel Shave', 'beard', '/images/gallery/beard-1.png', 'Traditional hot towel straight razor shave.', TRUE, NOW(), NOW()),
(NULL, 'Shop Interior', 'shop', '/images/blog/blog-1.png', 'The premium interior of CandyCutz.', FALSE, NOW(), NOW());
