<?php
$mysqli = new mysqli('127.0.0.1', 'root', '', 'candycutz_db');
$indexes = [
    'blog_posts' => ['idx_blog_posts_author' => 'author_id', 'idx_blog_posts_published' => 'is_published'],
    'testimonials' => ['idx_testimonials_approved' => 'is_approved', 'idx_testimonials_barber' => 'barber_id'],
    'gallery' => ['idx_gallery_barber' => 'barber_id']
];
foreach ($indexes as $table => $inds) {
    foreach ($inds as $idxName => $col) {
        $check = $mysqli->query("SHOW INDEX FROM `$table` WHERE Key_name = '$idxName'");
        if ($check && $check->num_rows == 0) {
            $mysqli->query("ALTER TABLE `$table` ADD INDEX `$idxName` (`$col`)");
            echo "Added $idxName to $table\n";
        } else {
            echo "$idxName already exists on $table (or table doesn't exist)\n";
        }
    }
}
