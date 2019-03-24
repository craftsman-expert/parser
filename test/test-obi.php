<?php

require_once "../vendor/autoload.php";

use src\Parser;
use src\parsers\Obi;

$obi = new Obi();
$obi->setStoreNumber(2);
$obi->setKeyWord('Краска');


/** @var Parser $parser */
$parser = new Parser($obi);

// 93.188.206.216:24531:cjzdkn:I6LmeEY5Td
$parser->setProxy('93.188.206.216', 24531, 'cjzdkn', 'I6LmeEY5Td');


// foreach ($parser->loadUrl() as $item){
//     $obi->setUrl($item['url']);
//     $result = $parser->loadProduct();
//     print_r($result); print PHP_EOL;
//     print "memory usage: " . $parser->memory_get_usage()  / 1024 / 1024 . " mb" . PHP_EOL;
//     sleep(3);
//
//     file_put_contents('out.json', json_encode($result));
// }



$obi->setUrl('/kraski-dlya-vnutrennikh-rabot/kraska-tikkurila-luja-7-baza-a-belaya-9-l/p/3964327');
$result = $parser->loadProduct();
print_r($result);
print PHP_EOL;
file_put_contents('out.json', json_encode($result));






