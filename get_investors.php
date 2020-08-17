<?php
require __DIR__.'/vendor/autoload.php'; // Composer's autoloader
require 'database.php';
require 'functions.php';

$client = \Symfony\Component\Panther\Client::createChromeClient(null, ['-no-sandbox', '--headless', '--proxy-server=http=socks5://Selmarkdissel1992:C8w2FpO@193.0.178.151:48785']);

for($i=0;$i<48;$i++) {
    $url = "https://www.etoro.com/sapi/rankings/rankings/?blocked=false&bonusonly=false&client_request_id=fbba0c08-f28a-4d81-ade2-9a62e900cbf1&copyblock=false&dailyddmin=-5&gainmin=10&istestaccount=false&optin=true&page=".$i."&pagesize=250&period=LastTwoYears&profitablemonthspctmin=50&sort=-copiers&tradesmin=5&weeklyddmin=-15";
    $crawler = $client->request("GET", 'https://www.etoro.com/sapi/rankings/rankings/?blocked=false&bonusonly=false&client_request_id=4b5f0936-999b-498f-9714-538e6808f490&copyblock=false&gainmin=10&istestaccount=false&optin=true&page='.$i.'&pagesize=250&period=LastTwoYears&profitablemonthspctmin=50&sort=-copiers');
    $html = $crawler->filter("body")->html();
    $html = getContents($html, '<body><pre style="word-wrap: break-word; white-space: pre-wrap;">', '</pre></body>');
    file_put_contents('investors/page_' . $i . '.txt', $html); // Save the HTML in a text file with the investorID as name
}
