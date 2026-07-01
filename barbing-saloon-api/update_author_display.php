<?php
try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=candycutz_db;charset=utf8mb4', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("ALTER TABLE blog_posts ADD COLUMN author_display VARCHAR(255) NULL AFTER author_id");
    echo "Column author_display added successfully.\n";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "Column already exists.\n";
    } else {
        echo "Error: " . $e->getMessage() . "\n";
    }
}
