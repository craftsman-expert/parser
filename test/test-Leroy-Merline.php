<?php

require_once "../vendor/autoload.php";

use src\Parser;
use src\parsers\LeroyMerlin;



$leroy = new LeroyMerlin();
$leroy->setStoreNumber(2);
$leroy->setKeyWord('Краска');

/** @var Parser $parser */
$parser = new Parser($leroy);

// 93.188.206.216:24531:cjzdkn:I6LmeEY5Td
//$parser->setProxy('93.188.206.216',24531,'cjzdkn','I6LmeEY5Td');

$result = $parser->loadProduct();

print_r($result);

print "memory usage: " . $parser->memory_get_usage();