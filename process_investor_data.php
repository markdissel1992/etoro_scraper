<?php
require 'functions.php';
require 'database.php';
$files = glob("investor_data/*.txt");

foreach($files as $file) {
    $html = file_get_contents($file);
    $customerId = getContents($file, "/", ".txt")[0];
    $valuesPerMonthAndYear = getValuesPerMonthAndYear($html);
    setMonthlyInvestorData($customerId, $valuesPerMonthAndYear, $conn);
    $customerStats = getCustomerStats($html, $valuesPerMonthAndYear);
    setCustomerStats($customerId, $customerStats, $conn);
}

echo "There are " . count($files) . " investors updated!";
