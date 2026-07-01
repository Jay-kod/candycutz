<?php
$is_closed_true = true;
$is_closed_false = false;
$is_closed_string_true = "true";
$is_closed_string_false = "false";

echo "true: " . (empty($is_closed_true) ? 1 : 0) . "\n";
echo "false: " . (empty($is_closed_false) ? 1 : 0) . "\n";
echo "'true': " . (empty($is_closed_string_true) ? 1 : 0) . "\n";
echo "'false': " . (empty($is_closed_string_false) ? 1 : 0) . "\n";
