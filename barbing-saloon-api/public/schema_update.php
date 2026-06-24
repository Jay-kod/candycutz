<?php
$pdo = new PDO('mysql:host=localhost;dbname=candycutz_db', 'root', '');
$pdo->exec("ALTER TABLE services ADD COLUMN image2 VARCHAR(255) NULL AFTER image");
$pdo->exec("ALTER TABLE services ADD COLUMN image3 VARCHAR(255) NULL AFTER image2");
echo "Added image2 and image3 columns.";
