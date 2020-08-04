<?php

require 'functions.php';
require 'database.php';
$files = glob("investment_data/*.txt");

foreach ($files as $file) {
    $html = file_get_contents($file);
    $customerId = getContents($file, "/", ".txt")[0];
    print($html);
}

echo "\nThere are " . count($files) . " investor investments updated!";
