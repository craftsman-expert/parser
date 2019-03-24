<?php

require_once "../vendor/autoload.php";

use src\Parser;
use src\parsers\KraskiRu;



$store = new KraskiRu();
$store->setQ('Краска');
$store->setUrl('/catalog/dulux_magic_white_dyulaks_madzhik_uayt_kraska_dlya_potolka_vodoemulsionnaya_matovaya/');

/** @var Parser $parser */
$parser = new Parser($store);


// 93.188.206.216:24531:cjzdkn:I6LmeEY5Td
$parser->setProxy('93.188.206.216',24531,'cjzdkn','I6LmeEY5Td');


$result = $parser->loadProduct();

print_r($result);

print "memory usage: " . $parser->memory_get_usage() / 1024 / 1024 . " mb";