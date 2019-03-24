<?php


namespace src\parsers;



use GuzzleHttp\Client;
use src\ParserException;

/**
 * Class MirKrasok
 * @package src\parsers
 */
class KraskiRu extends AbstractParser implements InterfaceParser
{
    /**
     * @var Client
     */
    private $http;
    /**
     * @var string
     */
    private $proxy = '';
    private $q;
    /**
     * @var string
     */
    private $url = '';

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
            'base_uri' => 'https://www.kraski.ru',
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



            $res = $this->http->request('GET', $this->url, [

                'on_stats' => $this->onStats,
                'progress' => $this->onProgress,

            ]);

            $doc = $this->newDocumentHTML($res->getBody()->getContents());

            $article = pregMatch('/[0-9]+/', $doc->find('.product--article span')->text())[0];
            $title = trim($doc->find('#product-name')->text());
            // $sub_title = $doc->find('span[class="sub_title"]')->text();
            $short_description = $doc->find('[itemprop="description"]')->text();
            $long_description = $doc->find('#tab-description')->text();
            $image_src = 'https://www.kraski.ru' . $doc->find('.product--photo img')->attr('src');
            // $in_stock = $doc->find('.buy-block .flex-item')->text();



            $specifications = [];
            $items = $doc->find('#tab-feature table tbody tr');
            for ($i = 0; $i < $items->length; $i++) {
                $item = $items->eq($i)->find('td');

                $specifications[] = $d = [
                    'name' => $item->eq(0)->text(),
                    'value' => trim($item->eq(1)->text()),
                ];
            }


            $product_items = [];
            $items = $doc->find('.product_modifications [itemprop="itemListElement"]');
            for ($i = 0; $i < $items->length; $i++) {
                $item = $items->eq($i);
                $product_items[] = [
                    'article' => (int) pregMatch('/[0-9 ]+/', $item->find('td')->eq(0)->text())[0],
                    'packing' => $item->find('td')->eq(1)->not('div')->text(),
                    'color' => preg_replace('/(\t|\n|\s+)/m', '', $item->find('td')->eq(2)->text()),
                    'price' => (double) pregMatch('/[0-9]+/', $item->find('[class="product_price"]')->text())[0],
                ];

            }

            $product = new \stdClass();
            $product->article = $article;
            $product->title = $title;
            // $product->sub_title = $sub_title;
            $product->short_description = $short_description;
            $product->long_description = $long_description;
            $product->product_items = $product_items;
            $product->specifications = $specifications;
            $product->image_src = $image_src;
            $product->reviews = null;
            $product->has_calculator = true;
            $product->has_video = false;


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

        try {
            $index = 0;
            $page_index = 1;
            $page_count = 2;
            $position = 1;
            do {

                $q = str_replace(' ', '%20', $this->getQ());
                $url = "https://www.kraski.ru/search/";
                $res = $this->http->request('GET', $url, [
                    'query' => [
                        'q' => ($q),
                        'PAGEN_2' => $page_index,
                    ],

                    'proxy' => $this->proxy
                ]);


                // Response data
                $doc = $this->newDocumentHTML($res->getBody()->getContents());
                $cards = $doc->find('.products_catalog .product_item');

                for ($i = 0; $i < $cards->length; $i++) {
                    $url_result[$index]['position'] = $position++;
                    $url_result[$index]['url'] = $cards->eq($i)->find('.product_item--name a')->attr('href');
                    $index++;
                }


                $page_index++;

            } while ($page_index < $page_count + 1);
        } finally {
            $this->unloadDocuments();
        }

        return $url_result;
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
     * @param mixed $q
     */
    public function setQ($q):void
    {
        $this->q = $q;
    }



    /**
     * @return mixed
     * @throws ParserException
     */
    private function getQ()
    {
        if (empty($this->q)) {
            throw new ParserException('Пустой поисковый запрос!', 'Краски RU');
        }
        return $this->q;
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



    /**
     * @param string $url
     */
    public function setUrl(string $url):void
    {
        $this->url = $url;
    }
}