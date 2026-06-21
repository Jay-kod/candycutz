-- CandyCutz Database Seed Data

USE candycutz_db;

-- Users: Super Admin
INSERT INTO users (name, email, password, role, is_active, created_at, updated_at) VALUES
('Super Admin', 'superadmin@candycutz.com', '$2y$12$PLACEHOLDER', 'super_admin', TRUE, NOW(), NOW());

-- Users: Admins
INSERT INTO users (name, email, password, role, is_active, created_at, updated_at) VALUES
('John Admin', 'admin@candycutz.com', '$2y$12$PLACEHOLDER', 'admin', TRUE, NOW(), NOW()),
('Jane Admin', 'admin2@candycutz.com', '$2y$12$PLACEHOLDER', 'admin', TRUE, NOW(), NOW());

-- Users: Barbers
INSERT INTO users (name, email, password, role, avatar, phone, is_active, created_at, updated_at) VALUES
('Marcus Johnson', 'marcus@candycutz.com', '$2y$12$PLACEHOLDER', 'barber', '/images/barbers/barber-1.png', '555-0101', TRUE, NOW(), NOW()),
('David Williams', 'david@candycutz.com', '$2y$12$PLACEHOLDER', 'barber', '/images/barbers/barber-2.png', '555-0102', TRUE, NOW(), NOW()),
('James Brown', 'james@candycutz.com', '$2y$12$PLACEHOLDER', 'barber', '/images/barbers/barber-1.png', '555-0103', TRUE, NOW(), NOW()),
('Chris Lee', 'chris@candycutz.com', '$2y$12$PLACEHOLDER', 'barber', '/images/barbers/barber-2.png', '555-0104', TRUE, NOW(), NOW());

-- Users: Customers
INSERT INTO users (name, email, password, role, phone, is_active, created_at, updated_at) VALUES
('Michael Smith', 'michael@example.com', '$2y$12$PLACEHOLDER', 'customer', '555-1001', TRUE, NOW(), NOW()),
('Robert Jones', 'robert@example.com', '$2y$12$PLACEHOLDER', 'customer', '555-1002', TRUE, NOW(), NOW()),
('William Garcia', 'william@example.com', '$2y$12$PLACEHOLDER', 'customer', '555-1003', TRUE, NOW(), NOW()),
('Thomas Miller', 'thomas@example.com', '$2y$12$PLACEHOLDER', 'customer', '555-1004', TRUE, NOW(), NOW()),
('Anthony Davis', 'anthony@example.com', '$2y$12$PLACEHOLDER', 'customer', '555-1005', TRUE, NOW(), NOW()),
('Kevin Rodriguez', 'kevin@example.com', '$2y$12$PLACEHOLDER', 'customer', '555-1006', TRUE, NOW(), NOW()),
('Jason Martinez', 'jason@example.com', '$2y$12$PLACEHOLDER', 'customer', '555-1007', TRUE, NOW(), NOW()),
('Jeffrey Moore', 'jeffrey@example.com', '$2y$12$PLACEHOLDER', 'customer', '555-1008', TRUE, NOW(), NOW());

-- Service Categories
INSERT INTO service_categories (name, description, icon, created_at, updated_at) VALUES
('Haircuts', 'Premium haircut services with various styles', 'scissors', NOW(), NOW()),
('Beard Grooming', 'Professional beard trimming and care', 'beard', NOW(), NOW()),
('Hair Treatment', 'Specialized hair treatments and therapies', 'spray', NOW(), NOW()),
('Styling', 'Hair styling and design services', 'wand', NOW(), NOW());

