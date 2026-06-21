-- CandyCutz: Add images and update data
-- Run this against an existing database to add image support

USE candycutz_db;

-- Add image column to services if it doesn't exist
SET @dbname = DATABASE();
SET @tablename = 'services';
SET @columnname = 'image';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = @columnname
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE `', @tablename, '` ADD COLUMN `', @columnname, '` VARCHAR(255) NULL AFTER `description`')
));
PREPARE addColumnIfNotExists FROM @preparedStatement;
EXECUTE addColumnIfNotExists;
DEALLOCATE PREPARE addColumnIfNotExists;

-- Update barber user avatars
UPDATE users SET avatar = '/images/barbers/barber-1.png' WHERE email = 'marcus@candycutz.com';
UPDATE users SET avatar = '/images/barbers/barber-2.png' WHERE email = 'david@candycutz.com';
UPDATE users SET avatar = '/images/barbers/barber-1.png' WHERE email = 'james@candycutz.com';
UPDATE users SET avatar = '/images/barbers/barber-2.png' WHERE email = 'chris@candycutz.com';

-- Update services with images and better descriptions
UPDATE services SET image = '/images/services/service-1.png', description = 'A timeless, impeccably blended fade tailored to your exact head shape.' WHERE name = 'Classic Fade';
UPDATE services SET image = '/images/services/service-1.png', description = 'Modern taper fade with crisp, detailed finishing for a sharp look.' WHERE name = 'Taper Fade';
UPDATE services SET image = '/images/services/service-1.png', description = 'A stylish textured modern crop, perfect for easy maintenance.' WHERE name = 'Textured Crop';
UPDATE services SET image = '/images/services/service-1.png', description = 'Sharp high fade with intricate custom design options available.' WHERE name = 'High Fade';
UPDATE services SET image = '/images/services/service-1.png', description = 'Gentle and stylish haircut service tailored for children.' WHERE name = 'Kids Haircut';
UPDATE services SET image = '/images/services/service-1.png', description = 'Classic, dignified haircut specialized for our senior customers.' WHERE name = 'Senior Cut';
UPDATE services SET image = '/images/services/service-1.png', description = 'Professional beard trimming, shaping, and essential oil treatment.' WHERE name = 'Full Beard Trim';
UPDATE services SET image = '/images/services/service-1.png', description = 'Detailed beard design with ultra-sharp razor lines and fading.' WHERE name = 'Beard Detail';
UPDATE services SET image = '/images/services/service-1.png', description = 'Deep conditioning spa treatment to soften and nourish your beard.' WHERE name = 'Beard Conditioning';
UPDATE services SET image = '/images/services/service-1.png', description = 'A traditional, incredibly relaxing hot towel straight razor shave.' WHERE name = 'Hot Towel Shave';
UPDATE services SET image = '/images/services/service-1.png', description = 'Invigorating deep scalp cleansing and revitalization therapy.' WHERE name = 'Scalp Treatment';
UPDATE services SET image = '/images/services/service-1.png', description = 'Professional, premium-grade hair coloring and blending service.' WHERE name = 'Hair Color';
UPDATE services SET image = '/images/services/service-1.png', description = 'Luxury double-wash shampoo and deep conditioning session.' WHERE name LIKE 'Shampoo%';
UPDATE services SET image = '/images/services/service-1.png', description = 'Expert hair styling using premium pomades and clays for events.' WHERE name = 'Hair Styling';

-- Update gallery with correct image paths
UPDATE gallery SET image_url = '/images/gallery/fade-1.png', description = 'Classic skin fade with razor-sharp lines.' WHERE id = 1;
UPDATE gallery SET image_url = '/images/gallery/fade-1.png', description = 'High fade precision cut.' WHERE id = 2;
UPDATE gallery SET image_url = '/images/gallery/beard-1.png', description = 'Luxury full beard shaping and trimming.' WHERE id = 3;
UPDATE gallery SET image_url = '/images/gallery/beard-1.png', description = 'Detailed beard styling with sharp contours.' WHERE id = 4;
UPDATE gallery SET image_url = '/images/gallery/fade-1.png', description = 'Modern textured crop with a low taper.' WHERE id = 5;
UPDATE gallery SET image_url = '/images/gallery/fade-1.png', description = 'Contemporary textured cut and style.' WHERE id = 6;
UPDATE gallery SET image_url = '/images/gallery/beard-1.png', description = 'Traditional hot towel straight razor shave.' WHERE id = 7;
UPDATE gallery SET image_url = '/images/blog/blog-1.png', description = 'The premium interior of CandyCutz.' WHERE id = 8;

-- Update blog posts with correct image paths and better text
UPDATE blog_posts SET featured_image = '/images/blog/blog-1.png', excerpt = 'Learn about the most popular haircut style and how our barbers achieve the flawless blend.' WHERE slug = 'art-of-the-perfect-fade';
UPDATE blog_posts SET featured_image = '/images/gallery/beard-1.png', excerpt = 'Master the art of beard maintenance with our expert daily grooming tips.' WHERE slug = 'beard-care-tips';
UPDATE blog_posts SET featured_image = '/images/gallery/fade-1.png', excerpt = 'Explore trendy, low-maintenance summer haircut styles perfect for the season.' WHERE slug = 'summer-haircut-styles';
UPDATE blog_posts SET featured_image = '/images/barbers/barber-1.png', excerpt = 'Improve your barbershop experience with better, clearer communication.' WHERE slug = 'how-to-talk-to-barber';
UPDATE blog_posts SET featured_image = '/images/services/service-1.png', excerpt = 'Understand why professional grooming is a necessity, not just a luxury.' WHERE slug = 'importance-professional-grooming';

SELECT 'Image migration complete!' as Status;
