<?php
require __DIR__.'/vendor/autoload.php'; // Composer's autoloader
require 'database.php';

$allInvestors = getInvestors($conn); // Get all the investors from the database

$client = \Symfony\Component\Panther\Client::createChromeClient();
$i = 0;
foreach($allInvestors as $investor) {
    getInvestorData($investor, $client);
    sleep(rand(4,7)); // Randomly sleep to make the crawler look more human-like and try not to overload the website.
    getInvestmentData($investor, $client);
    sleep(rand(4,7)); // Randomly sleep to make the crawler look more human-like and try not to overload the website.
    $i++;
}
echo "Er is nu van: " . $i . " investors data opgehaald.";

function getInvestorData($investor, $client) {
    $crawler = $client->request("GET", 'https://www.etoro.com/people/' . $investor[1] . "/stats"); // Send the crawler to the investor page
    $html = $crawler->filter(".body-content")->html(); // Get the HTML from the crawler
    file_put_contents('investor_data/' . $investor[0] . '.txt', $html); // Save the HTML in a text file with the investorID as name
}

function getInvestmentData($investor, $client) {
    $crawler = $client->request("GET", 'https://www.etoro.com/people/' . $investor[1] . "/portfolio"); // Send the crawler to the investor page
    $html = $crawler->filter(".portfolio-open-trades")->html(); // Get the HTML from the crawler
    file_put_contents('investment_data/' . $investor[0] . '.txt', $html); // Save the HTML in a text file with the investorID as name
}