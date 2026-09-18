<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LegacyDataSeeder extends Seeder
{
    public function run(): void
    {
        $sqls = [
            // seed_data.sql
            <<<'SQL'
INSERT INTO users (name, email, password, role, is_active, created_at, updated_at) VALUES
('Super Admin', 'superadmin@candycutz.com', '$2y$12$mmv6e8EoDEIriV//zb2v5u2YeGzUBEGvr7RSeYbbGaX9EDdfCIr3C', 'super_admin', TRUE, NOW(), NOW());

INSERT INTO users (name, email, password, role, is_active, created_at, updated_at) VALUES
('John Admin', 'admin@candycutz.com', '$2y$12$2N7mXIVmdprcpcLA9VafGuiJOWW1hDK/DxAOt2II5g6UYkWWZfWI2', 'admin', TRUE, NOW(), NOW()),
('Jane Admin', 'admin2@candycutz.com', '$2y$12$2N7mXIVmdprcpcLA9VafGuiJOWW1hDK/DxAOt2II5g6UYkWWZfWI2', 'admin', TRUE, NOW(), NOW());

INSERT INTO users (name, email, password, role, avatar, phone, is_active, created_at, updated_at) VALUES
('Marcus Johnson', 'marcus@candycutz.com', '$2y$12$vRFgVJLqbFyJqVT9luXWxu1v0oTEyxB3L/eM5SWe2v8fur4885HKW', 'barber', '/images/barbers/barber-1.png', '555-0101', TRUE, NOW(), NOW()),
('David Williams', 'david@candycutz.com', '$2y$12$vRFgVJLqbFyJqVT9luXWxu1v0oTEyxB3L/eM5SWe2v8fur4885HKW', 'barber', '/images/barbers/barber-2.png', '555-0102', TRUE, NOW(), NOW()),
('James Brown', 'james@candycutz.com', '$2y$12$vRFgVJLqbFyJqVT9luXWxu1v0oTEyxB3L/eM5SWe2v8fur4885HKW', 'barber', '/images/barbers/barber-1.png', '555-0103', TRUE, NOW(), NOW()),
('Chris Lee', 'chris@candycutz.com', '$2y$12$vRFgVJLqbFyJqVT9luXWxu1v0oTEyxB3L/eM5SWe2v8fur4885HKW', 'barber', '/images/barbers/barber-2.png', '555-0104', TRUE, NOW(), NOW());

INSERT INTO users (name, email, password, role, phone, is_active, created_at, updated_at) VALUES
('Michael Smith', 'michael@example.com', '$2y$12$tgl43jYl2IaAXKaH20XADOIdnNaXRIyj7cXdmO1Fc8p9k1Hh2FUdq', 'customer', '555-1001', TRUE, NOW(), NOW()),
('Robert Jones', 'robert@example.com', '$2y$12$tgl43jYl2IaAXKaH20XADOIdnNaXRIyj7cXdmO1Fc8p9k1Hh2FUdq', 'customer', '555-1002', TRUE, NOW(), NOW()),
('William Garcia', 'william@example.com', '$2y$12$tgl43jYl2IaAXKaH20XADOIdnNaXRIyj7cXdmO1Fc8p9k1Hh2FUdq', 'customer', '555-1003', TRUE, NOW(), NOW()),
('Thomas Miller', 'thomas@example.com', '$2y$12$tgl43jYl2IaAXKaH20XADOIdnNaXRIyj7cXdmO1Fc8p9k1Hh2FUdq', 'customer', '555-1004', TRUE, NOW(), NOW()),
('Anthony Davis', 'anthony@example.com', '$2y$12$tgl43jYl2IaAXKaH20XADOIdnNaXRIyj7cXdmO1Fc8p9k1Hh2FUdq', 'customer', '555-1005', TRUE, NOW(), NOW()),
('Kevin Rodriguez', 'kevin@example.com', '$2y$12$tgl43jYl2IaAXKaH20XADOIdnNaXRIyj7cXdmO1Fc8p9k1Hh2FUdq', 'customer', '555-1006', TRUE, NOW(), NOW()),
('Jason Martinez', 'jason@example.com', '$2y$12$tgl43jYl2IaAXKaH20XADOIdnNaXRIyj7cXdmO1Fc8p9k1Hh2FUdq', 'customer', '555-1007', TRUE, NOW(), NOW()),
('Jeffrey Moore', 'jeffrey@example.com', '$2y$12$tgl43jYl2IaAXKaH20XADOIdnNaXRIyj7cXdmO1Fc8p9k1Hh2FUdq', 'customer', '555-1008', TRUE, NOW(), NOW()),
('Jane Customer', 'customer@candycutz.com', '$2y$12$tgl43jYl2IaAXKaH20XADOIdnNaXRIyj7cXdmO1Fc8p9k1Hh2FUdq', 'customer', '555-1009', TRUE, NOW(), NOW());

INSERT INTO service_categories (name, slug, icon, display_order, is_active, created_at, updated_at) VALUES
('Haircuts', 'haircuts', 'scissors', 1, TRUE, NOW(), NOW()),
('Beard Grooming', 'beard-grooming', 'beard', 2, TRUE, NOW(), NOW()),
('Hair Treatment', 'hair-treatment', 'spray', 3, TRUE, NOW(), NOW()),
('Styling', 'styling', 'wand', 4, TRUE, NOW(), NOW());

INSERT INTO services (name, slug, description, category_id, image, price, duration_minutes, is_available, created_at, updated_at) VALUES
('Classic Fade', 'classic-fade', 'A timeless, impeccably blended fade tailored to your exact head shape.', 1, '/images/services/service-1.png', 25.00, 30, TRUE, NOW(), NOW()),
('Taper Fade', 'taper-fade', 'Modern taper fade with crisp, detailed finishing for a sharp look.', 1, '/images/services/service-1.png', 28.00, 35, TRUE, NOW(), NOW()),
('Textured Crop', 'textured-crop', 'A stylish textured modern crop, perfect for easy maintenance.', 1, '/images/services/service-1.png', 30.00, 35, TRUE, NOW(), NOW()),
('High Fade', 'high-fade', 'Sharp high fade with intricate custom design options available.', 1, '/images/services/service-1.png', 32.00, 40, TRUE, NOW(), NOW()),
('Kids Haircut', 'kids-haircut', 'Gentle and stylish haircut service tailored for children.', 1, '/images/services/service-1.png', 15.00, 20, TRUE, NOW(), NOW()),
('Senior Cut', 'senior-cut', 'Classic, dignified haircut specialized for our senior customers.', 1, '/images/services/service-1.png', 18.00, 25, TRUE, NOW(), NOW()),
('Full Beard Trim', 'full-beard-trim', 'Professional beard trimming, shaping, and essential oil treatment.', 2, '/images/services/service-1.png', 20.00, 25, TRUE, NOW(), NOW()),
('Beard Detail', 'beard-detail', 'Detailed beard design with ultra-sharp razor lines and fading.', 2, '/images/services/service-1.png', 25.00, 30, TRUE, NOW(), NOW()),
('Beard Conditioning', 'beard-conditioning', 'Deep conditioning spa treatment to soften and nourish your beard.', 2, '/images/services/service-1.png', 15.00, 15, TRUE, NOW(), NOW()),
('Hot Towel Shave', 'hot-towel-shave', 'A traditional, incredibly relaxing hot towel straight razor shave.', 2, '/images/services/service-1.png', 30.00, 35, TRUE, NOW(), NOW()),
('Scalp Treatment', 'scalp-treatment', 'Invigorating deep scalp cleansing and revitalization therapy.', 3, '/images/services/service-1.png', 35.00, 45, TRUE, NOW(), NOW()),
('Hair Color', 'hair-color', 'Professional, premium-grade hair coloring and blending service.', 3, '/images/services/service-1.png', 50.00, 60, TRUE, NOW(), NOW()),
('Shampoo & Conditioning', 'shampoo-conditioning', 'Luxury double-wash shampoo and deep conditioning session.', 3, '/images/services/service-1.png', 20.00, 20, TRUE, NOW(), NOW()),
('Hair Styling', 'hair-styling', 'Expert hair styling using premium pomades and clays for events.', 4, '/images/services/service-1.png', 40.00, 45, TRUE, NOW(), NOW());

INSERT INTO barbers (user_id, bio, specialties, rating, experience_years, is_available, created_at, updated_at) VALUES
(4, 'Expert in fades and modern cuts with 10 years experience', '["fades", "modern_cuts", "beard_design"]', 4.8, 10, TRUE, NOW(), NOW()),
(5, 'Specialist in beard grooming and styling', '["beard_grooming", "beard_design", "hot_shave"]', 4.9, 8, TRUE, NOW(), NOW()),
(6, 'Master of precision cuts and texturing', '["texture", "design", "color"]', 4.7, 7, TRUE, NOW(), NOW()),
(7, 'All-around expert with customer-first approach', '["all_styles", "beard", "shaving"]', 4.6, 5, TRUE, NOW(), NOW());

INSERT INTO working_hours (barber_id, day_of_week, open_time, close_time, is_closed, created_at, updated_at) VALUES
(1, 0, '09:00:00', '18:00:00', FALSE, NOW(), NOW()),
(1, 1, '09:00:00', '18:00:00', FALSE, NOW(), NOW()),
(1, 2, '09:00:00', '18:00:00', FALSE, NOW(), NOW()),
(1, 3, '09:00:00', '18:00:00', FALSE, NOW(), NOW()),
(1, 4, '09:00:00', '18:00:00', FALSE, NOW(), NOW()),
(1, 5, '09:00:00', '16:00:00', FALSE, NOW(), NOW()),
(2, 0, '10:00:00', '18:00:00', FALSE, NOW(), NOW()),
(2, 1, '10:00:00', '18:00:00', FALSE, NOW(), NOW()),
(2, 2, '10:00:00', '18:00:00', FALSE, NOW(), NOW()),
(2, 3, '10:00:00', '18:00:00', FALSE, NOW(), NOW()),
(2, 4, '10:00:00', '18:00:00', FALSE, NOW(), NOW()),
(2, 5, '10:00:00', '16:00:00', FALSE, NOW(), NOW()),
(3, 0, '09:00:00', '18:00:00', FALSE, NOW(), NOW()),
(3, 1, '09:00:00', '18:00:00', FALSE, NOW(), NOW()),
(3, 2, '09:00:00', '18:00:00', FALSE, NOW(), NOW()),
(3, 3, '09:00:00', '18:00:00', FALSE, NOW(), NOW()),
(3, 4, '09:00:00', '18:00:00', FALSE, NOW(), NOW()),
(3, 5, '09:00:00', '16:00:00', FALSE, NOW(), NOW()),
(4, 0, '08:00:00', '17:00:00', FALSE, NOW(), NOW()),
(4, 1, '08:00:00', '17:00:00', FALSE, NOW(), NOW()),
(4, 2, '08:00:00', '17:00:00', FALSE, NOW(), NOW()),
(4, 3, '08:00:00', '17:00:00', FALSE, NOW(), NOW()),
(4, 4, '08:00:00', '17:00:00', FALSE, NOW(), NOW()),
(4, 5, '08:00:00', '15:00:00', FALSE, NOW(), NOW());

INSERT INTO settings (`key`, `value`, created_at, updated_at) VALUES
('shop_name', 'CandyCutz', NOW(), NOW()),
('shop_email', 'contact@candycutz.com', NOW(), NOW()),
('shop_phone', '555-CUTZ-00', NOW(), NOW()),
('shop_address', '123 Main Street, Barberville, CA 90001', NOW(), NOW()),
('shop_timezone', 'America/Los_Angeles', NOW(), NOW()),
('appointment_slot_duration', '30', NOW(), NOW()),
('min_advance_booking_hours', '2', NOW(), NOW()),
('max_advance_booking_days', '60', NOW(), NOW());

INSERT INTO gallery (barber_id, image_path, description, title, category, is_featured, created_at, updated_at) VALUES
(1, '/images/gallery/fade-1.png', 'Classic skin fade with razor-sharp lines.', 'Skin Fade', 'haircut', TRUE, NOW(), NOW()),
(1, '/images/gallery/fade-1.png', 'High fade precision cut.', 'High Fade', 'haircut', FALSE, NOW(), NOW()),
(2, '/images/gallery/beard-1.png', 'Luxury full beard shaping and trimming.', 'Beard Trim', 'beard', TRUE, NOW(), NOW()),
(2, '/images/gallery/beard-1.png', 'Detailed beard styling with sharp contours.', 'Beard Styling', 'beard', FALSE, NOW(), NOW()),
(3, '/images/gallery/fade-1.png', 'Modern textured crop with a low taper.', 'Textured Crop', 'haircut', TRUE, NOW(), NOW()),
(3, '/images/gallery/fade-1.png', 'Contemporary textured cut and style.', 'Textured Cut', 'haircut', FALSE, NOW(), NOW()),
(4, '/images/gallery/beard-1.png', 'Traditional hot towel straight razor shave.', 'Hot Towel Shave', 'beard', TRUE, NOW(), NOW()),
(NULL, '/images/blog/blog-1.png', 'The premium interior of CandyCutz.', 'Shop Interior', 'shop', FALSE, NOW(), NOW());

INSERT INTO testimonials (customer_id, client_name, barber_id, rating, review, is_approved, is_featured, created_at, updated_at) VALUES
(8, 'Michael B.', 1, 5, 'I''ve been looking for a barber who actually understands how to fade without taking the guard too high, and Marcus completely nailed it. The attention to detail during the line-up was insane. The shop atmosphere is premium and super relaxing. Definitely found my new spot.', TRUE, TRUE, NOW(), NOW()),
(9, 'Sarah T.', 2, 5, 'David gave me the best beard trim and hot towel shave I''ve had in years. He took his time to map out the shape before starting, which I really appreciated. You can tell he''s deeply passionate about his craft. The complimentary drink was a nice touch too!', TRUE, TRUE, NOW(), NOW()),
(10, 'David L.', 3, 4, 'Really solid haircut. I came in wanting a completely new style and James helped me figure out what would work best for my face shape. The only reason it''s not 5 stars is because they were running about 10 minutes behind schedule, but the cut itself was flawless.', TRUE, FALSE, NOW(), NOW()),
(11, 'Chris W.', 1, 5, 'First time at CandyCutz and I was blown away by the level of service. Marcus doesn''t just cut hair; he performs an art. He fixed a bad fade I got from another shop and left me looking sharp for my wedding weekend. Absolutely worth the price.', FALSE, FALSE, NOW(), NOW()),
(12, 'Amanda R.', 4, 5, 'I brought my 5-year-old son in for a cut, and they handled him so well. It''s usually a nightmare getting him to sit still, but the barber was patient, engaging, and gave him a perfect little gentleman''s cut. Love that this place caters to everyone.', TRUE, TRUE, NOW(), NOW()),
(13, 'James P.', 2, 5, 'The Presidential Grooming package is next level. David includes a scalp massage and facial scrub that genuinely makes you feel like a new person. If you want to treat yourself to a luxury grooming experience, this is the place to go. 10/10.', FALSE, FALSE, NOW(), NOW()),
(14, 'Robert K.', 3, 5, 'Consistently excellent. I''ve been coming here for 6 months and every single cut has been precision perfect. The booking process is seamless and the shop is always impeccably clean. Highly recommend the VIP treatment.', TRUE, TRUE, NOW(), NOW()),
(15, 'Thomas M.', 1, 4, 'Solid haircut as always. Marcus is consistent and reliable.', TRUE, FALSE, NOW(), NOW());

INSERT INTO blog_posts (author_id, title, slug, body, excerpt, featured_image, status, published_at, created_at, updated_at) VALUES
(2, 'The Art of the Perfect Fade', 'art-of-the-perfect-fade', 'A comprehensive guide to understanding fade haircuts, their variations, and why they remain timeless in men''s grooming. We dive deep into the techniques that make a fade transition seamlessly from skin to top volume.', 'Learn about the most popular haircut style and how our barbers achieve the flawless blend.', '/images/blog/blog-1.png', 'published', NOW(), NOW(), NOW()),
(2, 'Beard Care 101: Tips and Tricks', 'beard-care-tips', 'Everything you need to know about maintaining a healthy, impressive beard including grooming techniques and premium product recommendations. We outline a daily routine that guarantees a softer, well-shaped beard.', 'Master the art of beard maintenance with our expert daily grooming tips.', '/images/gallery/beard-1.png', 'published', NOW(), NOW(), NOW()),
(3, 'Summer Haircut Styles 2026', 'summer-haircut-styles', 'Discover the hottest haircut trends for summer 2026, perfect for beating the heat while looking exceptionally sharp. From buzz cuts to textured crops, find the style that fits your lifestyle.', 'Explore trendy, low-maintenance summer haircut styles perfect for the season.', '/images/gallery/fade-1.png', 'published', NOW(), NOW(), NOW()),
(2, 'How to Talk to Your Barber', 'how-to-talk-to-barber', 'Communication tips for getting exactly the haircut you want. Learn the right terminology, how to describe your desired style, and what reference photos work best.', 'Improve your barbershop experience with better, clearer communication.', '/images/barbers/barber-1.png', 'published', NOW(), NOW(), NOW()),
(3, 'The Importance of Professional Grooming', 'importance-professional-grooming', 'Why investing in professional grooming services drastically impacts your appearance and confidence in personal and professional settings. First impressions are everything.', 'Understand why professional grooming is a necessity, not just a luxury.', '/images/services/service-1.png', 'published', NOW(), NOW(), NOW());

INSERT INTO appointments (customer_id, barber_id, service_id, client_name, client_phone, client_email, appointment_date, appointment_time, total_price, status, notes, created_at, updated_at) VALUES
(8, 1, 1, 'Michael B.', '555-0108', 'michael.b@example.com', DATE_ADD(CURDATE(), INTERVAL 1 DAY), '10:00:00', 3500.00, 'confirmed', 'Fade cut', NOW(), NOW()),
(9, 2, 7, 'Sarah T.', '555-0109', 'sarah.t@example.com', DATE_ADD(CURDATE(), INTERVAL 2 DAY), '14:00:00', 2500.00, 'confirmed', 'Full beard trim', NOW(), NOW()),
(10, 3, 2, 'David L.', '555-0110', 'david.l@example.com', DATE_ADD(CURDATE(), INTERVAL 3 DAY), '11:00:00', 3000.00, 'pending', 'Taper fade', NOW(), NOW()),
(11, 1, 9, 'Chris W.', '555-0111', 'chris.w@example.com', DATE_ADD(CURDATE(), INTERVAL 4 DAY), '15:30:00', 1500.00, 'confirmed', 'Beard conditioning', NOW(), NOW()),
(12, 4, 3, 'Amanda R.', '555-0112', 'amanda.r@example.com', DATE_ADD(CURDATE(), INTERVAL 5 DAY), '12:00:00', 4000.00, 'pending', 'Textured crop', NOW(), NOW()),
(8, 2, 10, 'Michael B.', '555-0108', 'michael.b@example.com', DATE_ADD(CURDATE(), INTERVAL 1 DAY), '16:00:00', 2000.00, 'completed', 'Hot towel shave', NOW(), NOW()),
(13, 1, 4, 'James P.', '555-0113', 'james.p@example.com', DATE_ADD(CURDATE(), INTERVAL 7 DAY), '09:30:00', 3500.00, 'confirmed', 'High fade', NOW(), NOW()),
(14, 3, 6, 'Robert K.', '555-0114', 'robert.k@example.com', DATE_ADD(CURDATE(), INTERVAL 6 DAY), '13:00:00', 2500.00, 'pending', 'Senior cut', NOW(), NOW());
SQL,
            // seed_hero_settings.sql
            <<<'SQL'
INSERT IGNORE INTO `settings` (`key`, `value`, `group`, `created_at`, `updated_at`) VALUES
  ('hero_image', NULL, 'hero', NOW(), NOW()),
  ('hero_title', 'Premium grooming with a sharper standard.', 'hero', NOW(), NOW()),
  ('hero_subtitle', 'Experience the CandyCutz difference. Log in to explore our full menu of premium services, meet our expert barbers, and book your next appointment seamlessly.', 'hero', NOW(), NOW());
SQL
        ];

        $driver = DB::connection()->getDriverName();

        foreach ($sqls as $sql) {
            // Replace DATE_ADD syntax for SQLite
            if ($driver === 'sqlite') {
                $sql = str_replace('INSERT IGNORE INTO', 'INSERT OR IGNORE INTO', $sql);
                // In sqlite, NOW() is usually fine as a string but better to use CURRENT_TIMESTAMP or let Laravel format it
                $sql = str_replace('NOW()', 'CURRENT_TIMESTAMP', $sql);
                // Convert DATE_ADD(CURDATE(), INTERVAL X DAY) to DATE('now', '+X day')
                $sql = preg_replace('/DATE_ADD\(CURDATE\(\),\s*INTERVAL\s*(\d+)\s*DAY\)/', "DATE('now', '+\$1 day')", $sql);
            }

            DB::unprepared($sql);
        }
    }
}
