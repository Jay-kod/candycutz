USE candycutz_db;

-- 1. Get the IDs of the new categories
SET @men_id = (SELECT id FROM service_categories WHERE name = 'Men' LIMIT 1);
SET @women_id = (SELECT id FROM service_categories WHERE name = 'Women' LIMIT 1);
SET @children_id = (SELECT id FROM service_categories WHERE name = 'Children' LIMIT 1);
SET @home_id = (SELECT id FROM service_categories WHERE name = 'Home Services' LIMIT 1);
SET @special_id = (SELECT id FROM service_categories WHERE name = 'Special Services' LIMIT 1);
SET @disab_id = (SELECT id FROM service_categories WHERE name = 'Disabilities' LIMIT 1);

-- 2. Update existing services to fall under these categories
UPDATE services SET category_id = @men_id WHERE id IN (1, 2, 3, 4, 6, 7, 8, 9, 10);
UPDATE services SET category_id = @children_id WHERE id = 5;
UPDATE services SET category_id = @women_id WHERE id IN (11, 12, 13, 14);

-- Make sure the dummy ones are explicitly mapped
UPDATE services SET category_id = @men_id WHERE name = 'Men Executive Cut';
UPDATE services SET category_id = @women_id WHERE name = 'Women Pixie Cut';
UPDATE services SET category_id = @children_id WHERE name = 'Kids Super Fade';
UPDATE services SET category_id = @home_id WHERE name = 'Home VIP Service';
UPDATE services SET category_id = @special_id WHERE name = 'Wedding Special';
UPDATE services SET category_id = @disab_id WHERE name = 'Accessible Care Cut';

-- 3. Delete the old categories (Haircuts, Beard Grooming, Hair Treatment, Styling)
DELETE FROM service_categories WHERE name IN ('Haircuts', 'Beard Grooming', 'Hair Treatment', 'Styling');
