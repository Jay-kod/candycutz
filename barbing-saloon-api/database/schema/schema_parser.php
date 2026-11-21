<?php

$content = file_get_contents(__DIR__.'/production-schema.sql');
$start = strpos($content, 'CREATE TABLE `testimonials`');
$end = strpos($content, ';', $start);
echo substr($content, $start, $end - $start + 1);
