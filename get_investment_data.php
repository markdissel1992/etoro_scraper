<?php

require __DIR__.'/vendor/autoload.php'; // Composer's autoloader
require 'database.php';
require 'functions.php';

$allInvestors = getInvestors($conn); // Get all the investors from the database

$proxies = array('185.236.77.52', '193.0.178.124', '45.89.189.95', '92.118.113.66');
$i = $currentProxy = 0;
$countInvestors = count($allInvestors);
$client = \Symfony\Component\Panther\Client::createChromeClient(null,['-no-sandbox', '--headless', '--proxy-server=92.118.113.66:45785']);
foreach($allInvestors as $investor) {
    setInvestmentData($investor, $client);
    $i++;
    if($i % 25 == 0) {
        $currentProxy == 3 ? $currentProxy = 0 : $currentProxy++;
        $client = \Symfony\Component\Panther\Client::createChromeClient(null,['-no-sandbox', '--headless', '--proxy-server='.$proxies[$currentProxy].':45785' ]);
        sleep(20);
    }
    echo $i."/".$countInvestors." \n";
}

function setInvestmentData($investor, $client) {
    $crawler = $client->request("GET", 'https://www.etoro.com/people/' . $investor[1] . "/portfolio"); // Send the crawler to the investor page
    sleep(5); // Randomly sleep to make the crawler look more human-like and try not to overload the website.
    $html = $crawler->filter(".portfolio-open-trades")->html(); // Get the HTML from the crawler
    file_put_contents('investment_data/' . $investor[0] . '.txt', $html); // Save the HTML in a text file with the investorID as name
    $investmentData = getInvestments($html);
    setInvestmentData($investor[0], $investmentData, $conn);
}
