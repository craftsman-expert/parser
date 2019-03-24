<?php


namespace src\parsers;



use GuzzleHttp\Client;
use GuzzleHttp\Cookie\FileCookieJar;
use phpQuery;
use src\ParserException;

/**
 * Class MirKrasok
 * @package src\parsers
 */
class Petrovich extends AbstractParser implements InterfaceParser
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
    private $url;
    private $city = 'Москва';

    /**
     * @var callable
     */
    private $onStats;
    /**
     * @var callable
     */
    private $onProgress;



    /**
     * Obi constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $cookie_jar = new FileCookieJar($this->getCookieDir() . '/petrovich.txt', true);
        $this->http = new Client([
            'timeout' => $this->http_timeout,
            'connect_timeout' => $this->http_connect_timeout,
            'cookies' => $cookie_jar,
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

            $code = $doc->find('.product--code [data-test="product-code"]')->text();
            $title = $doc->find('h1.product--title')->text();
            $description = $doc->find('.product--text-info .text--common p')->text();
            $details = $doc->find('.tabs__content .tabs__product-details')->text();
            $image_src = $doc->find('.image-gallery .image-gallery-image img')->attr('srcset');

            // $in_stock = $doc->find('.buy-block .flex-item')->text();
            //
            //
            //
            $specifications = [];
            $items = $doc->find('.text--formated p');
            for ($i = 0; $i < $items->length; $i++) {
                $item = $items->eq($i);
                $re = '/([^:;]*):([^:;]*)(?:;|$)/';
                preg_match($re, preg_replace('/\n  +/', '', $item->text()), $matches);
                $specifications[] = $d = [
                    'name' => $matches[1],
                    'value' => $matches[2],
                ];
            }





            $product = new \stdClass();
            $product->title = $title;

            $product->description = $description;
            $product->details = $details;
            $product->specifications = $specifications;
            $product->image_src = $image_src;
            $product->reviews = $this->loadReviews($code);
            // $product->has_calculator = (bool) $doc->find('.item_bot .item_tab [data-tab-data="RASHOD"]')->is('div');
            // $product->has_video = (bool) $doc->find('.item_bot .item_tab [data-tab-data="VIDEO"]')->is('div');


        } finally {
            $this->unloadDocuments();
            unset($doc);
        }

        return $product;
    }






    /**
     * @return array
     * @throws ParserException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    function loadUrl()
    {
        $url_result = [];
        $position = 1;
        $index = 0;
        $page_index = 0;
        $q = str_replace(' ', '%20', $this->getQ());
        do {
            try {


                $res = $this->http->request('GET', $this->getUrl() . '/search/', [

                    'query' => [
                        'q' => $q,
                        'p' => $page_index,
                        'current_city' => ($this->city),
                        'user_change_city' => 1
                    ],

                    'on_stats' => $this->onStats,
                    'progress' => $this->onProgress,

                    'proxy' => $this->proxy,

                ]);


                $doc = $this->newDocumentHTML($res->getBody()->getContents());
                $cards = $doc->find('.listing__product-item');

                if ($doc->find('.no_searchTitle')->length > 0) {
                    break;
                }

                for ($i = 0; $i < $cards->length; $i++) {
                    $url_result[$index]['position'] = $position++;
                    $url_result[$index]['url'] = $cards->eq($i)->find('.listing__product-title')->attr('href');
                    $index++;
                }

            } finally {
                $this->unloadDocuments();
            }

            $page_index++;
        } while (true);


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
     * @param $productCode
     *
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function loadReviews($productCode)
    {
        $reviews = [];

        $url = "https://widgets.global.ssl.fastly.net/widgets/58877d46c0828e3eabead218/default/product/$productCode/product-reviews.html?origin=https_.petrovich.ru";
        $res = $this->http->request('GET', $url);

        try{
            $doc = phpQuery::newDocumentHTML($res->getBody()->getContents());

            $items = $doc->find('.sp-reviews .sp-review');
            for ($i = 0; $i < $items->length; $i++){
                $item = $items->eq($i);

                $reviews[] = [
                    'rating' => $item->find('[itemprop="ratingValue"]')->attr('content'),
                    'author' => $item->find('[itemprop="author"]')->text(),
                    'location' => trim(preg_replace('/\n  +/m', ' ', $item->find('.sp-review-author-details')->text())),
                    'data' => $item->find('.sp-review-date')->attr('title'),
                    'text' => trim(preg_replace('/\n  +/m', ' ', $item->find('[class="sp-review-body sp-review-text"]')->text())),
                    'merits' => trim(preg_replace('/\n  +/m', ' ', $item->find('[class="sp-review-pros-content sp-review-text-content"]')->text())),
                    'disadvantages' => trim(preg_replace('/\n  +/m', ' ', $item->find('[class="sp-review-cons-content sp-review-text-content"]')->text())),
                ];
            }
        }finally{
            phpQuery::unloadDocuments();
        }

        return $reviews;
    }


    /**
     * Получить все магазины
     * @return array
     */
    public function getCities()
    {
        $res = $this->http->get('https://petrovich.ru/api/pet/v002/base/cities');
        $obj = json_decode($res->getBody()->getContents());
        return array_merge($obj->data->commonCities, $obj->data->regionalCities);
    }



    /**
     * @param mixed $key_word
     */
    public function q($key_word):void
    {
        $this->q = $key_word;
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
    private function getQ()
    {
        if (empty($this->q)) {
            throw new ParserException('Пустой поисковый запрос!', 'obi');
        }
        return $this->q;
    }



    /**
     * @param string $city
     */
    public function setCity(string $city):void
    {
        $this->city = $city;
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