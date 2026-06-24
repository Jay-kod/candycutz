<?php
$pdo = new PDO('mysql:host=localhost;dbname=candycutz_db', 'root', '');
$pdo->exec("INSERT IGNORE INTO settings (`key`, `value`) VALUES ('hero_title', 'The Premium Grooming Experience')");
$pdo->exec("INSERT IGNORE INTO settings (`key`, `value`) VALUES ('hero_subtitle', 'Where classic barbering meets modern style. Book your appointment today and elevate your look.')");
$pdo->exec("INSERT IGNORE INTO settings (`key`, `value`) VALUES ('hero_image', 'images/hero/barber_hero.jpg')");
echo "Settings seeded successfully.";