-- Services (10+ services)
INSERT INTO services (name, description, category_id, image, price, duration_minutes, is_available, created_at, updated_at) VALUES
('Classic Fade', 'A timeless, impeccably blended fade tailored to your exact head shape.', 1, '/images/services/service-1.png', 25.00, 30, TRUE, NOW(), NOW()),
('Taper Fade', 'Modern taper fade with crisp, detailed finishing for a sharp look.', 1, '/images/services/service-1.png', 28.00, 35, TRUE, NOW(), NOW()),
('Textured Crop', 'A stylish textured modern crop, perfect for easy maintenance.', 1, '/images/services/service-1.png', 30.00, 35, TRUE, NOW(), NOW()),
('High Fade', 'Sharp high fade with intricate custom design options available.', 1, '/images/services/service-1.png', 32.00, 40, TRUE, NOW(), NOW()),
('Kids Haircut', 'Gentle and stylish haircut service tailored for children.', 1, '/images/services/service-1.png', 15.00, 20, TRUE, NOW(), NOW()),
('Senior Cut', 'Classic, dignified haircut specialized for our senior customers.', 1, '/images/services/service-1.png', 18.00, 25, TRUE, NOW(), NOW()),
('Full Beard Trim', 'Professional beard trimming, shaping, and essential oil treatment.', 2, '/images/services/service-1.png', 20.00, 25, TRUE, NOW(), NOW()),
('Beard Detail', 'Detailed beard design with ultra-sharp razor lines and fading.', 2, '/images/services/service-1.png', 25.00, 30, TRUE, NOW(), NOW()),
('Beard Conditioning', 'Deep conditioning spa treatment to soften and nourish your beard.', 2, '/images/services/service-1.png', 15.00, 15, TRUE, NOW(), NOW()),
('Hot Towel Shave', 'A traditional, incredibly relaxing hot towel straight razor shave.', 2, '/images/services/service-1.png', 30.00, 35, TRUE, NOW(), NOW()),
('Scalp Treatment', 'Invigorating deep scalp cleansing and revitalization therapy.', 3, '/images/services/service-1.png', 35.00, 45, TRUE, NOW(), NOW()),
('Hair Color', 'Professional, premium-grade hair coloring and blending service.', 3, '/images/services/service-1.png', 50.00, 60, TRUE, NOW(), NOW()),
('Shampoo & Conditioning', 'Luxury double-wash shampoo and deep conditioning session.', 3, '/images/services/service-1.png', 20.00, 20, TRUE, NOW(), NOW()),
('Hair Styling', 'Expert hair styling using premium pomades and clays for events.', 4, '/images/services/service-1.png', 40.00, 45, TRUE, NOW(), NOW());

-- Barbers (link to users)
INSERT INTO barbers (user_id, bio, specialties, rating, experience_years, is_available, created_at, updated_at) VALUES
(4, 'Expert in fades and modern cuts with 10 years experience', '["fades", "modern_cuts", "beard_design"]', 4.8, 10, TRUE, NOW(), NOW()),
(5, 'Specialist in beard grooming and styling', '["beard_grooming", "beard_design", "hot_shave"]', 4.9, 8, TRUE, NOW(), NOW()),
(6, 'Master of precision cuts and texturing', '["texture", "design", "color"]', 4.7, 7, TRUE, NOW(), NOW()),
(7, 'All-around expert with customer-first approach', '["all_styles", "beard", "shaving"]', 4.6, 5, TRUE, NOW(), NOW());

-- Working Hours (Monday-Friday, 9am-6pm; Saturday 9am-4pm)
INSERT INTO working_hours (barber_id, day_of_week, start_time, end_time, is_available, created_at, updated_at) VALUES
-- Marcus (Monday-Saturday)
(1, 0, '09:00:00', '18:00:00', TRUE, NOW(), NOW()),
(1, 1, '09:00:00', '18:00:00', TRUE, NOW(), NOW()),
(1, 2, '09:00:00', '18:00:00', TRUE, NOW(), NOW()),
(1, 3, '09:00:00', '18:00:00', TRUE, NOW(), NOW()),
(1, 4, '09:00:00', '18:00:00', TRUE, NOW(), NOW()),
(1, 5, '09:00:00', '16:00:00', TRUE, NOW(), NOW()),
-- David (Monday-Saturday)
(2, 0, '10:00:00', '18:00:00', TRUE, NOW(), NOW()),
(2, 1, '10:00:00', '18:00:00', TRUE, NOW(), NOW()),
(2, 2, '10:00:00', '18:00:00', TRUE, NOW(), NOW()),
(2, 3, '10:00:00', '18:00:00', TRUE, NOW(), NOW()),
(2, 4, '10:00:00', '18:00:00', TRUE, NOW(), NOW()),
(2, 5, '10:00:00', '16:00:00', TRUE, NOW(), NOW()),
-- James (Monday-Friday)
(3, 0, '09:00:00', '18:00:00', TRUE, NOW(), NOW()),
(3, 1, '09:00:00', '18:00:00', TRUE, NOW(), NOW()),
(3, 2, '09:00:00', '18:00:00', TRUE, NOW(), NOW()),
(3, 3, '09:00:00', '18:00:00', TRUE, NOW(), NOW()),
(3, 4, '09:00:00', '18:00:00', TRUE, NOW(), NOW()),
-- Chris (Tuesday-Saturday)
(4, 1, '11:00:00', '19:00:00', TRUE, NOW(), NOW()),
(4, 2, '11:00:00', '19:00:00', TRUE, NOW(), NOW()),
(4, 3, '11:00:00', '19:00:00', TRUE, NOW(), NOW()),
(4, 4, '11:00:00', '19:00:00', TRUE, NOW(), NOW()),

