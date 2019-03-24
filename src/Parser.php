<?php

namespace src;

use GuzzleHttp\TransferStats;
use src\parsers\InterfaceParser;

/**
 * Class Parser
 * @package Src
 */
class Parser
{
    private $mem_start = 0;
    /**
     * @var InterfaceParser
     */
    private $parser;
    /**
     * @var string
     */
    private $proxy = '';

    /**
     * @var callable
     */
    private $onProgress;
    /**
     * @var callable
     */
    private $onStats;



    /**
     * Parser constructor.
     *
     * @param InterfaceParser $parser
     */
    public function __construct(InterfaceParser $parser)
    {
        $this->mem_start = memory_get_usage();
        $this->parser = $parser;

        $this->parser->onProgress(function($downloadTotal, $downloadedBytes, $uploadTotal, $uploadedBytes) {
            if (isset($this->onProgress)) {
                call_user_func($this->onProgress, $downloadTotal, $downloadedBytes, $uploadTotal, $uploadedBytes);
            }
        });

        $this->parser->onStats(function(TransferStats $stats){
            if (isset($this->onStats)) {
                call_user_func($this->onStats, $stats);
            }
        });

    }



    /**
     * @return mixed
     */
    public function loadProduct()
    {
        return $this->parser->loadProduct();
    }



    /**
     * @return mixed
     */
    public function loadUrl()
    {
        return $this->parser->loadUrl();
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
        $this->parser->setProxy($ip, $port, $login, $password);
    }



    /**
     * @return int
     */
    public function memory_get_usage()
    {
        return memory_get_usage() - $this->mem_start;
    }



    /**
     * @return string
     */
    public function getProxy():string
    {
        return $this->proxy;
    }



    /**
     * @param callable $onProgress
     *
     */
    public function setOnProgress(callable $onProgress):void
    {
        $this->onProgress = $onProgress;
    }



    /**
     * @param callable $onStats
     */
    public function setOnStats(callable $onStats):void
    {
        $this->onStats = $onStats;
    }



}