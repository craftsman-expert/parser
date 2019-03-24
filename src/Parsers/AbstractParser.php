<?php


namespace src\parsers;

use phpQuery;
use QueryTemplatesParse;
use QueryTemplatesSource;
use QueryTemplatesSourceQuery;

/**
 * Class AbstractParser
 * @package src\parsers
 */
abstract class AbstractParser
{
    /**
     * @var string
     */
    protected $cookie_dir;
    protected $http_timeout = 30;
    protected $http_connect_timeout = 30;

    /**
     * AbstractParser constructor.
     */
    public function __construct()
    {
        $this->setCookieDir(sys_get_temp_dir() . '/cookie');
        if (!file_exists($this->getCookieDir())){
            mkdir($this->getCookieDir(), 0774);
        }
    }



    /**
     * @param null $markup
     * @param null $charset
     *
     * @return \phpQueryObject|QueryTemplatesParse|QueryTemplatesSource|QueryTemplatesSourceQuery
     */
    public function newDocumentHTML($markup = null, $charset = null)
    {
        return phpQuery::newDocumentHTML($markup, $charset);
    }



    public function unloadDocuments()
    {
        phpQuery::unloadDocuments();
    }



    /**
     * @return string
     */
    public function getCookieDir()
    {
        return $this->cookie_dir;
    }



    /**
     * @param string $cookie_dir
     */
    public function setCookieDir($cookie_dir)
    {
        $this->cookie_dir = $cookie_dir;
    }
}