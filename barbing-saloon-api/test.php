<?php require 'config/database.php'; \ = \->query('SHOW COLUMNS FROM users'); print_r(\->fetchAll(PDO::FETCH_ASSOC));
