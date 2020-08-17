<?php

require 'database.php';
require 'functions.php';

$files = glob("investors/*.txt");

foreach ($files as $file) {
    $file = file_get_contents($file);
    $investors = json_decode($file, true)['Items'];
    setInvestors($investors, $conn);
}


