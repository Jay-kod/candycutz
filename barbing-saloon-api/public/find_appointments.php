<?php
$lines = file('api_admin.php');
foreach ($lines as $i => $line) {
    if (stripos($line, 'admin/appointments') !== false) {
        echo ($i + 1) . ': ' . trim($line) . PHP_EOL;
    }
}
