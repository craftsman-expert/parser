<?php

require_once "../vendor/autoload.php";

use src\Parser;
use src\parsers\Petrovich;



$store = new Petrovich();
// $store->setUrl('//moscow.petrovich.ru');
$store->setUrl('//vladimir.petrovich.ru/catalog/107344259/128477/');
// $store->setCity('Москва');
// $store->q('Краска');

// $cities = $store->getCities();
// $store->setOnStats(function(TransferStats $stats) {
//     print $stats->getTransferTime();
// });



/** @var Parser $parser */
$parser = new Parser($store);

// $parser->setOnProgress(function($downloadTotal, $downloadedBytes, $uploadTotal, $uploadedBytes){
//   print  $downloadedBytes . PHP_EOL;
// });

// $parser->setOnStats(function(TransferStats $stats){
//     echo $stats->getEffectiveUri() . "\n";
//     echo $stats->getTransferTime() . "\n";
//     var_dump($stats->getHandlerStats());
//
//     // You must check if a response was received before using the
//     // response object.
//     if ($stats->hasResponse()) {
//         echo $stats->getResponse()->getStatusCode();
//     } else {
//         // Error data is handler specific. You will need to know what
//         // type of error data your handler uses before using this
//         // value.
//         var_dump($stats->getHandlerErrorData());
//     }
// });

// 93.188.206.216:24531:cjzdkn:I6LmeEY5Td
//  $parser->setProxy('93.188.206.216',24531,'cjzdkn','I6LmeEY5Td');


$result = $parser->loadProduct();

print_r($result);

print "memory usage: " . $parser->memory_get_usage() / 1024 / 1024 . " mb";