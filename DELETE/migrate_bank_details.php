<?php
$pdo = new PDO('mysql:host=localhost;dbname=candycutz_db', 'root', '');
$pdo->exec("ALTER TABLE appointments MODIFY verification_code VARCHAR(50);");
echo "Done.";