INSERT INTO settings (key, value, created_at, updated_at) VALUES
('shop_name', 'CandyCutz', NOW(), NOW()),
('shop_email', 'contact@candycutz.com', NOW(), NOW()),
('shop_phone', '555-CUTZ-00', NOW(), NOW()),
('shop_address', '123 Main Street, Barberville, CA 90001', NOW(), NOW()),
('shop_timezone', 'America/Los_Angeles', NOW(), NOW()),
('appointment_slot_duration', '30', NOW(), NOW()),
('min_advance_booking_hours', '2', NOW(), NOW()),
('max_advance_booking_days', '60', NOW(), NOW());
-- Settings
INSERT INTO settings (`key`, `value`, `created_at`, `updated_at`) VALUES
('shop_name', 'CandyCutz', NOW(), NOW()),
('shop_email', 'contact@candycutz.com', NOW(), NOW()),
('shop_phone', '555-CUTZ-00', NOW(), NOW()),
('shop_address', '123 Main Street, Barberville, CA 90001', NOW(), NOW()),
('shop_timezone', 'America/Los_Angeles', NOW(), NOW()),
('appointment_slot_duration', '30', NOW(), NOW()),
('min_advance_booking_hours', '2', NOW(), NOW()),
('max_advance_booking_days', '60', NOW(), NOW());

-- Gallery Images
INSERT INTO gallery (barber_id, image_url, description, is_featured, created_at, updated_at) VALUES
(1, '/images/gallery/fade-1.png', 'Classic skin fade with razor-sharp lines.', TRUE, NOW(), NOW()),
(1, '/images/gallery/fade-1.png', 'High fade precision cut.', FALSE, NOW(), NOW()),
(2, '/images/gallery/beard-1.png', 'Luxury full beard shaping and trimming.', TRUE, NOW(), NOW()),
(2, '/images/gallery/beard-1.png', 'Detailed beard styling with sharp contours.', FALSE, NOW(), NOW()),
(3, '/images/gallery/fade-1.png', 'Modern textured crop with a low taper.', TRUE, NOW(), NOW()),
(3, '/images/gallery/fade-1.png', 'Contemporary textured cut and style.', FALSE, NOW(), NOW()),
(4, '/images/gallery/beard-1.png', 'Traditional hot towel straight razor shave.', TRUE, NOW(), NOW()),
(NULL, '/images/blog/blog-1.png', 'The premium interior of CandyCutz.', FALSE, NOW(), NOW());

-- Testimonials
INSERT INTO testimonials (customer_id, barber_id, rating, comment, is_approved, created_at, updated_at) VALUES
(8, 1, 5, 'I\'ve been looking for a barber who actually understands how to fade without taking the guard too high, and Marcus completely nailed it. The attention to detail during the line-up was insane. The shop atmosphere is premium and super relaxing. Definitely found my new spot.', TRUE, NOW(), NOW()),
(9, 2, 5, 'David gave me the best beard trim and hot towel shave I\'ve had in years. He took his time to map out the shape before starting, which I really appreciated. You can tell he\'s deeply passionate about his craft. The complimentary drink was a nice touch too!', TRUE, NOW(), NOW()),
(10, 3, 4, 'Really solid haircut. I came in wanting a completely new style and James helped me figure out what would work best for my face shape. The only reason it\'s not 5 stars is because they were running about 10 minutes behind schedule, but the cut itself was flawless.', TRUE, NOW(), NOW()),
(11, 1, 5, 'First time at CandyCutz and I was blown away by the level of service. Marcus doesn\'t just cut hair; he performs an art. He fixed a bad fade I got from another shop and left me looking sharp for my wedding weekend. Absolutely worth the price.', FALSE, NOW(), NOW()),
(12, 4, 5, 'I brought my 5-year-old son in for a cut, and they handled him so well. It\'s usually a nightmare getting him to sit still, but the barber was patient, engaging, and gave him a perfect little gentleman\'s cut. Love that this place caters to everyone.', TRUE, NOW(), NOW()),
(13, 2, 5, 'The Presidential Grooming package is next level. David includes a scalp massage and facial scrub that genuinely makes you feel like a new person. If you want to treat yourself to a luxury grooming experience, this is the place to go. 10/10.', FALSE, NOW(), NOW()),
(14, 3, 5, 'Consistently excellent. I\'ve been coming here for 6 months and every single cut has been precision perfect. The booking process is seamless and the shop is always impeccably clean. Highly recommend the VIP treatment.', TRUE, NOW(), NOW());
(15, 1, 4, 'Solid haircut as always. Marcus is consistent and reliable.', TRUE, NOW(), NOW());

