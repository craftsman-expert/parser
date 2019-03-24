<?php

require_once "../vendor/autoload.php";

use GuzzleHttp\TransferStats;
use src\Parser;
use src\parsers\KolesaDarom;



$store = new KolesaDarom();
$store->setPath('moskva');
$store->setSection('shiny');
$store->setQ('Шин');


/** @var Parser $parser */
$parser = new Parser($store);

// $parser->setOnStats(function(TransferStats $stats){
//     echo $stats->getEffectiveUri() . "\n";
//     echo $stats->getTransferTime() . "\n";
//     print_r($stats->getHandlerStats());
//
//     // You must check if a response was received before using the
//     // response object.
//     if ($stats->hasResponse()) {
//         echo $stats->getResponse()->getStatusCode();
//     } else {
//         // Error data is handler specific. You will need to know what
//         // type of error data your handler uses before using this
//         // value.
//         print_r($stats->getHandlerErrorData());
//     }
// });


// 93.188.206.216:24531:cjzdkn:I6LmeEY5Td
//  $parser->setProxy('93.188.206.216',24531,'cjzdkn','I6LmeEY5Td');


$result = $parser->loadUrl();
// $result = $store->getCities();

print_r($result);

print "memory usage: " . $parser->memory_get_usage() / 1024 / 1024 . " mb";