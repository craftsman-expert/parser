<?php


namespace src\parsers;



use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Cookie\FileCookieJar;
use src\ParserException;

/**
 * Class MirKrasok
 * @package src\parsers
 */
class MirKrasok extends AbstractParser implements InterfaceParser
{
    /**
     * @var Client
     */
    private $http;
    /**
     * @var integer
     */
    private $store_number;
    /**
     * @var string
     */
    private $proxy = '';
    private $key_word;
    private $url;

    /**
     * @var callable
     */
    private $onProgress;
    /**
     * @var callable
     */
    private $onStats;


    /**
     * Obi constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->http = new Client([
            'base_uri' => 'https://www.mirkrasok.ru',
            'timeout' => $this->http_timeout,
            'connect_timeout' => $this->http_connect_timeout,
        ]);
    }



    /**
     * @return \stdClass
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Exception
     */
    function loadProduct()
    {
        try {

            $cookie_jar = CookieJar::fromArray([
                'MY_SHOP' => $this->getStoreNumber(),
            ], 'www.mirkrasok.ru');

            $res = $this->http->request('GET', $this->getUrl(), [
                'on_stats' => $this->onStats,
                'progress' => $this->onProgress,
                'cookies' => $cookie_jar,
            ]);

            $doc = $this->newDocumentHTML($res->getBody()->getContents());

            $product_id = $doc->find('.dItemDetail')->attr('data-product-id');
            $title = $doc->find('h1[itemprop="name"]')->text();
            $sub_title = $doc->find('span[class="sub_title"]')->text();
            $short_description = $doc->find('[itemprop="description"] .preview_text p')->text();
            $long_description = $doc->find('[data-tab-data="DESCRIPTION"]')->text();
            $image_src = "https://mirkrasok.ru" . $doc->find('.product-image .fslider img')->attr('src');
            $in_stock = $doc->find('.buy-block .flex-item')->text();



            $specifications = [];
            $items = $doc->find('.item_bot .item_tab div[data-tab-data="TECH_DATA"] ul li');
            for ($i = 0; $i < $items->length; $i++){
                $item = $items->eq($i);

                $specifications[] = $d = [
                    'name'  => $item->find('[class="name"]')->text(),
                    'value' => $item->find('[class="text"]')->text()
                ];
            }


            $product_items = [];
            $items = $doc->find('.dItemDetail [data-volume-group]');
            for ($i = 0; $i < $items->length; $i++) {
                $item = $items->eq($i);
                $product_items[] = [
                    'article' => (int)pregMatch('/[0-9 ]+/',$item->find('.sub_item_main [class="kod"]')->text())[0],
                    'title' => $item->find('.sub_item_main .name')->text(),
                    'price' => (double) str_replace(' ', '', pregMatch('/[0-9 ]+/', $item->find('.sub_item_main .price')->text())[0]),
                    'volume' => $item->attr('data-volume'),
                    'in_stock' => null,
                    'rating' => null,
                    'delivery' => $item->find('.sub_item_main [class="how show-quantity"]')->text(),
                ];

            }

            $product = new \stdClass();
            $product->title = $title;
            $product->sub_title = $sub_title;
            $product->short_description = $short_description;
            $product->long_description = $long_description;
            $product->product_items = $product_items;
            $product->specifications = $specifications;
            $product->image_src = $image_src;
            $product->reviews = null;
            $product->has_calculator = (bool)$doc->find('.item_bot .item_tab [data-tab-data="RASHOD"]')->is('div');
            $product->has_video = (bool)$doc->find('.item_bot .item_tab [data-tab-data="VIDEO"]')->is('div');


        } finally {
            $this->unloadDocuments();
            unset($doc);
        }

        return $product;
    }



    /**
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws ParserException
     */
    function loadUrl()
    {
        $url_result = [];
        $position = 1;
        $index = 0;

        $q = str_replace(' ', '%20', $this->getKeyWord());

        $cookie_jar = CookieJar::fromArray([
            'MY_SHOP' => $this->getStoreNumber(),
        ], 'www.mirkrasok.ru');

        try{
            $res = $this->http->request('GET', '/search/', [
                'query' => [
                    'q' => $q,
                    'page' => 9999,
                ],

                'on_stats' => $this->onStats,
                'progress' => $this->onProgress,

                'proxy' => $this->proxy,
                'cookies' => $cookie_jar

            ]);

            // Response data
            $doc = $this->newDocumentHTML($res->getBody()->getContents());
            $cards = $doc->find('[class=catalogBox]');

            if ($doc->find('.no_searchTitle')->length > 0) {
                return [];
            }

            for ($i = 0; $i < $cards->length; $i++) {
                $url_result[$index]['position'] = $position++;
                $url_result[$index]['url'] = $cards->eq($i)->find('.one_tovar_box span a')->attr('href');
                $index++;
            }
        }finally{
            $this->unloadDocuments();
        }

        return $url_result;
    }



    /**
     * @return int
     */
    public function getStoreNumber()
    {
        return $this->store_number;
    }



    /**
     * @param int $store_number
     */
    public function setStoreNumber($store_number)
    {
        $this->store_number = $store_number;
    }



    /**
     * @param        $ip
     * @param        $port
     * @param string $login
     * @param        $password
     *
     * @return mixed
     */
    function setProxy($ip, $port, $login, $password)
    {
        $this->proxy = sprintf("http://%s:%s@%s:%d", $login, $password, $ip, $port);
    }



    /**
     * @param mixed $key_word
     */
    public function setKeyWord($key_word):void
    {
        $this->key_word = $key_word;
    }



    /**
     * @param mixed $url
     */
    public function setUrl($url):void
    {
        $this->url = $url;
    }



    /**
     * @return mixed
     * @throws ParserException
     */
    public function getKeyWord()
    {
        if (empty($this->key_word)) {
            throw new ParserException('Пустой поисковый запрос!', 'obi');
        }
        return $this->key_word;
    }



    /**
     * @return mixed
     */
    private function getUrl()
    {
        return $this->url;
    }



    /**
     * @param callable $function
     *
     * @return mixed
     */
    public function onProgress(callable $function)
    {
        $this->onProgress = $function;
    }



    /**
     * @param callable $function
     *
     * @return mixed
     */
    public function onStats(callable $function)
    {
        $this->onStats = $function;
    }
}