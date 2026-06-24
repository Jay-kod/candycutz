<?php
$pdo = new PDO('mysql:host=localhost;dbname=candycutz_db', 'root', '');
$pdo->exec("INSERT INTO settings (`key`, `value`) VALUES ('hero_image', 'hero/barber_hero.png') ON DUPLICATE KEY UPDATE `value`='hero/barber_hero.png'");
echo "Hero image updated in DB.";