-- Blog Posts
INSERT INTO blog_posts (author_id, title, slug, content, excerpt, featured_image, is_published, created_at, updated_at) VALUES
(2, 'The Art of the Perfect Fade', 'art-of-the-perfect-fade', 'A comprehensive guide to understanding fade haircuts, their variations, and why they remain timeless in men\'s grooming. We dive deep into the techniques that make a fade transition seamlessly from skin to top volume.', 'Learn about the most popular haircut style and how our barbers achieve the flawless blend.', '/images/blog/blog-1.png', TRUE, NOW(), NOW()),
(2, 'Beard Care 101: Tips and Tricks', 'beard-care-tips', 'Everything you need to know about maintaining a healthy, impressive beard including grooming techniques and premium product recommendations. We outline a daily routine that guarantees a softer, well-shaped beard.', 'Master the art of beard maintenance with our expert daily grooming tips.', '/images/gallery/beard-1.png', TRUE, NOW(), NOW()),
(3, 'Summer Haircut Styles 2026', 'summer-haircut-styles', 'Discover the hottest haircut trends for summer 2026, perfect for beating the heat while looking exceptionally sharp. From buzz cuts to textured crops, find the style that fits your lifestyle.', 'Explore trendy, low-maintenance summer haircut styles perfect for the season.', '/images/gallery/fade-1.png', TRUE, NOW(), NOW()),
(2, 'How to Talk to Your Barber', 'how-to-talk-to-barber', 'Communication tips for getting exactly the haircut you want. Learn the right terminology, how to describe your desired style, and what reference photos work best.', 'Improve your barbershop experience with better, clearer communication.', '/images/barbers/barber-1.png', TRUE, NOW(), NOW()),
(3, 'The Importance of Professional Grooming', 'importance-professional-grooming', 'Why investing in professional grooming services drastically impacts your appearance and confidence in personal and professional settings. First impressions are everything.', 'Understand why professional grooming is a necessity, not just a luxury.', '/images/services/service-1.png', TRUE, NOW(), NOW());

-- Appointments (mix of statuses)
INSERT INTO appointments (customer_id, barber_id, service_id, appointment_date, appointment_time, status, notes, created_at, updated_at) VALUES
(8, 1, 1, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '10:00:00', 'confirmed', 'Fade cut', NOW(), NOW()),
(9, 2, 7, DATE_ADD(CURDATE(), INTERVAL 2 DAY), '14:00:00', 'confirmed', 'Full beard trim', NOW(), NOW()),
(10, 3, 2, DATE_ADD(CURDATE(), INTERVAL 3 DAY), '11:00:00', 'pending', 'Taper fade', NOW(), NOW()),
(11, 1, 9, DATE_ADD(CURDATE(), INTERVAL 4 DAY), '15:30:00', 'confirmed', 'Beard conditioning', NOW(), NOW()),
(12, 4, 3, DATE_ADD(CURDATE(), INTERVAL 5 DAY), '12:00:00', 'pending', 'Textured crop', NOW(), NOW()),
(8, 2, 10, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '16:00:00', 'completed', 'Hot towel shave', NOW(), NOW()),
(13, 1, 4, DATE_ADD(CURDATE(), INTERVAL 7 DAY), '09:30:00', 'confirmed', 'High fade', NOW(), NOW()),
(14, 3, 6, DATE_ADD(CURDATE(), INTERVAL 6 DAY), '13:00:00', 'pending', 'Senior cut', NOW(), NOW());

SELECT 'Database seeding complete!' as Status;
