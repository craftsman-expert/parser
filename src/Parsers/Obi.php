<?php


namespace src\parsers;



use GuzzleHttp\Client;
use GuzzleHttp\Cookie\FileCookieJar;
use phpQueryObject;
use src\ParserException;

/**
 * Class Obi
 * @package src\parsers
 */
class Obi extends AbstractParser implements InterfaceParser
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
        $cookie_jar = new FileCookieJar($this->getCookieDir() . '/obi.txt', true);
        $this->http = new Client([
            'base_uri' => 'https://www.obi.ru',
            'cookies' => $cookie_jar,
            'timeout' => $this->http_timeout,
            'connect_timeout' => $this->http_connect_timeout,
        ]);
    }



    /**
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    function loadProduct()
    {
        try {
            $res = $this->http->request('GET', 'https://www.obi.ru/store/change', [
                'query' => [
                    'storeID' => sprintf("%'.03d", $this->getStoreNumber()),
                    'redirectUrl' => $this->url,
                ],

                'on_stats' => $this->onStats,
                'progress' => $this->onProgress,

                'proxy' => $this->proxy,

            ]);

            $doc = $this->newDocumentHTML($res->getBody()->getContents());

            $store_name = $doc->find('[wt_name="sticky_header.my_store"]')->text();
            $article = (int)@pregMatch('/[0-9]+/', $doc->find('[tm-data="ads.description-text.article-number.p"]')->text())[0];
            $title = $doc->find('[tm-data="ads.overview-description.product-name.h1"]')->text();
            $description = $doc->find('div.description-text p:not(.article-number)')->text();
            $price = $doc->find('[tm-data="ads.price.strong"]')->text();
            $image_src = "https:" . $doc->find('.ads-slider__image')->attr('src');
            $inStock = (int) @pregMatch('/[0-9]/', $doc->find('[tm-data="instore.adp.availability_message"]')->text())[0];
            $rating = (int) @pregMatch('/[0-9]/', $doc->find('#Overview')->find('[class="rating__count-after"]')->text())[0];
            $deliveryinfo = $doc->find('[class="row-flud marg_t15"]')->text();
            $deliveryinfo2 = $doc->find('[class="span8 deliveryinfo__left"]')->text();


            $specifications = [];
            for ($i = 0; $i < $doc->find('[class="c-datalist c-datalist--33"] dt')->length; $i++){
                $dt = $doc->find('[class="c-datalist c-datalist--33"] dt')->eq($i)->text();
                $dd = $doc->find('[class="c-datalist c-datalist--33"] dd')->eq($i)->text();
                $specifications[] = $d = [
                    'name' => $dt,
                    'value' =>$dd
                ];
            }

            $reviews = [];
            $items  = $doc->find('[class="span12 span-tablet8"] .rating');
            $vowels = array("Недостатки:", "&nbsp");
            for ($i = 0; $i < $items->length; $i++){
                $item = $items->eq($i);

                $reviews[] = [
                    'rating' => $item->find('[class="ratingbar large"] span span')->text(),
                    'title' => $item->find('[class="no-margin-bottom font-lg"] strong')->text(),
                    'text' => $item->find('[class="rating-text"]')->text(),
                    'author' => $item->find('[class="author font-xs text-grey"]')->text(),
                    'merits' => str_replace($vowels, '', $item->find('[class="rating__vote"] span:not(.rating__vote-icon)')->text()),
                    'disadvantages' => str_replace($vowels, '', $item->find('[class="rating__vote rating__vote--dislike"]')->text())
                ];
            }

            $product = [
                'article' => $article,
                'title' => $title,
                'description' => $description,
                'specifications' => $specifications,
                'deliveryinfo' => $deliveryinfo,
                'deliveryinfo2' => $deliveryinfo2,
                'price' => (double) str_replace(' ', '', $price),
                'in_stock' => $inStock,
                'rating' => $rating,
                'image_src' => $image_src,
                'reviews_url' => 'https://www.obi.ru' . $this->url . '#Ratings',
                'reviews' => $reviews
            ];


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

        $page_index = 1;
        $position = 1;
        $index = 0;
        do {
            try{
                $q = str_replace(' ', '%20', $this->getKeyWord());
                $url = "https://www.obi.ru/search/$q/?page=$page_index";
                $res = $this->http->request('GET', 'https://www.obi.ru/store/change', [
                    'query' => [
                        'storeID' => sprintf("%'.03d", $this->getStoreNumber()),
                        'redirectUrl' => $url,
                    ],

                    'on_stats' => $this->onStats,
                    'progress' => $this->onProgress,

                    'proxy' => $this->proxy,

                ]);


                // Response data
                $doc = $this->newDocumentHTML($res->getBody()->getContents());
                $pages = $doc->find('.pagination-bar__link-refs')->text();
                $products = $doc->find('.product');

                $page_index = (int) @pregMatch('/[0-9]/', $pages)[0];
                $page_count = (int) @pregMatch('/[0-9]$/', $pages)[0];

                if ($page_count == 0) {
                    break;
                }

                for ($i = 0; $i < $products->length; $i++) {
                    $url_result[$index]['position'] = $position++;
                    $url_result[$index]['url']      = $products->eq($i)->find('.product-wrapper')->attr('href');
                    $index++;
                }
            }finally{
               $this->unloadDocuments();
               unset($doc);
            }


            $page_index++;
        } while ($page_index < $page_count + 1);

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
        if (empty($this->key_word)){
            throw new ParserException('Пустой поисковый запрос!', 'obi');
        }
        return $this->key_word;
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