-- CandyCutz Additional Seed Data

USE candycutz_db;

-- Blog Posts
INSERT INTO blog_posts (author_id, title, slug, content, excerpt, featured_image, is_published, created_at, updated_at) VALUES
(2, 'The Art of the Perfect Fade', 'art-of-the-perfect-fade', 'A comprehensive guide to understanding fade haircuts, their variations, and why they remain timeless in men\'s grooming. We dive deep into the techniques that make a fade transition seamlessly from skin to top volume.', 'Learn about the most popular haircut style and how our barbers achieve the flawless blend.', '/images/blog/blog-1.png', TRUE, NOW(), NOW()),
(2, 'Beard Care 101: Tips and Tricks', 'beard-care-tips', 'Everything you need to know about maintaining a healthy, impressive beard including grooming techniques and premium product recommendations. We outline a daily routine that guarantees a softer, well-shaped beard.', 'Master the art of beard maintenance with our expert daily grooming tips.', '/images/gallery/beard-1.png', TRUE, NOW(), NOW()),
(3, 'Summer Haircut Styles 2026', 'summer-haircut-styles', 'Discover the hottest haircut trends for summer 2026, perfect for beating the heat while looking exceptionally sharp. From buzz cuts to textured crops, find the style that fits your lifestyle.', 'Explore trendy, low-maintenance summer haircut styles perfect for the season.', '/images/gallery/fade-1.png', TRUE, NOW(), NOW()),
(2, 'How to Talk to Your Barber', 'how-to-talk-to-barber', 'Communication tips for getting exactly the haircut you want. Learn the right terminology, how to describe your desired style, and what reference photos work best.', 'Improve your barbershop experience with better, clearer communication.', '/images/barbers/barber-1.png', TRUE, NOW(), NOW()),
(3, 'The Importance of Professional Grooming', 'importance-professional-grooming', 'Why investing in professional grooming services drastically impacts your appearance and confidence in personal and professional settings. First impressions are everything.', 'Understand why professional grooming is a necessity, not just a luxury.', '/images/services/service-1.png', TRUE, NOW(), NOW());

-- Testimonials
INSERT INTO testimonials (customer_id, barber_id, rating, comment, is_approved, created_at, updated_at) VALUES
(8, 1, 5, 'Marcus did an amazing job with my fade. Very professional and friendly!', TRUE, NOW(), NOW()),
(9, 2, 5, 'Best beard trim I\'ve ever had. David really knows his craft.', TRUE, NOW(), NOW()),
(10, 3, 4, 'Great haircut and nice atmosphere. Will definitely come back.', TRUE, NOW(), NOW()),
(11, 1, 5, 'Excellent service, Marcus is very skilled and attentive to detail.', TRUE, NOW(), NOW()),
(12, 4, 4, 'Good experience, the barber was knowledgeable and friendly.', TRUE, NOW(), NOW()),
(13, 2, 5, 'Top-notch service! David exceeded my expectations.', TRUE, NOW(), NOW()),
(14, 3, 5, 'Professional staff, great results, will recommend to friends.', TRUE, NOW(), NOW()),
(15, 1, 4, 'Solid haircut as always. Marcus is consistent and reliable.', TRUE, NOW(), NOW());

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

SELECT 'Additional seeding complete!' as Status;
