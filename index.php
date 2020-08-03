<?php
require __DIR__.'/vendor/autoload.php'; // Composer's autoloader
require 'database.php';
$allInvestors = getInvestors($conn);

//Creates a client and make the crawler request the login page
//$client = \Symfony\Component\Panther\Client::createChromeClient(null, ['--proxy-server=socks5://Selmarkdissel1992:C8w2FpO@193.0.178.151:45786',]);
$guzzle = new \GuzzleHttp\Client(['proxy' => '193.0.178.151:45786']);
$client = new \Goutte\Client();
$client->setClient($guzzle);
$i = 0;
foreach($allInvestors as $investor) {
    $crawler = $client->request("GET", 'https://www.etoro.com/people/' . $investor[1] . "/stats");
    sleep(rand(4,7));
    $client->takeScreenshot('screen.png'); // Yeah, screenshot!
    $html = $crawler->filter(".body-content")->html();
    file_put_contents('investor_data/' . $investor[0] . '.txt', $html);
    echo "Er zijn nu: " . $i . " van de 251 investors data opgehaald.";
}
