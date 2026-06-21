USE candycutz_db;

-- Add new categories
INSERT INTO service_categories (name, description, icon, created_at, updated_at) VALUES
('Men', 'Classic and modern cuts for men', 'scissors', NOW(), NOW()),
('Women', 'Premium styling and cuts for women', 'scissors', NOW(), NOW()),
('Children', 'Gentle and stylish cuts for kids', 'scissors', NOW(), NOW()),
('Home Services', 'Premium grooming at your doorstep', 'home', NOW(), NOW()),
('Special Services', 'Bespoke and specialized grooming services', 'star', NOW(), NOW()),
('Disabilities', 'Accessible and specialized care', 'heart', NOW(), NOW());

-- Retrieve the newly inserted category IDs and insert services
-- To keep this simple and compatible, we'll use a subquery for the category_id
INSERT INTO services (name, description, category_id, image, price, duration_minutes, is_available, created_at, updated_at) VALUES
('Men Executive Cut', 'A sharp executive cut for the modern man.', (SELECT id FROM service_categories WHERE name = 'Men' LIMIT 1), '/images/services/service-1.png', 40.00, 45, TRUE, NOW(), NOW()),
('Women Pixie Cut', 'A bold and beautiful pixie cut styling.', (SELECT id FROM service_categories WHERE name = 'Women' LIMIT 1), '/images/services/service-1.png', 60.00, 60, TRUE, NOW(), NOW()),
('Kids Super Fade', 'A fun and gentle fade for children.', (SELECT id FROM service_categories WHERE name = 'Children' LIMIT 1), '/images/services/service-1.png', 25.00, 30, TRUE, NOW(), NOW()),
('Home VIP Service', 'Full grooming service delivered to your home.', (SELECT id FROM service_categories WHERE name = 'Home Services' LIMIT 1), '/images/services/service-1.png', 150.00, 120, TRUE, NOW(), NOW()),
('Wedding Special', 'Complete grooming package for the groom and best men.', (SELECT id FROM service_categories WHERE name = 'Special Services' LIMIT 1), '/images/services/service-1.png', 200.00, 180, TRUE, NOW(), NOW()),
('Accessible Care Cut', 'A specialized, comfortable grooming experience.', (SELECT id FROM service_categories WHERE name = 'Disabilities' LIMIT 1), '/images/services/service-1.png', 30.00, 45, TRUE, NOW(), NOW());
