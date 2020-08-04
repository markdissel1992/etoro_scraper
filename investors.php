<?php
require 'database.php';

//$url = "https://www.etoro.com/sapi/rankings/rankings/?blocked=false&bonusonly=false&client_request_id=4b5f0936-999b-498f-9714-538e6808f490&copyblock=false&gainmin=10&istestaccount=false&optin=true&page=1&pagesize=250&period=LastTwoYears&profitablemonthspctmin=50&sort=-copiers";

$file = file_get_contents("investors/page_1.txt");
$investors = json_decode($file, true)['Items'];

setInvestors($investors, $conn);


