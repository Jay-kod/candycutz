-- Optimize CandyCutz Database
-- Adding indexes to frequently queried columns to improve read performance.

ALTER TABLE `blog_posts` 
  ADD INDEX `idx_blog_posts_author` (`author_id`),
  ADD INDEX `idx_blog_posts_published` (`is_published`);

ALTER TABLE `testimonials` 
  ADD INDEX `idx_testimonials_approved` (`is_approved`),
  ADD INDEX `idx_testimonials_barber` (`barber_id`);

ALTER TABLE `gallery` 
  ADD INDEX `idx_gallery_barber` (`barber_id`);
