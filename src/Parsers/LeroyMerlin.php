<?php


namespace src\parsers;

use GuzzleHttp\Client;
use src\ParserException;

/**
 * Class LeroyMerlin
 * @package src\parsers
 */
class LeroyMerlin extends AbstractParser implements InterfaceParser
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
    /**
     * @var callable
     */
    private $onProgress;
    /**
     * @var callable
     */
    private $onStats;



    /**
     * LeroyMerlin constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->http = new Client([
            'timeout' => $this->http_timeout,
            'connect_timeout' => $this->http_connect_timeout,
        ]);
    }



    /**
     *
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    function loadProduct()
    {
        $page_index = 1;
        $position = 1;
        $page_count = 0;
        $products = [];
        do {

            try {
                $q = str_replace(' ', '%20', $this->key_word);
                $searchInStoreId = $this->getStoreNumber();

                $url = "https://leroymerlin.ru/bitrix/php_interface/ajax/search_ajax_.php?q=$q&onPage=500&page=$page_index&sortBy=score&order=desc&needButtonBuy=false&searchInStoreId=$searchInStoreId";
                $res = $this->http->request('GET', $url, [
                    'on_stats' => $this->onStats,
                    'progress' => $this->onProgress,
                ]);

                $obj = @json_decode($res->getBody()->getContents());

                if (is_object($obj)) {
                    $page_count = (int) $obj->cntPage;
                    foreach ($obj->ITEMS as $item) {
                        $position++;
                        $name = $item->UF_NAME;
                        $rating = $item->UF_RATING;
                        $price = $item->UF_PRICE;
                        $inStock = $item->UF_COUNT;
                        $image_src = $item->PICTURE;
                        $url = $item->DETAIL_PAGE_URL;

                        $products[] = [
                            'title' => $name,
                            'price' => (double) str_replace(' ', '', $price),
                            'in_stock' => $inStock,
                            'rating' => $rating,
                            'image_src' => $image_src,
                            'position' => $position,
                            'url' => $url,
                        ];
                    }
                }

            } finally {
                $this->unloadDocuments();
            }


            $page_index++;
        } while ($page_index < $page_count + 1);

        return $products;
    }



    /**
     * @throws ParserException
     */
    function loadUrl()
    {
        // TODO: Implement loadUrl() method.
        throw new ParserException('Извините, метод не реализует интерфейс!');
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
     * @return int
     */
    public function getStoreNumber():int
    {
        return $this->store_number;
    }



    /**
     * @param int $store_number
     */
    public function setStoreNumber(int $store_number):void
    {
        $this->store_number = $store_number;
    }



    /**
     * @param mixed $key_word
     */
    public function setKeyWord($key_word):void
    {
        $this->key_word = $key_word;
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