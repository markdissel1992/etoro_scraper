<?php
// TODO: check if negative values get inserted into database
require 'functions.php';
require 'database.php';
$files = glob("investor_data/*.txt");

foreach($files as $file) {
    $html = file_get_contents($file);
    $customerId = getContents($file, "/", ".txt")[0];
    $parsedMonthlyData = parseMonthlyData($html);
    setMonthlyInvestorData($customerId, $parsedMonthlyData, $conn);
    $customerStats = getCustomerStats($html, $parsedMonthlyData);
    print_r($customerStats);
    setCustomerStats($customerId, $customerStats, $conn);
}


