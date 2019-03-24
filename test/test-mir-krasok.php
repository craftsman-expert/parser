<?php

require_once "../vendor/autoload.php";

use src\Parser;
use src\parsers\LeroyMerlin;
use src\parsers\MirKrasok;



$store = new MirKrasok();
$store->setStoreNumber(1873);
$store->setKeyWord('Краска');
$store->setUrl('/product/dulux_ultra_resist_dulyuks_dlya_detskoy/');

/** @var Parser $parser */
$parser = new Parser($store);

// 93.188.206.216:24531:cjzdkn:I6LmeEY5Td
//  $parser->setProxy('93.188.206.216',24531,'cjzdkn','I6LmeEY5Td');

$result = $parser->loadUrl();

print_r($result);

print "memory usage: " . $parser->memory_get_usage() / 1024 / 1024 . " mb";